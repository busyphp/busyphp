<?php

namespace BusyPHP\app\admin\plugin\linkagePicker;

use BusyPHP\app\admin\plugin\LinkagePickerPlugin;
use BusyPHP\Model;
use BusyPHP\model\Field;

/**
 * LinkageHandler
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/3/13 9:24 AM LinkageHandler.php $
 */
abstract class LinkageHandler
{
    /**
     * 节点处理回调
     * @param Field|array     $item 数据项
     * @param LinkageFlatItem $node 节点项
     */
    public function node($item, LinkageFlatItem $node) : void
    {
    }
    
    
    /**
     * 查询处理回调
     * @param LinkagePickerPlugin $plugin 插件对象
     * @param Model               $model 查询模型
     */
    public function query(LinkagePickerPlugin $plugin, Model $model) : void
    {
    }
}