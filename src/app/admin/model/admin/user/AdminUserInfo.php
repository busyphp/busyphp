<?php

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\group\AdminGroupInfo;

/**
 * 管理员信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午2:48 AdminUserInfo.php $
 */
class AdminUserInfo extends AdminUserField
{
    /**
     * 是否审核
     * @var bool
     */
    public $isChecked;
    
    /**
     * 所在用户组信息
     * @var AdminGroupInfo|null
     */
    public $group;
    
    /**
     * 用户组信息
     * @var AdminGroupInfo[]
     */
    private static $_groupList;
    
    
    public function onParseAfter()
    {
        if (!isset(static::$_groupList)) {
            static::$_groupList = AdminGroup::init()->getList();
        }
        
        $this->isChecked = $this->checked > 0;
        $this->isSystem  = $this->isSystem > 0;
        $this->group     = static::$_groupList[$this->groupId] ?? null;
    }
}