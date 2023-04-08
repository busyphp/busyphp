<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel;

use BusyPHP\helper\ArrayHelper;
use BusyPHP\Model;
use BusyPHP\office\excel\export\Column;
use BusyPHP\office\excel\export\parameter\HandleParameter;
use BusyPHP\office\excel\export\Sheet;
use BusyPHP\office\excel\export\interfaces\SheetInterface;
use BusyPHP\office\excel\export\parameter\CellParameter;
use BusyPHP\office\excel\export\parameter\FilterParameter;
use BusyPHP\office\excel\export\parameter\RowParameter;
use BusyPHP\office\excel\export\parameter\SheetParameter;
use Closure;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use think\Container;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;
use think\response\File;
use Throwable;

/**
 * Excel导出类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/6 11:58 Export.php $
 */
class Export
{
    /** @var string XLSX文件 */
    const TYPE_XLSX = 'xlsx';
    
    /** @var string XLS文件 */
    const TYPE_XLS = 'xls';
    
    /** @var string CSV文件 */
    const TYPE_CSV = 'csv';
    
    /** @var string ODS文件 */
    const TYPE_ODS = 'ods';
    
    /** @var string HTML文件 */
    const TYPE_HTML = 'html';
    
    /** @var string TCPDF */
    const TYPE_TCPDF = 'tcpdf';
    
    /** @var string DOMPDF */
    const TYPE_DOMPDF = 'dompdf';
    
    /** @var string MPDF */
    const TYPE_MPDF = 'mpdf';
    
    /**
     * 导出类型
     * @var string
     */
    protected string $type = self::TYPE_XLSX;
    
    /**
     * 工作集集合
     * @var Sheet[]
     */
    protected array $sheets = [];
    
    /**
     * Spreadsheet
     * @var Spreadsheet
     */
    protected Spreadsheet $spread;
    
    /**
     * 处理回调
     * @var Closure|null
     */
    protected ?Closure $handle = null;
    
    
    /**
     * 快速实例化
     * @param Sheet|SheetInterface|Model|null $sheet 工作集对象或模型
     * @return static
     * @throws Throwable
     */
    public static function init(Sheet|SheetInterface|Model|null $sheet = null) : static
    {
        return Container::getInstance()->make(self::class, [$sheet], true);
    }
    
    
    /**
     * 构造函数
     * @param null $sheet 工作集对象或模型
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function __construct($sheet = null)
    {
        $this->spread = new Spreadsheet();
        
        if ($sheet) {
            $this->add($sheet);
        }
    }
    
    
    /**
     * 设置导出类型
     * @param string $type
     * @return static
     */
    public function type(string $type) : static
    {
        $this->type = $type;
        
        return $this;
    }
    
    
    /**
     * 添加工作集
     * @param Sheet|SheetInterface|Model $sheet 工作集对象或工作集接口
     * @return static
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function add(Sheet|SheetInterface|Model $sheet) : static
    {
        $columns = [];
        $model   = null;
        if ($sheet instanceof Model) {
            $model   = $sheet;
            $columns = Sheet::getColumnsByModel($sheet);
        }
        
        // 实现了接口
        if ($sheet instanceof SheetInterface) {
            $sheet->initExcelExportSheet($sheet = Sheet::init($columns));
        } elseif ($model) {
            $sheet = Sheet::init($columns);
        }
        
        // 模型自动填充数据
        if ($model) {
            // 排序
            if (!$model->getOptions('order')) {
                $model->order($model->getPk(), 'desc');
            }
            // 查找数据
            if (!$sheet->getList()) {
                $sheet->list($model->selectList());
            }
        }
        
        $this->sheets[] = $sheet;
        
        return $this;
    }
    
    
    /**
     * 设置导出处理回调
     * @param Closure(HandleParameter $parameter):void|null $handle 回调参数：<p>
     * - {@see HandleParameter} $parameter 参数对象<br /><br />
     * <b>示例</b>：<br />
     * <pre>
     * $this->handle(function({@see HandleParameter} $parameter) {
     * })
     * </pre>
     * </p>
     * @return $this
     */
    public function handle(?Closure $handle) : static
    {
        $this->handle = $handle;
        
        return $this;
    }
    
    
    /**
     * 构建一个Sheet的数据
     * @param int   $index sheet下标，从0开始
     * @param Sheet $config sheet配置
     * @return static
     * @throws Exception
     */
    protected function create(int $index, Sheet $config) : static
    {
        $columns = $config->getColumns();
        $begin   = max($config->getStartRow(), 1);
        
        // 创建sheet
        if ($index > 0) {
            $this->spread->createSheet($index);
            $this->spread->setActiveSheetIndex($index);
        }
        
        // 获取当前工作集
        $sheet = $this->spread->getActiveSheet();
        if ('' !== $title = $config->getTitle()) {
            $sheet->setTitle($title);
        }
        
        // 设置标题栏
        foreach ($columns as $column) {
            $sheet->setCellValue($column->cellIndex($begin), $column->getName());
        }
        
        $rowIndex = $begin + 1;
        foreach ($config->getList() as $item) {
            foreach ($columns as $column) {
                $cellIndex = $column->cellIndex($rowIndex);
                $value     = ArrayHelper::get($item, $column->getField());
                $filter    = $column->getFilter();
                $drawing   = null;
                switch (true) {
                    // 字符串
                    case $filter === Column::FILTER_STRING:
                        $value = (string) $value;
                    break;
                    // 日期
                    case $filter === Column::FILTER_DATE && $value !== 0:
                        $value = Date::PHPToExcel($value);
                    break;
                    // 函数过滤
                    case is_string($filter) && function_exists($filter):
                        $value = call_user_func($filter, $value);
                    break;
                    // 自定义过滤
                    case is_callable($filter):
                        $value = call_user_func($filter, $value, $item, new FilterParameter($column, $cellIndex, $rowIndex));
                    break;
                }
                
                // 图片
                $drawings = [];
                if ($column->isImage()) {
                    foreach ((array) $value as $image) {
                        $drawing = new Drawing();
                        $drawing->setPath($image);
                        $drawing->setCoordinates($cellIndex);
                        $drawings[] = $drawing;
                    }
                } else {
                    if ($type = $column->getDataType()) {
                        $sheet->setCellValueExplicit($cellIndex, $value, $type);
                    } else {
                        $sheet->setCellValue($cellIndex, $value);
                    }
                    
                    // 格式化
                    $style = $sheet->getStyle($cellIndex);
                    if ($numberFormat = $column->getNumberFormat()) {
                        $style->getNumberFormat()->setFormatCode($numberFormat);
                    }
                    
                    // 自动换行
                    if ($column->isWrapText()) {
                        $style->getAlignment()->setWrapText(true);
                    }
                }
                
                // 单元格回调
                if (($cell = $config->getCell()) instanceof Closure) {
                    call_user_func($cell, $value, $item, new CellParameter($sheet, $drawings, $column, $cellIndex, $rowIndex));
                }
                
                // 写入图片
                if ($column->isImage()) {
                    foreach ($drawings as $drawing) {
                        if (!$drawing->getCoordinates()) {
                            $drawing->setCoordinates($cellIndex);
                        }
                        if (!$drawing->getWorksheet()) {
                            $drawing->setWorksheet($sheet);
                        }
                    }
                }
            }
            
            // 行回调
            if (($row = $config->getRow()) instanceof Closure) {
                call_user_func($row, $item, new RowParameter($sheet, $config, $rowIndex));
            }
            
            $rowIndex++;
        }
        
        // 处理回调
        if (($call = $config->getHandle()) instanceof Closure) {
            call_user_func($call, new SheetParameter($sheet, $config, $rowIndex));
        }
        
        return $this;
    }
    
    
    /**
     * 构建Excel
     * @throws Exception
     */
    protected function build() : IWriter
    {
        foreach ($this->sheets as $index => $sheet) {
            $this->create($index, $sheet);
        }
        $this->spread->setActiveSheetIndex(0);
        
        // 处理回调
        if ($this->handle instanceof Closure) {
            call_user_func($this->handle, new HandleParameter($this->spread, $this->sheets));
        }
        
        return IOFactory::createWriter($this->spread, ucfirst($this->type));
    }
    
    
    /**
     * 获取文件名
     * @param string $filename
     * @return string
     */
    protected function getFilename(string $filename) : string
    {
        $extension = $this->type;
        if (in_array($this->type, [self::TYPE_MPDF, self::TYPE_TCPDF, self::TYPE_DOMPDF])) {
            $extension = 'pdf';
        }
        
        if (false !== $index = mb_strrpos($filename, '.')) {
            $filename = mb_substr($filename, 0, $index);
        }
        
        return $filename . '.' . $extension;
    }
    
    
    /**
     * 获取mimetype
     * @return string
     */
    protected function getMimetype() : string
    {
        if (in_array($this->type, [self::TYPE_MPDF, self::TYPE_TCPDF, self::TYPE_DOMPDF])) {
            return 'application/pdf';
        }
        
        if ($this->type == self::TYPE_XLS) {
            return 'application/vnd.ms-excel';
        } elseif ($this->type == self::TYPE_XLSX) {
            return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } elseif ($this->type == self::TYPE_CSV) {
            return 'text/csv';
        }
        
        return '';
    }
    
    
    /**
     * 响应
     * @param string $filename 下载的文件名
     * @return Response
     * @throws Exception
     */
    public function response(string $filename) : Response
    {
        ob_start();
        $this->build()->save('php://output');
        $content = ob_get_clean();
        
        $response = new File($content);
        $response->isContent(true);
        $response->expire(0);
        $response->name($this->getFilename($filename));
        $response->mimeType($this->getMimetype());
        
        return $response;
    }
    
    
    /**
     * 保存
     * @param string $filename 保存的文件路径
     * @return string 保存的文件路径
     * @throws Exception
     */
    public function save(string $filename) : string
    {
        $this->build()->save($filename = $this->getFilename($filename));
        
        return $filename;
    }
}