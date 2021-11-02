<?php

namespace BusyPHP\app\admin\plugin\lists;

use BusyPHP\app\admin\plugin\ListPlugin;
use BusyPHP\Model;
use BusyPHP\model\Map;

/**
 * 列表查询回调
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/2 下午下午3:03 ListHandler.php $
 */
abstract class ListHandler extends BaseHandler
{
    /**
     * 条件查询处理
     * @param ListPlugin $plugin 插件
     * @param Model      $model 查询模型
     * @param Map        $data 查询条件数据
     */
    public function query(ListPlugin $plugin, Model $model, Map $data) : void
    {
    }
}