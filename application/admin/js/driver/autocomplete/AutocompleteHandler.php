<?php

namespace BusyPHP\app\admin\js\driver\autocomplete;

use BusyPHP\app\admin\js\driver\Autocomplete;
use BusyPHP\app\admin\js\Handler;
use BusyPHP\model\Field;

/**
 * JS组件[busyAdmin.plugins.Autocomplete] 处理回调
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 00:01 AutocompleteHandler.php $
 * @see Autocomplete
 * @property Autocomplete $driver
 */
abstract class AutocompleteHandler extends Handler
{
    /**
     * 查询处理回调
     */
    public function query()
    {
    }
    
    
    /**
     * 数据集单个Item处理回调
     * @param array|Field $item item
     * @param int         $index 数据下标
     * @return string
     */
    public function item($item, int $index) : string
    {
        return $item[$this->driver->getText()] ?? '';
    }
}