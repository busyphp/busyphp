<?php

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\model\ObjectOption;

/**
 * 创建用户前参数获取事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/23 6:50 PM AdminUserEventCreatePrepare.php $
 * @property AdminUser      $model 用户模型
 * @property AdminUserField $data 提交的数据
 */
class AdminUserEventCreatePrepare extends ObjectOption
{
    public function __construct(AdminUser $model, AdminUserField $data)
    {
        $this->model = $model;
        $this->data  = $data;
        
        parent::__construct();
    }
}