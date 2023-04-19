<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel;

use BusyPHP\exception\ClassNotFoundException;
use BusyPHP\exception\MethodNotFoundException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\LogHelper;
use BusyPHP\Model;
use BusyPHP\model\annotation\field\Export as FieldExport;
use BusyPHP\model\annotation\field\Import as FieldImport;
use BusyPHP\model\Field;
use BusyPHP\office\excel\import\ImportColumn;
use BusyPHP\office\excel\import\ImportException;
use BusyPHP\office\excel\import\ImportResult;
use BusyPHP\office\excel\import\interfaces\ImportFetchClassInterface;
use BusyPHP\office\excel\import\interfaces\ImportInterface;
use BusyPHP\office\excel\import\parameter\ImportFilterParameter;
use BusyPHP\office\excel\import\parameter\ImportInitParameter;
use BusyPHP\office\excel\import\parameter\ImportListParameter;
use BusyPHP\office\excel\import\parameter\ImportRowParameter;
use BusyPHP\office\excel\import\parameter\ImportSaveParameter;
use Closure;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use think\Container;
use Throwable;

/**
 * Excel导入类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/6 22:25 Import.php $
 * @template T
 */
class Import
{
    /**
     * 行数据处理成功事件，闭包参数：
     * - {@see mixed} $data 当前行得到的数据
     * - {@see int} $rowIndex 当前第几行
     *
     * <b>示例</b>
     * <pre>
     * $this->on({@see Import::EVENT_ROW_SUCCESS}, function({@see mixed} $data, {@see int} $rowIndex) {
     * })
     * </pre>
     * @var string
     */
    public const EVENT_ROW_SUCCESS = 'row_success';
    
    /**
     * 行数据处理失败事件，闭包参数：
     * - {@see string} $error 失败原因
     * - {@see boolean} $break true 跳出循环，false为跳过本次循环
     *
     * <b>示例</b>
     * <pre>
     * $this->on({@see Import::EVENT_ROW_ERROR}, function({@see string} $error, {@see boolean} $break) {
     * })
     * </pre>
     * @var string
     */
    public const EVENT_ROW_ERROR = 'row_error';
    
    /**
     * 总数据处理完成事件，闭包参数：
     * - {@see array} $list 读取的所有数据
     *
     * <b>示例</b>
     * <pre>
     * $this->on({@see Import::EVENT_LIST_HANDLED}, function({@see array} $list) {
     * })
     * </pre>
     * @var string
     */
    public const EVENT_LIST_HANDLED = 'list_handled';
    
    /**
     * 数据已保存成功事件，闭包参数：
     * - {@see array} $data 保存成功的单条数据或一批次数据集合
     *
     * <b>示例</b>
     * <pre>
     * $this->on({@see Import::EVENT_SAVE_SUCCESS}, function({@see mixed} $data) {
     * })
     * </pre>
     * @var string
     */
    public const EVENT_SAVE_SUCCESS = 'save_success';
    
    /**
     * 数据保存失败事件，闭包参数：
     * - {@see array} $data 保存失败的单条数据或一批次数据集合
     * - {@see Throwable} $error 保存失败异常对象
     *
     * <b>示例</b>
     * <pre>
     * $this->on({@see Import::EVENT_SAVE_ERROR}, function({@see mixed} $data, {@see Throwable} $error) {
     * })
     * </pre>
     * @var string
     */
    public const EVENT_SAVE_ERROR = 'save_error';
    
    /**
     * 导入的文件路径
     * @var string
     */
    protected string $path;
    
    /**
     * 导入列配置
     * @var ImportColumn[]
     */
    protected array $columns = [];
    
    /**
     * 数据开始行
     * @var int
     */
    protected int $startRow;
    
    /**
     * 第几个工作集
     * @var int
     */
    protected int $sheetIndex;
    
    /**
     * 行处理回调
     * @var Closure|null
     */
    protected Closure|null $row = null;
    
    /**
     * 总体数据处理回调
     * @var Closure|null
     */
    protected Closure|null $list = null;
    
    /**
     * 导入的模型
     * @var Model|null
     */
    protected ?Model $model = null;
    
    /**
     * 接口
     * @var ImportInterface|null
     */
    protected ?ImportInterface $api = null;
    
    /**
     * 事件
     * @var array
     */
    protected array $events = [];
    
    
    /**
     * 快速实例化
     * @param string                               $path 导入的文件路径
     * @param ImportColumn[]|Model|ImportInterface $columns 导入列配置或导入的模型
     * @param int                                  $startRow 从第几行开始读
     * @param int                                  $sheetIndex 读取的工作集下标
     * @return static
     */
    public static function init(string $path, array|Model|ImportInterface $columns = [], int $startRow = 2, int $sheetIndex = 0) : static
    {
        return Container::getInstance()->make(self::class, [$path, $columns, $startRow, $sheetIndex], true);
    }
    
    
    /**
     * 构造函数
     * @param string                               $path 导入的文件路径
     * @param ImportColumn[]|Model|ImportInterface $columns 导入列配置或导入的模型
     * @param int                                  $startRow 从第几行开始读
     * @param int                                  $sheetIndex 读取的工作集下标
     */
    public function __construct(string $path, mixed $columns = [], int $startRow = 2, int $sheetIndex = 0)
    {
        $this->path       = $path;
        $this->startRow   = $startRow;
        $this->sheetIndex = $sheetIndex;
        
        if ($columns instanceof ImportInterface) {
            $this->api = $columns;
            $this->api->initExcelImport(new ImportInitParameter($this));
        }
        
        if ($columns instanceof Model) {
            $this->model = $columns;
            if (!$this->columns) {
                $this->columns(static::getColumnsByModel($this->model));
            }
        }
        
        if (!$this->columns && is_array($columns)) {
            $this->columns($columns);
        }
    }
    
    
    /**
     * 设置导入的文件路径
     * @param string $path
     * @return static
     */
    public function path(string $path) : static
    {
        $this->path = $path;
        
        return $this;
    }
    
    
    /**
     * 设置导入列配置
     * @param ImportColumn[] $columns
     * @return static
     */
    public function columns(array $columns) : static
    {
        $this->columns = $columns;
        
        return $this;
    }
    
    
    /**
     * 添加导入列配置
     * @param ImportColumn $column
     * @return $this
     */
    public function add(ImportColumn $column) : static
    {
        $this->columns[] = $column;
        
        return $this;
    }
    
    
    /**
     * 设置从第几行开始读
     * @param int $row
     * @return static
     */
    public function start(int $row) : static
    {
        $this->startRow = $row;
        
        return $this;
    }
    
    
    /**
     * 设置读取的工作集下标
     * @param int $index
     * @return $this
     */
    public function sheet(int $index) : static
    {
        $this->sheetIndex = $index;
        
        return $this;
    }
    
    
    /**
     * 设置行处理回调
     * @param Closure(ImportRowParameter):void|null $callback 闭包参数：<p>
     * - {@see ImportRowParameter} $parameter 参数对象<br /><br />
     * <b>示例</b>：<br />
     * <pre>
     * $this->row(function({@see ImportRowParameter} $parameter) {
     * })
     * </pre>
     * </p>
     * @return static
     */
    public function row(?Closure $callback) : static
    {
        $this->row = $callback;
        
        return $this;
    }
    
    
    /**
     * 设置总数据处理回调
     * @param Closure(ImportListParameter):void|null $callback 闭包参数：<p>
     * - {@see ImportListParameter} $parameter 参数对象<br /><br />
     * <b>示例</b>：<br />
     * <pre>
     * $this->row(function({@see ImportListParameter} $parameter) {
     * })
     * </pre>
     * </p>
     * @return static
     */
    public function list(?Closure $callback) : static
    {
        $this->list = $callback;
        
        return $this;
    }
    
    
    /**
     * 监听事件
     * @param string|array $event 事件名称
     * @param Closure|null $callback 事件回调
     * @return $this
     */
    public function on(string|array $event, ?Closure $callback = null) : static
    {
        if (is_array($event)) {
            foreach ($event as $k => $v) {
                $this->on($k, $v);
            }
        } else {
            $this->events[$event][] = $callback;
        }
        
        return $this;
    }
    
    
    /**
     * 读取数据
     * @param class-string<T> $class
     * @return ImportResult
     * @throws Exception
     */
    public function fetch(string $class = '') : ImportResult
    {
        // 解析的类
        if (!$class && $this->model) {
            $class = $this->model->getFieldClass();
        }
        
        $reader    = IOFactory::createReader(IOFactory::identify($this->path));
        $spread    = $reader->load($this->path);
        $sheet     = $spread->getSheet($this->sheetIndex);
        $maxRow    = $sheet->getHighestRow();
        $maxColumn = $sheet->getHighestColumn();
        
        // 取出图片
        $drawingMap = [];
        /** @var Drawing $drawing */
        foreach ($sheet->getDrawingCollection() as $drawing) {
            $drawingMap[$drawing->getCoordinates()][] = $drawing;
        }
        
        // 遍历取数据
        $list = [];
        if ($this->columns) {
            $this->columns = Helper::fullLetterAndSortColumns($this->columns);
            for ($rowIndex = $this->startRow; $rowIndex <= $maxRow; $rowIndex++) {
                $item = [];
                try {
                    foreach ($this->columns as $column) {
                        $cellIndex = sprintf('%s%s', $column->getLetter(), $rowIndex);
                        $filter    = $column->getFilter();
                        
                        // 图片
                        if ($drawings = ($drawingMap[$cellIndex] ?? null)) {
                            $value = [];
                            foreach ($drawings as $drawing) {
                                $value[] = $drawing->getPath();
                            }
                        } else {
                            $value = $sheet->getCell($cellIndex)->getValue();
                        }
                        
                        // 过滤
                        switch (true) {
                            // int
                            case $filter === ImportColumn::FILTER_INT:
                                $value = (int) $value;
                            break;
                            
                            // float
                            case $filter === ImportColumn::FILTER_FLOAT:
                                $value = (float) $value;
                            break;
                            
                            // bool
                            case $filter === ImportColumn::FILTER_BOOL:
                                $value = (bool) $value;
                            break;
                            
                            // 剔除左右内容
                            case $filter === ImportColumn::FILTER_TRIM:
                                $value = trim((string) $value);
                                foreach ($column->getTrimCharacters() as $character) {
                                    $value = trim($value, $character);
                                }
                            break;
                            
                            // 切割为数组
                            case $filter === ImportColumn::FILTER_SPLIT:
                                $delimiter = $column->getSplitDelimiter();
                                $delimiter = $delimiter === '' ? ',' : $delimiter;
                                $value     = (string) $value;
                                if ($replaces = $column->getSplitReplaces()) {
                                    $value = str_replace($replaces, $delimiter, $value);
                                }
                                $value = ArrayHelper::split($delimiter, $value, $column->getSplitMin(), $column->getSplitLimit());
                            break;
                            
                            // 转为时间
                            case $filter === ImportColumn::FILTER_TIMESTAMP:
                            case $filter === ImportColumn::FILTER_DATE:
                                if (is_numeric($value)) {
                                    $value = Date::excelToTimestamp($value);
                                } else {
                                    $value = (int) strtotime($value);
                                }
                                
                                // 转为日期格式
                                if ($filter === ImportColumn::FILTER_DATE) {
                                    $value = date($column->getDateFormat(), $value);
                                }
                            break;
                            
                            // 方法回调
                            case is_string($filter) && is_callable($filter) && function_exists($filter):
                                $value = call_user_func($filter, $value);
                            break;
                            
                            // 闭包回调
                            case is_callable($filter):
                                $value = call_user_func($filter, $value, new ImportFilterParameter($sheet, $cellIndex, $rowIndex, $column));
                            break;
                        }
                        
                        ArrayHelper::set($item, $column->getField(), $value);
                    }
                    
                    // 行处理回调
                    if ($this->row instanceof Closure) {
                        call_user_func($this->row, $parameter = new ImportRowParameter($sheet, $item, $rowIndex));
                        $item = $parameter->data;
                    }
                    
                    // 转为对象
                    if ($class && !$item instanceof $class) {
                        if ($class instanceof Field) {
                            $item = $class::init($item);
                        } elseif ($class instanceof ImportFetchClassInterface) {
                            $item = $class::onExcelImportFetchRowToThis($item);
                        }
                    }
                    
                    $list[] = $item;
                    $this->trigger(self::EVENT_ROW_SUCCESS, [$item, $rowIndex]);
                } catch (ImportException $e) {
                    $this->trigger(self::EVENT_ROW_ERROR, [$e->getMessage(), $e->isBreak()]);
                    
                    if ($e->isBreak()) {
                        break;
                    } else {
                        continue;
                    }
                }
            }
        }
        
        // 总体数据处理回调
        if ($this->list instanceof Closure) {
            call_user_func($this->list, $parameter = new ImportListParameter($sheet, $list, $maxRow, $maxColumn));
            $list = $parameter->list;
        }
        // 触发总数据处理完成事件
        $this->trigger(self::EVENT_LIST_HANDLED, [$list]);
        
        // 实现了接口
        if ($this->api) {
            $result = $this->api->saveExcelImport(new ImportSaveParameter($this, $list));
        } elseif ($this->model) {
            $result = new ImportResult(0, 0);
            foreach ($list as $item) {
                try {
                    $this->model->validate($item, $this->model::SCENE_CREATE)->insert();
                    $result->successTotal++;
                    $this->triggerSaved(true, $item);
                } catch (Throwable $e) {
                    $result->errorTotal++;
                    $this->triggerSaved(false, $item, $e);
                }
            }
        } else {
            $result = new ImportResult(0, 0);
        }
        
        $result->list = $list;
        
        return $result;
    }
    
    
    /**
     * 触发数据保存完成事件
     * @param bool           $success 是否成功
     * @param mixed          $data 完成的单条数据或完成的批次数据集合
     * @param Throwable|null $error 失败异常
     * @return static
     * @internal
     */
    public function triggerSaved(bool $success, mixed $data, Throwable $error = null) : static
    {
        if ($success) {
            $this->trigger(self::EVENT_SAVE_SUCCESS, [$data]);
        } else {
            $this->trigger(self::EVENT_SAVE_ERROR, [$data, $error]);
        }
        
        return $this;
    }
    
    
    /**
     * 触发事件
     * @param string $event
     * @param array  $args
     * @return static
     * @internal
     */
    protected function trigger(string $event, array $args) : static
    {
        foreach ($this->events[$event] ?? [] as $item) {
            if (!$item instanceof Closure) {
                continue;
            }
            
            try {
                call_user_func_array($item, $args);
            } catch (Throwable $e) {
                LogHelper::default()->tag($event, __METHOD__)->error($e);
            }
        }
        
        return $this;
    }
    
    
    /**
     * 通过模型获取导入列选项集合
     * @param Model $model
     * @return ImportColumn[]
     */
    public static function getColumnsByModel(Model $model) : array
    {
        $columns    = [];
        $fieldClass = $model->getFieldClass();
        foreach ($fieldClass::getPropertyAttrs() as $name => $attr) {
            /** @var FieldImport $import */
            $import = $attr['import'];
            /** @var FieldExport $export */
            $export = $attr['export'];
            
            // 1. 都没有不记录
            // 2. 导入注解不存在，导出注解存在且不允许导入
            if ((!$import && !$export) || (!$import && $export && !$export->isImport())) {
                continue;
            }
            
            $letter = '';
            if ($import) {
                $letter = $import->getLetter();
            }
            if (!$letter && $export) {
                $letter = $export->getLetter();
            }
            $columns[] = ImportColumn::init($letter, $fieldClass::$name());
        }
        
        return $columns;
    }
}