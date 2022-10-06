<?php

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\model\ObjectOption;

/**
 * 删除角色组前获取参数事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/23 7:43 PM AdminGroupEventDeletePrepare.php $
 * @property int        $id 角色组ID
 * @property AdminGroup $model
 */
class AdminGroupEventDeletePrepare extends ObjectOption
{
    public function __construct(AdminGroup $model, int $id)
    {
        $this->model = $model;
        $this->id    = $id;
        
        parent::__construct();
    }
}