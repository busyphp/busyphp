<?php

namespace BusyPHP\app\admin\event\model\user;

use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\model\ObjectOption;

/**
 * 创建管理员前事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 CreateAdminUserBeforeEvent.php $
 * @property AdminUserField $data 提交的数据
 */
class CreateAdminUserBeforeEvent extends ObjectOption
{
}