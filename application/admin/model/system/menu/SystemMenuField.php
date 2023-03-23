<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\menu;

use BusyPHP\helper\FilterHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\interfaces\FieldSetValueInterface;
use BusyPHP\interfaces\ModelValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\Validator;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use think\facade\Route;
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
 * @method static Entity child();
 * @method static Entity hash();
 * @method static Entity parentHash();
 * @method static Entity paramList();
 * @method static Entity url();
 * @method static Entity hides();
 * @method static Entity routePath();
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
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
class SystemMenuField extends Field implements ModelValidateInterface, FieldSetValueInterface
{
    /**
     * ID
     * @var int
     */
    #[Validator(name: Validator::REQUIRE)]
    #[Validator(name: Validator::GT, rule: 0)]
    public $id;
    
    /**
     * 菜单名称
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '请输入:attribute')]
    #[Filter(filter: 'trim')]
    public $name;
    
    /**
     * 菜单链接
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '请输入:attribute')]
    #[Validator(name: Validator::UNIQUE, rule: SystemMenu::class)]
    #[Filter(filter: 'trim')]
    public $path;
    
    /**
     * 上级菜单
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $parentPath;
    
    /**
     * 访问链接
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $topPath;
    
    /**
     * 附加参数
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $params;
    
    /**
     * 图标
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $icon;
    
    /**
     * 打开方式
     * @var string
     */
    #[Filter(filter: 'trim')]
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
     * 下级菜单
     * @var SystemMenuField[]
     */
    #[Ignore]
    public $child = [];
    
    /**
     * 隐藏的下级菜单，只有在前端菜单中用到
     * @var SystemMenuField[]
     */
    #[Ignore]
    public $hides = [];
    
    /**
     * 菜单地址哈希值
     * @var string
     */
    #[Ignore]
    public $hash;
    
    /**
     * 上级菜单地址哈西值
     * @var string
     */
    #[Ignore]
    public $parentHash;
    
    /**
     * 菜单连接
     * @var string
     */
    #[Ignore]
    public $url;
    
    /**
     * 顶级菜单访问连接
     * @var string
     */
    #[Ignore]
    public $topUrl;
    
    /**
     * 参数列表
     * @var string[]
     */
    #[Ignore]
    public $paramList;
    
    /**
     * 路由路径
     * @var string
     */
    #[Ignore]
    public $routePath;
    
    /**
     * 该菜单是否由注释生成
     * @var bool
     */
    #[Ignore]
    public $annotation;
    
    
    protected function onParseAfter()
    {
        $this->annotation = $this->id < 0;
        $this->routePath  = StringHelper::snake($this->path);
        $this->hash       = md5($this->routePath);
        $this->parentHash = $this->parentPath ? md5(StringHelper::snake($this->parentPath)) : '';
        $this->paramList  = FilterHelper::trimArray(explode(',', $this->params) ?: []);
        
        if (0 === strpos($this->path, '#')) {
            $this->url = '';
        } elseif (false !== strpos($this->path, '://')) {
            $this->url = $this->path;
        } else {
            $this->url = Route::buildUrl('/' . ltrim($this->routePath, '/'))->build();
        }
        
        $this->topUrl = '';
        if ($this->topPath) {
            $this->topUrl = Route::buildUrl('/' . ltrim($this->topPath, '/'))->build();
        }
    }
    
    
    /**
     * @inheritDoc
     * @param SystemMenuField $data
     */
    public function onModelValidate(Model $model, Validate $validate, string $scene, $data = null)
    {
        $validate
            ->append(
                $this::target(),
                ValidateRule::init()->in(array_keys(SystemMenu::class()::getTargets()), '请选择有效的:attribute')
            )
            ->append(
                $this::topPath(),
                ValidateRule::init()->closure(function($value) {
                    if (str_starts_with($value, '#') || str_contains($value, '://')) {
                        return false;
                    }
                    
                    
                    return true;
                }, ':attribute不能是锚连接或外部连接')->closure(function($value) use ($model) {
                    if (!$value) {
                        return true;
                    }
                    
                    if (!$model->where($this::path($value))->find()) {
                        return false;
                    }
                    
                    return true;
                }, ':attribute必须是已定义的菜单链接'));
        
        if ($scene == SystemMenu::SCENE_CREATE) {
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
        } elseif ($scene == SystemMenu::SCENE_UPDATE) {
            if ($data->system) {
                $this->retain($validate, [
                    $this::id(),
                    $this::name(),
                    $this::icon(),
                ]);
            } else {
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
            }
            
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