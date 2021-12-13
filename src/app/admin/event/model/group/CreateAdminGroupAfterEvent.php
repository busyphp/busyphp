<?php

namespace BusyPHP\app\admin\event\model\group;

use BusyPHP\app\admin\model\admin\group\AdminGroupField;
use BusyPHP\app\admin\model\admin\group\AdminGroupInfo;
use BusyPHP\model\ObjectOption;

/**
 * 创建角色组后事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 CreateAdminGroupAfterEvent.php $
 * @property AdminGroupField $data 提交的数据
 * @property AdminGroupInfo  $info 角色组
 */
class CreateAdminGroupAfterEvent extends ObjectOption
{
}