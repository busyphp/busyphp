<?php

namespace BusyPHP\app\admin\event\model\user;

/**
 * 创建管理员前事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 CreateAdminUserBeforeEvent.php $
 * @property mixed $takeParams 通过事件{@see CreateAdminUserTakeParamsEvent}取到的数据
 */
class CreateAdminUserBeforeEvent extends CreateAdminUserTakeParamsEvent
{
}