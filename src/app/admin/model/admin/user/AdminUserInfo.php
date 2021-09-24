<?php

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\group\AdminGroupInfo;
use BusyPHP\helper\util\Transform;
use BusyPHP\model\Entity;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 管理员信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午2:48 AdminUserInfo.php $
 * @method static Entity groupList() 权限组数据
 * @method static Entity groupRulePaths() 权限规则路径集合
 * @method static Entity groupRuleIds() 权限规则ID集合
 * @method static Entity groupNames() 权限名称集合
 * @method static Entity groupHasSystem() 权限中是否包涵超级权限
 */
class AdminUserInfo extends AdminUserField
{
    /**
     * 权限组数据，以权限ID为下标
     * @var AdminGroupInfo[]
     */
    public $groupList = [];
    
    /**
     * 权限规则路径集合，未去重复
     * @var string[]
     */
    public $groupRulePaths = [];
    
    /**
     * 权限规则Id集合，未去重复
     * @var int[]
     */
    public $groupRuleIds = [];
    
    /**
     * 权限名称集合
     * @var string[]
     */
    public $groupNames = [];
    
    /**
     * 权限是否包涵超级权限
     * @var bool
     */
    public $groupHasSystem;
    
    /**
     * 格式化的创建时间
     * @var string
     */
    public $formatCreateTime;
    
    /**
     * 格式化的更新时间
     * @var string
     */
    public $formatUpdateTime;
    
    /**
     * 格式化的上次登录时间
     * @var string
     */
    public $formatLastTime;
    
    /**
     * 格式化的本次登录时间
     * @var string
     */
    public $formatLoginTime;
    
    /**
     * @var AdminGroupInfo[]
     */
    private static $_groupList;
    
    
    /**
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function onParseAfter()
    {
        if (!is_array(static::$_groupList)) {
            static::$_groupList = AdminGroup::init()->getIdList();
        }
        
        $this->formatCreateTime = Transform::date($this->createTime);
        $this->formatUpdateTime = Transform::date($this->updateTime);
        $this->formatLastTime   = Transform::date($this->lastTime);
        $this->formatLoginTime  = Transform::date($this->loginTime);
        $this->checked          = $this->checked > 0;
        $this->system           = $this->system > 0;
        
        $groupIds             = explode(',', $this->groupIds);
        $this->groupIds       = [];
        $this->groupList      = [];
        $this->groupRuleIds   = [];
        $this->groupRulePaths = [];
        $this->groupNames     = [];
        $this->groupHasSystem = false;
        foreach ($groupIds as $i => $groupId) {
            $groupId = intval($groupId);
            if (!$groupId || !isset(static::$_groupList[$groupId])) {
                continue;
            }
            
            $groupInfo                 = static::$_groupList[$groupId];
            $this->groupNames[]        = $groupInfo->name;
            $this->groupIds[]          = $groupId;
            $this->groupList[$groupId] = $groupInfo;
            $this->groupRuleIds        = array_merge($this->groupRuleIds, $groupInfo->ruleIds);
            $this->groupRulePaths      = array_merge($this->groupRulePaths, $groupInfo->rulePaths);
            if ($groupInfo->system) {
                $this->groupHasSystem = true;
            }
        }
    }
}