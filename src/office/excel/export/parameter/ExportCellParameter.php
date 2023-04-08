<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\export\parameter;

use BusyPHP\office\excel\export\ExportColumn;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 导出单元格处理回调参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/6 20:40 ExportCellParameter.php $
 * @see ExportSheet::cell()
 */
class ExportCellParameter
{
    /**
     * 当前工作集对象
     * @var Worksheet
     */
    public Worksheet $worksheet;
    
    /**
     * 图片处理对象集
     * @var Drawing[]
     */
    public array $drawings;
    
    /**
     * 列对象
     * @var ExportColumn
     */
    public ExportColumn $column;
    
    /**
     * 当前所在列
     * @var string
     */
    public string $cellIndex;
    
    /**
     * 当前所在行
     * @var int
     */
    public int $rowIndex;
    
    
    public function __construct(Worksheet $worksheet, array $drawings, ExportColumn $column, string $cellIndex, int $rowIndex)
    {
        $this->worksheet = $worksheet;
        $this->drawings  = $drawings;
        $this->column    = $column;
        $this->cellIndex = $cellIndex;
        $this->rowIndex  = $rowIndex;
    }
}