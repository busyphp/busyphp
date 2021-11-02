<?php

namespace BusyPHP\app\admin\plugin\tree;

use BusyPHP\app\admin\plugin\TreePlugin;
use BusyPHP\Model;
use BusyPHP\model\Field;

/**
 * Tree插件处理回调
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/2 下午下午3:50 TreeHandler.php $
 */
abstract class TreeHandler
{
    /**
     * 节点处理回调
     * @param Field|array        $item 数据项
     * @param TreeFlatItemStruct $node 节点项
     */
    public function node($item, TreeFlatItemStruct $node) : void
    {
    }
    
    
    /**
     * 查询处理回调
     * @param TreePlugin $plugin 插件对象
     * @param Model      $model 查询模型
     */
    public function query(TreePlugin $plugin, Model $model) : void
    {
    }
}