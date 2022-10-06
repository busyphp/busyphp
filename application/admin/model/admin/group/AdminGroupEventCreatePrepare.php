<?php

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\model\ObjectOption;

/**
 * 创建角色组前获取参数事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/23 7:41 PM AdminGroupEventCreatePrepare.php $
 * @property AdminGroupField $data 提交的数据
 * @property AdminGroup      $model 模型
 */
class AdminGroupEventCreatePrepare extends ObjectOption
{
    public function __construct(AdminGroup $model, AdminGroupField $data)
    {
        $this->model = $model;
        $this->data  = $data;
        
        parent::__construct();
    }
}