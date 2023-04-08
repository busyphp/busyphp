<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\export\parameter;

use BusyPHP\office\excel\export\ExportSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 导出工作集处理回调参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/6 20:46 ExportSheetParameter.php $
 * @see ExportSheet::sheet()
 */
class ExportSheetParameter
{
    /**
     * 当前工作集对象
     * @var Worksheet
     */
    public Worksheet $worksheet;
    
    /**
     * 最新可用的行
     * @var int
     */
    public int $rowIndex;
    
    /**
     * 工作集配置
     * @var ExportSheet
     */
    public ExportSheet $sheet;
    
    
    /**
     * @param Worksheet   $worksheet
     * @param ExportSheet $sheet
     * @param int         $rowIndex
     */
    public function __construct(Worksheet $worksheet, ExportSheet $sheet, int $rowIndex)
    {
        $this->worksheet = $worksheet;
        $this->rowIndex  = $rowIndex;
        $this->sheet     = $sheet;
    }
}