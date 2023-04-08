<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\export\parameter;

use BusyPHP\office\excel\Export;
use BusyPHP\office\excel\export\ExportSheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * 导出处理回调参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/8 14:34 ExportHandleParameter.php $
 * @see Export::handle()
 */
class ExportHandleParameter
{
    /**
     * Spreadsheet对象
     * @var Spreadsheet
     */
    public Spreadsheet $spreadsheet;
    
    /**
     * 工作集
     * @var ExportSheet[]
     */
    public array $sheets;
    
    
    /**
     * @param Spreadsheet   $spreadsheet
     * @param ExportSheet[] $sheets
     */
    public function __construct(Spreadsheet $spreadsheet, array $sheets)
    {
        $this->spreadsheet = $spreadsheet;
        $this->sheets      = $sheets;
    }
}