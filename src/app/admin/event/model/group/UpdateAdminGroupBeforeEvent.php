<?php

namespace BusyPHP\app\admin\event\model\group;

use BusyPHP\app\admin\model\admin\group\AdminGroupField;
use BusyPHP\app\admin\model\admin\group\AdminGroupInfo;
use BusyPHP\model\ObjectOption;

/**
 * 更新角色组前事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 UpdateAdminGroupBeforeEvent.php $
 * @property AdminGroupField $data 更新的数据
 * @property AdminGroupInfo  $info 更新前的数据
 * @property int             $operate 操作类型
 */
class UpdateAdminGroupBeforeEvent extends ObjectOption
{
    /** @var int 更新操作 */
    const OPERATE_DEFAULT = 0;
    
    /** @var int 更新状态操作 */
    const OPERATE_STATUS = 1;
}