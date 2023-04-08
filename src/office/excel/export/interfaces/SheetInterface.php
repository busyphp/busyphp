<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\export\interfaces;

use BusyPHP\office\excel\Export;
use BusyPHP\office\excel\export\Sheet;

/**
 * Excel导出接口
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/7 21:46 SheetInterface.php $
 * @see Export::add()
 */
interface SheetInterface
{
    /**
     * 初始化导出工作集
     * @param Sheet $sheet
     */
    public function initExcelExportSheet(Sheet $sheet) : void;
}