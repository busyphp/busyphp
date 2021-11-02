<?php

namespace BusyPHP\app\admin\plugin\table;

use BusyPHP\app\admin\plugin\lists\BaseHandler;
use BusyPHP\app\admin\plugin\TablePlugin;
use BusyPHP\Model;
use BusyPHP\model\Map;

/**
 * Table插件回调
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/2 下午下午3:34 TableHandler.php $
 */
abstract class TableHandler extends BaseHandler
{
    /**
     * 条件查询处理
     * @param TablePlugin $plugin 插件
     * @param Model       $model 查询模型
     * @param Map         $data 查询条件数据
     */
    public function query(TablePlugin $plugin, Model $model, Map $data) : void
    {
    }
}