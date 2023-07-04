<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\model\system\menu\SystemMenuField;
use BusyPHP\interfaces\FieldGetModelDataInterface;
use BusyPHP\interfaces\ModelValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\Separate;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\Validator;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use think\Validate;
use think\validate\ValidateRule;

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
 * @method static Entity remark(mixed $op = null, mixed $condition = null) 描述
 * @method static Entity child() 子节点数据
 * @method static Entity ruleIds() 权限ID集合
 * @method static Entity ruleIndeterminate() 权限所有父节点ID集合
 * @method static Entity rulePaths() 权限地址集合
 * @method static Entity defaultMenuName() 默认菜单名称
 * @method static Entity defaultMenu() 默认菜单信息
 * @method $this setId(mixed $id, bool|ValidateRule[] $validate = false) 设置ID
 * @method $this setParentId(mixed $parentId, bool|ValidateRule[] $validate = false) 设置上级权限ID
 * @method $this setName(mixed $name, bool|ValidateRule[] $validate = false) 设置权限名称
 * @method $this setDefaultMenuId(mixed $defaultMenuId, bool|ValidateRule[] $validate = false) 设置默认菜单
 * @method $this setSystem(mixed $system, bool|ValidateRule[] $validate = false) 设置是否系统权限
 * @method $this setRule(mixed $rule, bool|ValidateRule[] $validate = false) 设置权限规则集合，英文逗号分割，左右要有逗号
 * @method $this setStatus(mixed $status, bool|ValidateRule[] $validate = false) 设置是否启用
 * @method $this setSort(mixed $sort, bool|ValidateRule[] $validate = false) 设置排序
 * @method $this setRemark(mixed $sort, bool|ValidateRule[] $validate = false) 设置描述
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
class AdminGroupField extends Field implements ModelValidateInterface, FieldGetModelDataInterface
{
    /**
     * ID
     * @var int
     */
    #[Validator(name: Validator::REQUIRE)]
    #[Validator(name: Validator::IS_NUMBER)]
    #[Validator(name: Validator::GT, rule: 0)]
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
    #[Filter(filter: 'trim')]
    public $name;
    
    /**
     * 默认菜单
     * @var string
     */
    #[Filter(filter: 'trim')]
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
     * 描述
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $remark;
    
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
     * 添加场景保留字段
     * @param AdminGroup $model 角色组模型
     * @return array
     */
    protected function createRetain(AdminGroup $model) : array
    {
        return [
            $this::parentId(),
            $this::name(),
            $this::rule(),
            $this::defaultMenuId(),
            $this::remark(),
            $this::status()
        ];
    }
    
    
    /**
     * 修改场景保留字段
     * @param AdminGroup      $model 角色模型
     * @param AdminGroupField $data 更新前角色组数据
     * @param bool            $system 是否系统角色组
     * @return array
     */
    protected function updateRetain(AdminGroup $model, AdminGroupField $data, bool $system) : array
    {
        if ($system) {
            return [
                $this::id(),
                $this::name(),
                $this::remark(),
            ];
        } else {
            return [
                $this::id(),
                $this::parentId(),
                $this::name(),
                $this::rule(),
                $this::remark(),
                $this::defaultMenuId(),
                $this::status()
            ];
        }
    }
    
    
    /**
     * 通用保留字段
     * @param AdminGroup           $model 角色模型
     * @param string               $scene 场景值
     * @param AdminGroupField|null $data 更新前的角色组数据
     * @return array
     */
    protected function commonRetain(AdminGroup $model, string $scene, ?AdminGroupField $data) : array
    {
        return [];
    }
    
    
    /**
     * @inheritDoc
     * @param AdminGroup      $model
     * @param AdminGroupField $data
     */
    public function onModelValidate(Model $model, Validate $validate, string $scene, $data = null)
    {
        $commonRetain = $this->commonRetain($model, $scene, $data);
        
        switch ($scene) {
            case $model::SCENE_CREATE:
                $this->retain($validate, $this->createRetain($model), $commonRetain);
                
                return true;
            case $model::SCENE_UPDATE:
                if (!$this->rule) {
                    $this->rule = [];
                }
                
                $this->retain($validate, $this->updateRetain($model, $data, $data->system), $commonRetain);
                
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
    public function onGetModelData(string $field, string $property, mixed $value) : mixed
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