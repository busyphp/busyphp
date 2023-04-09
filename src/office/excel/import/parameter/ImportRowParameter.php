<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\import\parameter;

use BusyPHP\office\excel\import\ImportException;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 导入行处理回调参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/7 11:11 ImportRowParameter.php $
 */
class ImportRowParameter extends BaseParameter
{
    /**
     * 当前工作集对象
     * @var Worksheet
     */
    public Worksheet $worksheet;
    
    /**
     * 当前行整理后的行数据
     * @var array|object
     */
    public array|object $data;
    
    /**
     * 当前行下标
     * @var int
     */
    public int $rowIndex;
    
    
    /**
     * 构造函数
     * @param Worksheet $worksheet
     * @param array     $data
     * @param int       $rowIndex
     */
    public function __construct(Worksheet $worksheet, array $data, int $rowIndex)
    {
        $this->worksheet = $worksheet;
        $this->data      = $data;
        $this->rowIndex  = $rowIndex;
    }
}