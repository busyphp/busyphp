<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\import\parameter;

use BusyPHP\office\excel\import\ImportColumn;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 导入单元格过滤回调参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/7 11:03 ImportFilterParameter.php $
 */
class ImportFilterParameter extends BaseParameter
{
    /**
     * 当前工作集对象
     * @var Worksheet
     */
    public Worksheet $worksheet;
    
    /**
     * 当前单元格下标
     * @var string
     */
    public string $cellIndex;
    
    /**
     * 当前行下标
     * @var int
     */
    public int $rowIndex;
    
    /**
     * 当前列对象
     * @var ImportColumn
     */
    public ImportColumn $column;
    
    
    /**
     * 构造函数
     * @param Worksheet    $sheet
     * @param string       $cellIndex
     * @param int          $rowIndex
     * @param ImportColumn $column
     */
    public function __construct(Worksheet $sheet, string $cellIndex, int $rowIndex, ImportColumn $column)
    {
        $this->worksheet = $sheet;
        $this->cellIndex = $cellIndex;
        $this->rowIndex  = $rowIndex;
        $this->column    = $column;
    }
}