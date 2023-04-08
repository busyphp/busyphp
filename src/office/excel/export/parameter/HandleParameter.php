<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\export\parameter;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 导出工作集处理回调参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/6 20:46 HandleParameter.php $
 * @see Sheet::handle()
 */
class HandleParameter
{
    /**
     * 当前工作集对象
     * @var Worksheet
     */
    public Worksheet $worksheet;
    
    /**
     * 导出的数据集
     * @var array
     */
    public array $list;
    
    /**
     * 最新可用的行
     * @var int
     */
    public int $rowIndex;
    
    
    public function __construct(Worksheet $worksheet, array $list, int $rowIndex)
    {
        $this->worksheet = $worksheet;
        $this->list      = $list;
        $this->rowIndex  = $rowIndex;
    }
}