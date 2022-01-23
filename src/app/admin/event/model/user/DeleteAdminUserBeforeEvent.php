<?php

namespace BusyPHP\app\admin\event\model\user;

use BusyPHP\app\admin\model\admin\user\AdminUserInfo;

/**
 * 删除管理员前事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 DeleteAdminUserBeforeEvent.php $
 * @property AdminUserInfo $info 删除前的用户数据
 * @property mixed         $takeParams 通过事件{@see DeleteAdminUserTakeParamsEvent}取到的数据
 */
class DeleteAdminUserBeforeEvent extends DeleteAdminUserTakeParamsEvent
{
}