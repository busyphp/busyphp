<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\export\parameter;

use BusyPHP\office\excel\export\ExportColumn;

/**
 * 导出过滤回调参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/7 20:14 ExportFilterParameter.php $
 * @see ExportColumn::$filter
 */
class ExportFilterParameter
{
    /**
     * 当前所在行
     * @var int
     */
    public int $rowIndex;
    
    /**
     * 当前所在列对象
     * @var ExportColumn
     */
    public ExportColumn $column;
    
    /**
     * 当前单元格下标
     * @var string
     */
    public string $cellIndex;
    
    
    /**
     * @param ExportColumn $column
     * @param string       $cellIndex
     * @param int          $rowIndex
     */
    public function __construct(ExportColumn $column, string $cellIndex, int $rowIndex)
    {
        $this->rowIndex  = $rowIndex;
        $this->column    = $column;
        $this->cellIndex = $cellIndex;
    }
}