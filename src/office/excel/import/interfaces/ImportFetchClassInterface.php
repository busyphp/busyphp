<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\import\interfaces;

/**
 * ImportFetchClassInterface
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/19 12:42 ImportFetchClassInterface.php $
 */
interface ImportFetchClassInterface
{
    /**
     * 将读取的行数据转为对象
     * @param array|object $row
     * @return static
     */
    public static function onExcelImportFetchRowToThis(array|object $row) : static;
}