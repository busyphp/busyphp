<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\menu;

use BusyPHP\interfaces\FieldSetValueInterface;
use BusyPHP\interfaces\ModelSceneValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use think\Validate;
use think\validate\ValidateRule;

/**
 * 后台菜单模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2017 busy^life <busy.life@qq.com>
 * @version $Id: 2017-06-06 下午5:27 SystemMenu.php busy^life $
 * @method static Entity id(mixed $op = null, mixed $condition = null) ID
 * @method static Entity name(mixed $op = null, mixed $condition = null) 名称
 * @method static Entity path(mixed $op = null, mixed $condition = null) 路由地址
 * @method static Entity parentPath(mixed $op = null, mixed $condition = null) 上级路由
 * @method static Entity topPath(mixed $op = null, mixed $condition = null) 顶级菜单默认访问路由地址
 * @method static Entity params(mixed $op = null, mixed $condition = null) 附加参数
 * @method static Entity icon(mixed $op = null, mixed $condition = null) 图标
 * @method static Entity target(mixed $op = null, mixed $condition = null) 打开方式
 * @method static Entity hide(mixed $op = null, mixed $condition = null) 是否隐藏
 * @method static Entity disabled(mixed $op = null, mixed $condition = null) 是否禁用
 * @method static Entity system(mixed $op = null, mixed $condition = null) 是否系统菜单
 * @method static Entity sort(mixed $op = null, mixed $condition = null) 自定义排序
 * @method $this setId(mixed $id) 设置ID
 * @method $this setName(mixed $name) 设置名称
 * @method $this setPath(mixed $path) 设置路由地址
 * @method $this setParentPath(mixed $parentPath) 设置上级路由
 * @method $this setTopPath(mixed $topPath) 设置顶级菜单默认访问路由地址
 * @method $this setParams(mixed $params) 设置附加参数
 * @method $this setIcon(mixed $icon) 设置图标
 * @method $this setTarget(mixed $target) 设置打开方式
 * @method $this setHide(mixed $hide) 设置是否隐藏
 * @method $this setDisabled(mixed $disabled) 设置是否禁用
 * @method $this setSystem(mixed $system) 设置是否系统菜单
 * @method $this setSort(mixed $sort) 设置自定义排序
 */
class SystemMenuField extends Field implements ModelSceneValidateInterface, FieldSetValueInterface
{
    /**
     * ID
     * @var int
     * @busy-validate require
     * @busy-validate gt:0
     */
    public $id;
    
    /**
     * 菜单名称
     * @var string
     * @busy-validate require#请输入:attribute
     * @busy-filter trim
     */
    public $name;
    
    /**
     * 菜单链接
     * @var string
     * @busy-validate require#请输入:attribute
     * @busy-filter trim
     */
    public $path;
    
    /**
     * 上级菜单
     * @var string
     * @busy-filter trim
     */
    public $parentPath;
    
    /**
     * 访问链接
     * @var string
     * @busy-filter trim
     */
    public $topPath;
    
    /**
     * 附加参数
     * @var string
     * @busy-filter trim
     */
    public $params;
    
    /**
     * 图标
     * @var string
     * @busy-filter trim
     */
    public $icon;
    
    /**
     * 打开方式
     * @var string
     * @busy-filter trim
     */
    public $target;
    
    /**
     * 是否隐藏
     * @var bool
     */
    public $hide;
    
    /**
     * 是否禁用
     * @var bool
     */
    public $disabled;
    
    /**
     * 是否系统菜单
     * @var bool
     */
    public $system;
    
    /**
     * 自定义排序
     * @var int
     */
    public $sort;
    
    
    /**
     * @inheritDoc
     */
    public function onModelSceneValidate(Model $model, Validate $validate, string $name, $data = null)
    {
        $validate
            ->append(
                $this::target(),
                ValidateRule::init()->in(array_keys(SystemMenu::getClass()::getTargets()), '请选择有效的:attribute')
            )
            ->append(
                $this::path(),
                ValidateRule::init()->unique($model)
            )
            ->append(
                $this::topPath(),
                ValidateRule::init()->closure(function($value) {
                    if (0 === strpos($value, '#') || false !== strpos($value, '://')) {
                        return false;
                    }
                    
                    
                    return true;
                }, ':attribute不能是锚连接或外部连接')->closure(function($value) use ($model) {
                    if (!$value) {
                        return true;
                    }
                    
                    if (!$model->whereEntity($this::path($value))->find()) {
                        return false;
                    }
                    
                    return true;
                }, ':attribute必须是已定义的菜单链接'));
        
        if ($name == SystemMenu::SCENE_CREATE) {
            $this->setId(0);
            $this->retain($validate, [
                $this::parentPath(),
                $this::name(),
                $this::icon(),
                $this::path(),
                $this::params(),
                $this::target(),
                $this::hide(),
                $this::disabled(),
                $this::topPath()
            ]);
            
            return true;
        } elseif ($name == SystemMenu::SCENE_AUTO_CREATE) {
            $this->setId(0);
            $this->setHide(true);
            $this->retain($validate, [
                $this::parentPath(),
                $this::name(),
                $this::path(),
                $this::hide(),
            ]);
            
            return true;
        } elseif ($name == SystemMenu::SCENE_UPDATE) {
            $this->retain($validate, [
                $this::id(),
                $this::parentPath(),
                $this::name(),
                $this::icon(),
                $this::path(),
                $this::params(),
                $this::target(),
                $this::hide(),
                $this::disabled(),
                $this::topPath()
            ]);
            
            return true;
        }
        
        return false;
    }
    
    
    /**
     * @inheritDoc
     */
    public function onSetValue(string $field, string $property, array $attrs, $value)
    {
        if ($field == $this::path()) {
            return ltrim($value, '/');
        }
        
        return $value;
    }
}