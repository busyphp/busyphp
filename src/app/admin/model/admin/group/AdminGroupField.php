<?php

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Filter;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use BusyPHP\helper\util\Transform;

/**
 * 用户组模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午12:54 AdminGroupField.php $
 * @method static Entity id($op = null, $value = null) ID
 * @method static Entity parentId($op = null, $value = null) 上级权限ID
 * @method static Entity name($op = null, $value = null) 权限名称
 * @method static Entity rule($op = null, $value = null) 权限规则ID集合，英文逗号分割，左右要有逗号
 * @method static Entity system($op = null, $value = null) 是否系统权限
 * @method static Entity defaultMenuId($op = null, $value = null) 默认菜单ID
 * @method static Entity status($op = null, $value = null) 是否启用
 * @method static Entity sort($op = null, $value = null) 排序
 */
class AdminGroupField extends Field
{
    /**
     * ID
     * @var int
     */
    public $id;
    
    /**
     * 上级权限ID
     * @var int
     */
    public $parentId;
    
    /**
     * 权限名称
     * @var string
     */
    public $name;
    
    /**
     * 权限规则ID集合，英文逗号分割，左右要有逗号
     * @var string|array
     */
    public $rule;
    
    /**
     * 是否系统权限
     * @var int
     */
    public $system;
    
    /**
     * 默认菜单
     * @var string
     */
    public $defaultMenuId;
    
    /**
     * 是否启用
     * @var int
     */
    public $status;
    
    /**
     * 排序
     * @var int
     */
    public $sort;
    
    
    /**
     * 设置
     * @param int $id
     * @return $this
     * @throws VerifyException
     */
    public function setId($id) : self
    {
        $this->id = floatval($id);
        if ($this->id < 1) {
            throw new VerifyException('缺少参数', 'id');
        }
        
        return $this;
    }
    
    
    /**
     * 设置权限名称
     * @param string $name
     * @return $this
     * @throws VerifyException
     */
    public function setName($name) : self
    {
        $this->name = trim($name);
        if (!$this->name) {
            throw new VerifyException('请输入角色名称', 'name');
        }
        
        return $this;
    }
    
    
    /**
     * 设置权限规则
     * @param array $rule
     * @return $this
     * @throws VerifyException
     */
    public function setRule(array $rule) : self
    {
        $rule = array_map('intval', $rule);
        $rule = Filter::trimArray($rule);
        if (!$rule) {
            throw new VerifyException('请选择角色权限', 'rule');
        }
        
        $this->rule = "," . implode(',', $rule) . ",";
        
        return $this;
    }
    
    
    /**
     * 设置父角色组
     * @param int $parentId
     * @return $this
     */
    public function setParentId($parentId) : self
    {
        $this->parentId = intval($parentId);
        
        return $this;
    }
    
    
    /**
     * 设置默认面板
     * @param string $defaultMenuId
     * @return $this
     * @throws VerifyException
     */
    public function setDefaultMenuId($defaultMenuId) : self
    {
        $this->defaultMenuId = trim($defaultMenuId);
        if (!$this->defaultMenuId) {
            throw new VerifyException('请选择默认菜单', 'default_group');
        }
        
        return $this;
    }
    
    
    /**
     * 设置状态
     * @param int $status
     * @return $this
     */
    public function setStatus($status) : self
    {
        $this->status = Transform::dataToBool($status);
        
        return $this;
    }
}