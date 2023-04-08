<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\export\parameter;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 导出行处理回调参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/6 20:42 RowParameter.php $
 * @see Sheet::row()
 */
class RowParameter
{
    /**
     * 当前工作集对象
     * @var Worksheet
     */
    public Worksheet $worksheet;
    
    /**
     * 当前所在行
     * @var int
     */
    public int $rowIndex;
    
    
    public function __construct(Worksheet $worksheet, int $rowIndex)
    {
        $this->worksheet = $worksheet;
        $this->rowIndex  = $rowIndex;
    }
}