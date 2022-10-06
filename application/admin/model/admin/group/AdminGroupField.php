<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\interfaces\FieldObtainDataInterface;
use BusyPHP\interfaces\FieldSceneValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use think\Validate;

/**
 * 用户组模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午12:54 AdminGroupField.php $
 * @method static Entity id(mixed $op = null, mixed $condition = null) ID
 * @method static Entity parentId(mixed $op = null, mixed $condition = null) 上级权限ID
 * @method static Entity name(mixed $op = null, mixed $condition = null) 权限名称
 * @method static Entity defaultMenuId(mixed $op = null, mixed $condition = null) 默认菜单ID
 * @method static Entity system(mixed $op = null, mixed $condition = null) 是否系统权限
 * @method static Entity rule(mixed $op = null, mixed $condition = null) 权限规则ID集合，英文逗号分割，左右要有逗号
 * @method static Entity status(mixed $op = null, mixed $condition = null) 是否启用
 * @method static Entity sort(mixed $op = null, mixed $condition = null) 排序
 * @method $this setId(mixed $id) 设置ID
 * @method $this setParentId(mixed $parentId) 设置上级权限ID
 * @method $this setName(mixed $name) 设置权限名称
 * @method $this setDefaultMenuId(mixed $defaultMenuId) 设置默认菜单ID
 * @method $this setSystem(mixed $system) 设置是否系统权限
 * @method $this setRule(mixed $rule) 设置权限规则ID集合，英文逗号分割，左右要有逗号
 * @method $this setStatus(mixed $status) 设置是否启用
 * @method $this setSort(mixed $sort) 设置排序
 */
class AdminGroupField extends Field implements FieldSceneValidateInterface, FieldObtainDataInterface
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
     * 角色名称
     * @var string
     * @busy-validate require#请输入:attribute
     */
    public $name;
    
    /**
     * 默认菜单
     * @var int
     * @busy-validate require#请选择:attribute
     * @busy-validate gt:0#请选择:attribute
     */
    public $defaultMenuId;
    
    /**
     * 是否系统权限
     * @var bool
     */
    public $system;
    
    /**
     * 角色权限
     * @var array
     * @busy-validate require#请选择:attribute
     * @busy-validate array
     * @busy-validate min:1#请至少选择:rule个:attribute
     * @busy-array "," true
     */
    public $rule;
    
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
     * @inheritDoc
     */
    public function onSceneValidate(Model $model, Validate $validate, string $name)
    {
        switch ($name) {
            case AdminGroup::SCENE_CREATE:
                $this->setId(0);
                $this->retain($validate, [
                    $this::name(),
                    $this::rule(),
                    $this::defaultMenuId(),
                    $this::status()
                ]);
                
                return true;
            case AdminGroup::SCENE_UPDATE:
                $this->retain($validate, [
                    $this::id(),
                    $this::name(),
                    $this::rule(),
                    $this::defaultMenuId(),
                    $this::status()
                ]);
                
                return true;
        }
        
        return false;
    }
    
    
    /**
     * @inheritDoc
     * @throws
     */
    public function onObtainData(string $field, string $property, array $attrs, $value)
    {
        if ($field == $this::rule()) {
            $idList  = SystemMenu::init()->getIdList();
            $newRule = [];
            foreach ($value as $id) {
                if (!isset($idList[$id])) {
                    continue;
                }
                $newRule[] = $id;
            }
            
            return $newRule;
        }
        
        return $value;
    }
}