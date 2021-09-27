<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\model\system\menu\SystemMenuInfo;
use BusyPHP\helper\util\Transform;
use BusyPHP\model\Entity;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 管理员用户组信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午12:57 AdminGroupInfo.php $
 * @method static Entity child() 子节点数据
 * @method static Entity ruleIds() 权限ID集合
 * @method static Entity ruleIndeterminate() 权限所有父节点ID集合
 * @method static Entity rulePaths() 权限地址集合
 */
class AdminGroupInfo extends AdminGroupField
{
    /**
     * 权限所有父节点ID集合
     * @var array
     */
    public $ruleIndeterminate;
    
    /**
     * 权限ID集合
     * @var array
     */
    public $ruleIds;
    
    /**
     * 子节点数据
     * @var AdminGroupInfo[]
     */
    public $child = [];
    
    /**
     * 权限地址集合
     * @var array
     */
    public $rulePaths = [];
    
    /**
     * @var array
     */
    private static $_menuIdParents;
    
    /**
     * @var SystemMenuInfo[]
     */
    private static $_menuIdList;
    
    
    /**
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function onParseAfter()
    {
        if (!is_array(self::$_menuIdParents)) {
            self::$_menuIdParents = SystemMenu::init()->getIdParens();
        }
        if (!is_array(self::$_menuIdList)) {
            self::$_menuIdList = SystemMenu::init()->getIdList();
        }
        
        $this->system = Transform::dataToBool($this->system);
        
        // 遍历权限剔除失效节点
        $rule            = [];
        $this->rulePaths = [];
        foreach (explode(',', $this->rule) as $ruleId) {
            if (!isset(self::$_menuIdParents[$ruleId])) {
                continue;
            }
            $rule[] = intval($ruleId);
            
            if (isset(self::$_menuIdList[$ruleId]) && !self::$_menuIdList[$ruleId]->disabled) {
                $this->rulePaths[] = self::$_menuIdList[$ruleId]->path;
            }
        }
        $this->rule    = $rule;
        $this->ruleIds = $rule;
        
        
        // 计算权限所有父节点ID集合
        $this->ruleIndeterminate = [];
        foreach ($this->rule as $ruleId) {
            if (isset(self::$_menuIdParents[$ruleId])) {
                foreach (self::$_menuIdParents[$ruleId] as $id) {
                    if (!in_array($id, $this->ruleIndeterminate)) {
                        $this->ruleIndeterminate[] = $id;
                        $this->ruleIds[]           = $id;
                    }
                }
            }
        }
    }
}