<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\model\system\menu\SystemMenuField;
use BusyPHP\interfaces\FieldGetModelDataInterface;
use BusyPHP\interfaces\ModelValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\Separate;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\Validator;
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
 * @method static Entity child() 子节点数据
 * @method static Entity ruleIds() 权限ID集合
 * @method static Entity ruleIndeterminate() 权限所有父节点ID集合
 * @method static Entity rulePaths() 权限地址集合
 * @method static Entity defaultMenuName() 默认菜单名称
 * @method static Entity defaultMenu() 默认菜单信息
 * @method $this setId(mixed $id) 设置ID
 * @method $this setParentId(mixed $parentId) 设置上级权限ID
 * @method $this setName(mixed $name) 设置权限名称
 * @method $this setDefaultMenuId(mixed $defaultMenuId) 设置默认菜单
 * @method $this setSystem(mixed $system) 设置是否系统权限
 * @method $this setRule(mixed $rule) 设置权限规则集合，英文逗号分割，左右要有逗号
 * @method $this setStatus(mixed $status) 设置是否启用
 * @method $this setSort(mixed $sort) 设置排序
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
class AdminGroupField extends Field implements ModelValidateInterface, FieldGetModelDataInterface
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
     */
    #[Validator(name: Validator::REQUIRE, msg: '请输入:attribute')]
    public $name;
    
    /**
     * 默认菜单
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '请选择:attribute')]
    public $defaultMenuId;
    
    /**
     * 是否系统权限
     * @var bool
     */
    public $system;
    
    /**
     * 角色权限
     * @var array
     */
    #[Separate(separator: ',', full: true)]
    #[Validator(name: Validator::REQUIRE, msg: '请选择:attribute')]
    #[Validator(name: Validator::MIN, rule: 1, msg: '请至少选择:rule个:attribute')]
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
     * 权限所有父节点ID集合
     * @var array
     */
    #[Ignore]
    public $ruleIndeterminate;
    
    /**
     * 权限ID集合
     * @var array
     */
    #[Ignore]
    public $ruleIds;
    
    /**
     * 子节点数据
     * @var AdminGroupField[]
     */
    #[Ignore]
    public $child = [];
    
    /**
     * 权限地址集合
     * @var array
     */
    #[Ignore]
    public $rulePaths = [];
    
    /**
     * 默认菜单名称
     * @var string
     */
    #[Ignore]
    public $defaultMenuName;
    
    /**
     * 默认菜单信息
     * @var SystemMenuField
     */
    #[Ignore]
    public $defaultMenu;
    
    
    /**
     * @inheritDoc
     * @param AdminGroupField $data
     */
    public function onModelValidate(Model $model, Validate $validate, string $scene, $data = null)
    {
        switch ($scene) {
            case AdminGroup::SCENE_CREATE:
                $this->setId(0);
                $this->retain($validate, [
                    $this::parentId(),
                    $this::name(),
                    $this::rule(),
                    $this::defaultMenuId(),
                    $this::status()
                ]);
                
                return true;
            case AdminGroup::SCENE_UPDATE:
                if ($data->system) {
                    $this->retain($validate, [
                        $this::id(),
                        $this::name(),
                        $this::rule(),
                        $this::defaultMenuId(),
                    ]);
                } else {
                    $this->retain($validate, [
                        $this::id(),
                        $this::parentId(),
                        $this::name(),
                        $this::rule(),
                        $this::defaultMenuId(),
                        $this::status()
                    ]);
                }
                
                return true;
        }
        
        return false;
    }
    
    
    protected function onParseAfter()
    {
        $menuModel  = SystemMenu::instance();
        $hashParent = $menuModel->getHashParentMap();
        $hashMap    = $menuModel->getHashMap();
        
        // 遍历权限剔除失效节点
        $rule            = [];
        $this->rulePaths = [];
        foreach ($this->rule as $item) {
            if (!isset($hashMap[$item]) || $hashMap[$item]->disabled) {
                continue;
            }
            $rule[]            = $item;
            $this->rulePaths[] = $hashMap[$item]->routePath;
        }
        
        $this->rule            = $rule;
        $this->ruleIds         = $rule;
        $this->defaultMenu     = $hashMap[$this->defaultMenuId] ?? null;
        $this->defaultMenuName = $this->defaultMenu->name ?? '';
        
        // 计算权限所有父节点ID集合
        $this->ruleIndeterminate = [];
        foreach ($this->rule as $item) {
            if (isset($hashParent[$item])) {
                foreach ($hashParent[$item] as $hash) {
                    if (!in_array($hash, $this->ruleIndeterminate)) {
                        $this->ruleIndeterminate[] = $hash;
                        $this->ruleIds[]           = $hash;
                    }
                }
            }
        }
    }
    
    
    /**
     * @inheritDoc
     * @throws
     */
    public function onGetModelData(string $field, string $property, array $attrs, $value)
    {
        if ($field == $this::rule()) {
            $hashMap = SystemMenu::init()->getHashMap();
            $newRule = [];
            foreach ((array) $value as $hash) {
                if (!isset($hashMap[$hash])) {
                    continue;
                }
                $newRule[] = $hash;
            }
            
            return $newRule;
        }
        
        return $value;
    }
}