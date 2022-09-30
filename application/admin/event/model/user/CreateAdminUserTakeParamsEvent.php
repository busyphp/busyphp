<?php

namespace BusyPHP\app\admin\event\model\user;

use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\model\ObjectOption;

/**
 * 创建用户前参数获取事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/23 6:50 PM CreateAdminUserParamsEvent.php $
 * @property AdminUserField $data 提交的数据
 */
class CreateAdminUserTakeParamsEvent extends ObjectOption
{
}