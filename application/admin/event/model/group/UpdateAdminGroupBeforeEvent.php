<?php

namespace BusyPHP\app\admin\event\model\group;

use BusyPHP\app\admin\model\admin\group\AdminGroupInfo;

/**
 * 更新角色组前事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 UpdateAdminGroupBeforeEvent.php $
 * @property AdminGroupInfo $info 更新前的角色数据
 * @property mixed          $takeParams 通过 {@see UpdateAdminGroupTakeParamsEvent} 获取的数据
 */
class UpdateAdminGroupBeforeEvent extends UpdateAdminGroupTakeParamsEvent
{
}