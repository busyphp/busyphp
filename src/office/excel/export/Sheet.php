<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\export;

use BusyPHP\Model;
use BusyPHP\model\annotation\field\Export;
use BusyPHP\office\excel\Helper;
use BusyPHP\office\excel\export\parameter\CellParameter;
use BusyPHP\office\excel\export\parameter\RowParameter;
use BusyPHP\office\excel\export\parameter\SheetParameter;
use Closure;
use RuntimeException;
use think\Collection;

/**
 * 导出工作集配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/6 15:31 Sheet.php $
 */
class Sheet
{
    /**
     * 导出列配置
     * @var Column[]
     */
    protected array $columns;
    
    /**
     * 数据集
     * @var array|Collection
     */
    protected array|Collection $list;
    
    /**
     * 工作集名称
     * @var string
     */
    protected string $title = '';
    
    /**
     * 起始行下标
     * @var int
     */
    protected int $startRow = 1;
    
    /**
     * 是否自动字母
     * @var bool
     */
    protected bool $autoLetter = false;
    
    /**
     * 是否已赋值自动字母
     * @var bool
     */
    protected bool $autoLetterLock = false;
    
    /**
     * 是否已排序
     * @var bool
     */
    protected bool $letterSortLock = false;
    
    /**
     * 行处理回调
     * @var Closure|null
     */
    protected ?Closure $row = null;
    
    /**
     * 单元格处理回调
     * @var Closure|null
     */
    protected ?Closure $cell = null;
    
    /**
     * 工作集处理回调
     * @var Closure|null
     */
    protected ?Closure $handle = null;
    
    /**
     * 第一列
     * @var Column|null
     */
    protected ?Column $first = null;
    
    /**
     * 最后一列
     * @var Column|null
     */
    protected ?Column $last = null;
    
    
    /**
     * 初始化
     * @param Column[]         $columns 导出列配置
     * @param array|Collection $list 导出数据
     * @return static
     */
    public static function init(array $columns = [], array|Collection $list = []) : static
    {
        return new static($columns, $list);
    }
    
    
    /**
     * 构造函数
     * @param Column[]         $columns 导出列配置
     * @param array|Collection $list 导出数据
     */
    public function __construct(array $columns = [], array|Collection $list = [])
    {
        $this->columns = $columns;
        $this->list    = $list;
    }
    
    
    /**
     * 设置导出列配置
     * @param array $columns
     * @return $this
     */
    public function columns(array $columns) : static
    {
        $this->columns = $columns;
        
        return $this;
    }
    
    
    /**
     * 设置导出数据集合
     * @param array|Collection $list
     * @return $this
     */
    public function list(array|Collection $list) : static
    {
        $this->list = $list;
        
        return $this;
    }
    
    
    /**
     * 设置工作集名称
     * @param string $title 工作集名称
     * @return $this
     */
    public function title(string $title) : static
    {
        $this->title = $title;
        
        return $this;
    }
    
    
    /**
     * 设置起始行
     * @param int $row
     * @return static
     */
    public function start(int $row) : static
    {
        $this->startRow = $row;
        
        return $this;
    }
    
    
    /**
     * 设置单元格处理回调
     * @param Closure(mixed $value, mixed $data, CellParameter $parameter):void|null $callback 回调参数： <p>
     * - {@see mixed} $value 当前单元格的值<br />
     * - {@see mixed} $data 当前数据<br />
     * - {@see CellParameter} $parameter 单元格参数<br /><br />
     * <b>示例</b>：<br />
     * <pre>
     * $this->call(function({@see mixed} $value, {@see mixed} $data, {@see CellParameter} $parameter) {
     * })
     * </pre>
     * </p>
     * @return static
     */
    public function cell(?Closure $callback) : static
    {
        $this->cell = $callback;
        
        return $this;
    }
    
    
    /**
     * 设置行处理回调
     * @param Closure(mixed $value, mixed $data, RowParameter $parameter):void|null $callback 回调参数： <p>
     * - {@see mixed} $data 当前数据<br />
     * - {@see RowParameter} $parameter 行参数<br /><br />
     * <b>示例</b>：<br />
     * <pre>
     * $this->row(function({@see mixed} $data, {@see RowParameter} $parameter) {
     * })
     * </pre>
     * </p>
     * @return $this
     */
    public function row(?Closure $callback) : static
    {
        $this->row = $callback;
        
        return $this;
    }
    
    
    /**
     * 设置工作集处理回调
     * @param Closure(SheetParameter):void|null $callback 回调参数： <p>
     * - {@see SheetParameter} $parameter 工作集参数<br /><br />
     * <b>示例</b>：<br />
     * <pre>
     * $this->sheet(function({@see SheetParameter} $parameter) {
     * })
     * </pre>
     * </p>
     * @return $this
     */
    public function sheet(?Closure $callback) : static
    {
        $this->handle = $callback;
        
        return $this;
    }
    
    
    /**
     * 设置是否自动字母
     * @param bool $auto
     * @return static
     */
    public function autoLetter(bool $auto) : static
    {
        $this->autoLetter = $auto;
        
        return $this;
    }
    
    
    /**
     * 获取工作集表格
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }
    
    
    /**
     * 获取起始行下标
     * @return int
     */
    public function getStartRow() : int
    {
        return $this->startRow;
    }
    
    
    /**
     * 获取单元格处理回调
     * @return Closure|null
     * @internal
     */
    public function getCell() : ?Closure
    {
        return $this->cell;
    }
    
    
    /**
     * 获取行处理回调
     * @return Closure|null
     * @internal
     */
    public function getRow() : ?Closure
    {
        return $this->row;
    }
    
    
    /**
     * 获取所有列
     * @return Column[]
     */
    public function getColumns() : array
    {
        if (!$this->autoLetterLock && $this->autoLetter) {
            $this->autoLetterLock = true;
            $letters              = Helper::letters();
            foreach ($this->columns as $i => $column) {
                $column->letter($letters[$i]);
            }
        }
        
        // 排序
        if (!$this->letterSortLock) {
            $this->letterSortLock = true;
            
            $columns = [];
            $count   = count($this->columns);
            foreach ($this->columns as $i => $column) {
                if (!$letter = $column->getLetter()) {
                    throw new RuntimeException(sprintf('第%s列未指定字母', $i));
                }
                if ('' === $column->getField()) {
                    throw new RuntimeException(sprintf('第%s列未指定字段', $i));
                }
                
                if ($i === 0) {
                    $this->first = $column;
                } elseif ($i === $count - 1) {
                    $this->last = $column;
                }
                
                $columns[$letter] = $column;
            }
            ksort($columns);
            $this->columns = array_values($columns);
            
            if ($this->columns) {
                $this->first = $this->columns[0];
                
                $count = count($this->columns);
                if ($count > 1) {
                    $this->last = $this->columns[$count - 1];
                }
            }
        }
        
        return $this->columns;
    }
    
    
    /**
     * 获取第一列
     * @return Column|null
     */
    public function getFirst() : ?Column
    {
        $this->getColumns();
        
        return $this->first;
    }
    
    
    /**
     * 获取最后一列
     * @return Column|null
     */
    public function getLast() : ?Column
    {
        $this->getColumns();
        
        return $this->last;
    }
    
    
    /**
     * 获取数据的开始行
     * @return int
     */
    public function getDataStartRow() : int
    {
        return $this->getStartRow() + 1;
    }
    
    
    /**
     * 获取数据的结束行
     * @return int
     */
    public function getDataEndRow() : int
    {
        return $this->getStartRow() + count($this->getList());
    }
    
    
    /**
     * 获取指定行的单元格范围
     * @param int $rowIndex
     * @return string
     */
    public function getRowRange(int $rowIndex) : string
    {
        if (!$first = $this->getFirst()) {
            throw new RuntimeException('未设置导出列');
        }
        
        return $first->cellIndex($rowIndex, $this->getLast());
    }
    
    
    /**
     * 获取从开始行到指定行的单元格范围
     * @param int|null $rowIndex 默认为全部范围
     * @return string
     */
    public function getAllRange(int $rowIndex = null) : string
    {
        if (!$first = $this->getFirst()) {
            throw new RuntimeException('未设置导出列');
        }
        
        $rowIndex = is_null($rowIndex) ? $this->getDataEndRow() : $rowIndex;
        
        return $first->cellIndex($this->getStartRow(), $this->getLast(), $rowIndex);
    }
    
    
    /**
     * 获取标题栏的单元格范围
     * @return string
     */
    public function getHeadRange() : string
    {
        return $this->getRowRange($this->getStartRow());
    }
    
    
    /**
     * 获取数据行的单元格范围
     * @return string
     */
    public function getDataRange() : string
    {
        if (!$first = $this->getFirst()) {
            throw new RuntimeException('未设置导出列');
        }
        
        return $first->cellIndex($this->getDataStartRow(), $this->getLast(), $this->getDataEndRow());
    }
    
    
    /**
     * 获取数据
     * @return array
     */
    public function getList() : array
    {
        if ($this->list instanceof Collection) {
            $this->list = $this->list->all();
        }
        
        return $this->list;
    }
    
    
    /**
     * 获取工作集处理回调
     * @return Closure|null
     */
    public function getHandle() : ?Closure
    {
        return $this->handle;
    }
    
    
    /**
     * 通过模型获取导出列选项
     * @param Model $model
     * @return Column[]
     */
    public static function getColumnsByModel(Model $model) : array
    {
        $columns    = [];
        $fieldClass = $model->getFieldClass();
        $letters    = Helper::letters();
        $columnList = [];
        $deleteList = [];
        foreach ($fieldClass::getPropertyAttrs() as $name => $attr) {
            /** @var Export $export */
            $export = $attr['export'] ?? null;
            if (!$export) {
                continue;
            }
            
            if ($letter = $export->getLetter()) {
                if (false !== $index = array_search($letter, $letters, true)) {
                    $deleteList[] = $index;
                }
                $columns[] = Column::init($letter, $fieldClass::$name());
            } else {
                $columnList[] = Column::init('', $fieldClass::$name());
            }
        }
        
        $newLetters = [];
        foreach ($letters as $i => $letter) {
            if (!in_array($i, $deleteList, true)) {
                $newLetters[] = $letter;
            }
        }
        
        foreach ($columnList as $i => $item) {
            $item->letter($newLetters[$i]);
            $columns[] = $item;
        }
        unset($columnList, $newLetters, $letters);
        
        return $columns;
    }
}