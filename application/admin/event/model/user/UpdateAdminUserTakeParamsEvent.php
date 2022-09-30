<?php

namespace BusyPHP\app\admin\event\model\user;

use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\model\ObjectOption;

/**
 * 更新管理员前获取参数事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/23 7:27 PM UpdateAdminUserTakeParamsEvent.php $
 * @property AdminUserField $data 提交的数据
 * @property int            $operate 操作类型
 */
class UpdateAdminUserTakeParamsEvent extends ObjectOption
{
    /** @var int 更新操作 */
    const OPERATE_DEFAULT = 0;
    
    /** @var int 更新密码操作 */
    const OPERATE_PASSWORD = 1;
    
    /** @var int 更新状态操作 */
    const OPERATE_CHECKED = 2;
    
    /** @var int 解锁操作 */
    const OPERATE_UNLOCK = 3;
    
    /** @var int 设置主题操作 */
    const OPERATE_THEME = 4;
    
    /** @var int 登录成功操作 */
    const OPERATE_LOGIN = 4;
}