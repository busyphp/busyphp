<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\import\parameter;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 导入总数据处理回调参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/7 20:00 ImportListParameter.php $
 */
class ImportListParameter
{
    /**
     * 当前工作集对象
     * @var Worksheet
     */
    public Worksheet $worksheet;
    
    /**
     * 获取的数据集
     * @var array
     */
    public array $list;
    
    /**
     * 最大可用行
     * @var int
     */
    public int $maxRow;
    
    /**
     * 最大可用列
     * @var string
     */
    public string $maxColumn;
    
    
    /**
     * 构造函数
     * @param Worksheet $worksheet
     * @param array     $list
     * @param int       $maxRow
     * @param string    $maxColumn
     */
    public function __construct(Worksheet $worksheet, array $list, int $maxRow, string $maxColumn)
    {
        $this->worksheet = $worksheet;
        $this->list      = $list;
        $this->maxRow    = $maxRow;
        $this->maxColumn = $maxColumn;
    }
}