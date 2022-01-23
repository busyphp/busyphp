<?php

namespace BusyPHP\app\admin\event\model\group;

use BusyPHP\app\admin\model\admin\group\AdminGroupField;
use BusyPHP\model\ObjectOption;

/**
 * 创建角色组前获取参数事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/23 7:41 PM CreateAdminGroupTakeParamsEvent.php $
 * @property AdminGroupField $data 提交的数据
 */
class CreateAdminGroupTakeParamsEvent extends ObjectOption
{
}