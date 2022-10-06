<?php

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\model\ObjectOption;

/**
 * 更新管理员前获取参数事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/23 7:27 PM AdminUserEventUpdatePrepare.php $
 * @property AdminUser      $model 用户模型
 * @property AdminUserField $data 提交的数据
 * @property string         $scene 操作场景
 */
class AdminUserEventUpdatePrepare extends ObjectOption
{
    public function __construct(AdminUser $model, AdminUserField $data, string $scene)
    {
        $this->model = $model;
        $this->data  = $data;
        $this->scene = $scene;
        
        parent::__construct();
    }
}