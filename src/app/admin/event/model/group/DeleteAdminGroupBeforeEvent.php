<?php

namespace BusyPHP\app\admin\event\model\group;

use BusyPHP\app\admin\model\admin\group\AdminGroupInfo;

/**
 * 删除角色组前事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 DeleteAdminGroupBeforeEvent.php $
 * @property AdminGroupInfo $info 删除前的角色组数据
 * @property mixed          $takeParams 通过 {@see DeleteAdminGroupTakeParamsEvent} 获取的数据
 */
class DeleteAdminGroupBeforeEvent extends DeleteAdminGroupTakeParamsEvent
{
}