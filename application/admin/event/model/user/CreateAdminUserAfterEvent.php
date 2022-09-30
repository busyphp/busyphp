<?php

namespace BusyPHP\app\admin\event\model\user;

use BusyPHP\app\admin\model\admin\user\AdminUserInfo;

/**
 * 创建管理员后事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 CreateAdminUserAfterEvent.php $
 * @property AdminUserInfo $info 添加成功的用户信息
 */
class CreateAdminUserAfterEvent extends CreateAdminUserBeforeEvent
{
}