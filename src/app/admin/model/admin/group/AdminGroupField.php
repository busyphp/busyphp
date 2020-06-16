<?php

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\exception\VerifyException;
use BusyPHP\model\Field;
use BusyPHP\helper\util\Transform;


/**
 * 用户组模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2018 busy^life <busy.life@qq.com>
 * @version $Id: 2018-01-17 下午4:53 AdminGroupField.php busy^life $
 */
class AdminGroupField extends Field
{
    /** @var int */
    public $id = null;
    /** @var string 权限名称 */
    public $name = null;
    /** @var string 权限规则 */
    public $rule = null;
    /** @var int 是否系统权限 */
    public $isSystem = null;
    /** @var string 默认面板 */
    public $defaultGroup = null;
    
    
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