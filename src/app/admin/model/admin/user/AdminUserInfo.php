<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\user;

use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\group\AdminGroupInfo;
use BusyPHP\helper\util\Str;
use BusyPHP\helper\util\Transform;
use BusyPHP\model\Entity;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 管理员信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午2:48 AdminUserInfo.php $
 * @method static Entity groupList($op = null, $value = null) 权限组数据
 * @method static Entity groupRulePaths($op = null, $value = null) 权限规则路径集合
 * @method static Entity groupRuleIds($op = null, $value = null) 权限规则ID集合
 * @method static Entity groupNames($op = null, $value = null) 权限名称集合
 * @method static Entity groupHasSystem($op = null, $value = null) 权限中是否包涵超级权限
 * @method static Entity formatCreateTime($op = null, $value = null) 格式化的创建时间
 * @method static Entity formatUpdateTime($op = null, $value = null) 格式化的更新时间
 * @method static Entity formatLastTime($op = null, $value = null) 格式化的上次登录时间
 * @method static Entity formatLoginTime($op = null, $value = null) 格式化的本次登录时间
 * @method static Entity isTempLock($op = null, $value = null) 是否临时锁定
 * @method static Entity formatErrorReleaseTime($op = null, $value = null) 格式化的锁定释放时间
 * @method static Entity defaultGroup($op = null, $value = null) 默认角色组信息
 * @method static Entity defaultMenu($op = null, $value = null) 默认菜单
 * @method static Entity skin($op = null, $value = null) 皮肤
 * @property array|string $theme
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
     * 是否已经临时锁定
     * @var bool
     */
    public $isTempLock;
    
    /**
     * 格式化的锁定释放时间
     * @var string
     */
    public $formatErrorRelease;
    
    /**
     * 默认角色组信息
     * @var AdminGroupInfo
     */
    public $defaultGroup;
    
    /**
     * 默认菜单
     * @var string
     */
    public $defaultMenu;
    
    /**
     * 皮肤
     * @var string
     */
    public $skin;
    
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
        
        $this->theme              = json_decode($this->theme, true) ?: [];
        $this->skin               = $this->theme['skin'] ?? '';
        $this->formatCreateTime   = Transform::date($this->createTime);
        $this->formatUpdateTime   = Transform::date($this->updateTime);
        $this->formatLastTime     = $this->lastTime > 0 ? Transform::date($this->lastTime) : '';
        $this->formatLoginTime    = $this->loginTime > 0 ? Transform::date($this->loginTime) : '';
        $this->checked            = $this->checked > 0;
        $this->system             = $this->system > 0;
        $this->isTempLock         = $this->errorRelease > time();
        $this->formatErrorRelease = $this->errorRelease > 0 ? Transform::date($this->errorRelease) : '';
        
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
            
            $groupInfo = static::$_groupList[$groupId];
            if (!$groupInfo->status) {
                continue;
            }
            
            $this->groupNames[]        = $groupInfo->name;
            $this->groupIds[]          = $groupId;
            $this->groupList[$groupId] = $groupInfo;
            $this->groupRuleIds        = array_merge($this->groupRuleIds, $groupInfo->ruleIds);
            $this->groupRulePaths      = array_merge($this->groupRulePaths, $groupInfo->rulePaths);
            if ($groupInfo->system) {
                $this->groupHasSystem = true;
            }
        }
        $this->groupRulePaths = array_map([Str::class, 'snake'], $this->groupRulePaths);
        
        if (!$this->defaultGroupId || !isset($this->groupList[$this->defaultGroupId])) {
            $this->defaultGroupId = end($this->groupIds);
        }
        
        $this->defaultGroup = $this->groupList[$this->defaultGroupId] ?? null;
        $this->defaultMenu  = $this->defaultGroup->defaultMenu->path ?? '';
    }
}