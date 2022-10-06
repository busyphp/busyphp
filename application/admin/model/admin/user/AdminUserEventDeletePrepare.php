<?php

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\model\ObjectOption;

/**
 * 删除管理员前获取参数事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 AdminUserEventDeletePrepare.php $
 * @property AdminUser $model 用户模型
 * @property int       $id 删除的管理员ID
 */
class AdminUserEventDeletePrepare extends ObjectOption
{
    public function __construct(AdminUser $model, int $id)
    {
        $this->id    = $id;
        $this->model = $model;
        
        parent::__construct();
    }
}