<?php

namespace BusyPHP\app\admin\plugin\selectPicker;

use BusyPHP\app\admin\plugin\SelectPickerPlugin;
use BusyPHP\Model;
use BusyPHP\model\Field;

/**
 * SelectPicker 插件回调
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/2 下午下午4:28 SelectPickerHandler.php $
 * @deprecated
 */
abstract class SelectPickerHandler
{
    /**
     * 查询处理
     * @param SelectPickerPlugin $plugin 插件对象
     * @param Model              $model 查询模型
     */
    public function query(SelectPickerPlugin $plugin, Model $model) : void
    {
    }
    
    
    /**
     * ID处理
     * @param Field|array $item 数据项
     * @param bool        $group 是否分组
     * @return string
     */
    public function id($item, bool $group) : string
    {
        return '';
    }
    
    
    /**
     * text处理
     * @param Field|array $item 数据项
     * @param bool        $group 是否分组
     * @return string
     */
    public function text($item, bool $group) : string
    {
        return '';
    }
}