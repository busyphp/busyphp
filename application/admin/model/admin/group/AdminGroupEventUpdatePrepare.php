<?php

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\model\ObjectOption;

/**
 * 更新角色组前获取参数事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/23 7:46 PM AdminGroupEventUpdatePrepare.php $
 * @property AdminGroupField $data 提交的数据
 * @property string          $scene 操作类型
 * @property AdminGroup      $model
 */
class AdminGroupEventUpdatePrepare extends ObjectOption
{
    public function __construct(AdminGroup $model, AdminGroupField $data, string $scene)
    {
        $this->model = $model;
        $this->data  = $data;
        $this->scene = $scene;
        
        parent::__construct();
    }
}