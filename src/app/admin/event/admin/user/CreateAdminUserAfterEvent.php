<?php

namespace BusyPHP\app\admin\event\admin\user;

use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\model\ObjectOption;

/**
 * 创建管理员后事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 CreateAdminUserAfterEvent.php $
 * @property AdminUserField $data 提交的数据
 * @property AdminUserInfo  $info 管理员信息
 */
class CreateAdminUserAfterEvent extends ObjectOption
{
}