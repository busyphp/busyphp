<?php

namespace BusyPHP\app\admin\plugin\autocomplete;

use BusyPHP\app\admin\plugin\AutocompletePlugin;
use BusyPHP\Model;
use BusyPHP\model\Field;

/**
 * Autocomplete 插件回调
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/2 下午下午4:23 AutocompleteHandler.php $
 * @deprecated
 */
abstract class AutocompleteHandler
{
    /**
     * 查询处理
     * @param AutocompletePlugin $plugin 插件对象
     * @param Model              $model 查询模型
     */
    public function query(AutocompletePlugin $plugin, Model $model) : void
    {
    }
    
    
    /**
     * 数据项处理
     * @param Field|array $item 数据项
     * @param bool        $group 是否分组
     * @return string
     */
    public function text($item, bool $group) : string
    {
        return '';
    }
}