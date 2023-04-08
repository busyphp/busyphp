<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\export\parameter;

use BusyPHP\office\excel\export\Column;

/**
 * 导出过滤回调参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/7 20:14 FilterParameter.php $
 * @see Column::$filter
 */
class FilterParameter
{
    /**
     * 当前所在行
     * @var int
     */
    public int $rowIndex;
    
    /**
     * 当前所在列对象
     * @var Column
     */
    public Column $column;
    
    /**
     * 当前单元格下标
     * @var string
     */
    public string $cellIndex;
    
    
    /**
     * @param Column $column
     * @param string $cellIndex
     * @param int    $rowIndex
     */
    public function __construct(Column $column, string $cellIndex, int $rowIndex)
    {
        $this->rowIndex  = $rowIndex;
        $this->column    = $column;
        $this->cellIndex = $cellIndex;
    }
}