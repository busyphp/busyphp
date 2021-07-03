<?php

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\exception\VerifyException;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use BusyPHP\helper\util\Transform;


/**
 * 用户组模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午12:54 AdminGroupField.php $
 * @method static Entity id($op = null, $value = null) ID
 * @method static Entity name($op = null, $value = null) 权限名称
 * @method static Entity rule($op = null, $value = null) 权限规则
 * @method static Entity isSystem($op = null, $value = null) 是否系统权限
 * @method static Entity defaultGroup($op = null, $value = null) 默认面板
 */
class AdminGroupField extends Field
{
    /**
     * ID
     * @var int
     */
    public $id;
    
    /**
     * 权限名称
     * @var string
     */
    public $name;
    
    /**
     * 权限规则
     * @var string
     */
    public $rule;
    
    /**
     * 是否系统权限
     * @var int
     */
    public $isSystem;
    
    /**
     * 默认面板
     * @var string
     */
    public $defaultGroup;
    
    
    /**
     * 设置
     * @param int $id
     * @return $this
     * @throws VerifyException
     */
    public function setId($id)
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
    public function setName($name)
    {
        $this->name = trim($name);
        if (!$this->name) {
            throw new VerifyException('请输入权限组名称', 'name');
        }
        
        return $this;
    }
    
    
    /**
     * 设置权限规则
     * @param string|array $rule
     * @return $this
     */
    public function setRule($rule)
    {
        if (is_array($rule)) {
            $this->rule = implode(',', $rule);
        } else {
            $this->rule = trim($rule);
        }
        
        return $this;
    }
    
    
    /**
     * 设置是否系统权限
     * @param int $isSystem
     * @return $this
     */
    public function setIsSystem($isSystem)
    {
        $this->isSystem = Transform::dataToBool($isSystem);
        
        return $this;
    }
    
    
    /**
     * 设置默认面板
     * @param string $defaultGroup
     * @return $this
     * @throws VerifyException
     */
    public function setDefaultGroup($defaultGroup)
    {
        $this->defaultGroup = trim($defaultGroup);
        if (!$this->defaultGroup) {
            throw new VerifyException('请选择默认面板', 'default_group');
        }
        
        return $this;
    }
}