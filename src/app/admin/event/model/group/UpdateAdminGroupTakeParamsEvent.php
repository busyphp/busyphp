<?php

namespace BusyPHP\app\admin\event\model\group;

use BusyPHP\app\admin\model\admin\group\AdminGroupField;
use BusyPHP\model\ObjectOption;

/**
 * 更新角色组前获取参数事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/23 7:46 PM UpdateAdminGroupTakeParamsEvent.php $
 * @property AdminGroupField $data 提交的数据
 * @property int             $operate 操作类型
 */
class UpdateAdminGroupTakeParamsEvent extends ObjectOption
{
    /** @var int 更新操作 */
    const OPERATE_DEFAULT = 0;
    
    /** @var int 更新状态操作 */
    const OPERATE_STATUS = 1;
}