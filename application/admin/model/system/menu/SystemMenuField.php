<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\menu;

use BusyPHP\helper\FilterHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\interfaces\ModelValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\Validator;
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
 * @method static Entity sort(mixed $op = null, mixed $condition = null) 自定义排序
 * @method static Entity child();
 * @method static Entity hash();
 * @method static Entity parentHash();
 * @method static Entity paramList();
 * @method static Entity url();
 * @method static Entity hides();
 * @method static Entity routePath();
 * @method static Entity source();
 * @method $this setId(mixed $id, bool|ValidateRule[] $validate = false) 设置ID
 * @method $this setName(mixed $name, bool|ValidateRule[] $validate = false) 设置名称
 * @method $this setPath(mixed $path, bool|ValidateRule[] $validate = false) 设置路由地址
 * @method $this setParentPath(mixed $parentPath, bool|ValidateRule[] $validate = false) 设置上级路由
 * @method $this setTopPath(mixed $topPath, bool|ValidateRule[] $validate = false) 设置顶级菜单默认访问路由地址
 * @method $this setParams(mixed $params, bool|ValidateRule[] $validate = false) 设置附加参数
 * @method $this setIcon(mixed $icon, bool|ValidateRule[] $validate = false) 设置图标
 * @method $this setTarget(mixed $target, bool|ValidateRule[] $validate = false) 设置打开方式
 * @method $this setHide(mixed $hide, bool|ValidateRule[] $validate = false) 设置是否隐藏
 * @method $this setDisabled(mixed $disabled, bool|ValidateRule[] $validate = false) 设置是否禁用
 * @method $this setSystem(mixed $system, bool|ValidateRule[] $validate = false) 设置是否系统菜单
 * @method $this setSort(mixed $sort, bool|ValidateRule[] $validate = false) 设置自定义排序
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
class SystemMenuField extends Field implements ModelValidateInterface
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
    #[Filter('ltrim', '/')]
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
    
    /**
     * 该数据是否复制的注解菜单
     * @var bool
     */
    #[Ignore]
    public $system;
    
    /**
     * 该注解菜单是否可以被禁用
     * @var bool
     */
    #[Ignore]
    public $canDisable;
    
    /**
     * 是否允许操作禁用字段
     * @var bool
     */
    #[Ignore]
    public $operateDisable;
    
    /**
     * 菜单源
     * @var string
     */
    #[Ignore]
    public $source;
    
    
    protected function onParseAfter()
    {
        $this->annotation = $this->id < 0;
        $this->system     = $this->annotation;
        $this->routePath  = StringHelper::snake($this->path);
        $this->hash       = md5($this->routePath);
        $this->parentHash = $this->parentPath ? md5(StringHelper::snake($this->parentPath)) : '';
        $this->paramList  = FilterHelper::trimArray(explode(',', $this->params) ?: []);
        
        if (str_starts_with($this->path, '#')) {
            $this->url = '';
        } elseif (str_contains($this->path, '://')) {
            $this->url = $this->path;
        } else {
            $this->url = '/' . $this->routePath . '.html';
        }
        
        $this->topUrl = '';
        if ($this->topPath) {
            $this->topUrl = '/' . ltrim($this->topPath, '/') . '.html';
        }
        
        // 非注解菜单判断是否复制的注解菜单
        if (!$this->annotation) {
            $menu = SystemMenu::class()::getAnnotationMenusHashMap()[$this->hash] ?? null;
            if ($menu) {
                $this->system     = true;
                $this->canDisable = $menu->canDisable;
                $this->source     = $menu->source;
            } else {
                $this->source     = $this->routePath;
                $this->canDisable = true;
            }
            $this->operateDisable = $this->canDisable;
        } else {
            $this->operateDisable = false;
        }
    }
    
    
    /**
     * @inheritDoc
     * @param SystemMenu      $model
     * @param SystemMenuField $data
     */
    public function onModelValidate(Model $model, Validate $validate, string $scene, $data = null)
    {
        $validate
            ->append(
                $this::target(),
                ValidateRule::init()->in(array_keys($model::getTargetMap()), '请选择有效的:attribute')
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
        
        if ($scene == $model::SCENE_CREATE) {
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
                $this::topPath(),
                $this::sort()
            ]);
            
            return true;
        } elseif ($scene == $model::SCENE_UPDATE) {
            // annotation菜单
            // 只保留 name icon parent_path
            if ($data->annotation) {
                $this->retain($validate, [
                    $this::name(),
                    $this::icon(),
                    $this::parentPath(),
                ]);
            }
            
            // 属于复制的annotation菜单
            // 保留 id name icon parent_path
            elseif ($data->system) {
                $propertyList = [
                    $this::id(),
                    $this::name(),
                    $this::icon(),
                    $this::parentPath(),
                ];
                
                // 如果允许操作禁用字段
                if ($data->operateDisable) {
                    $propertyList[] = $this::disabled();
                }
                
                
                $this->retain($validate, $propertyList);
            }
            
            //
            // 正常修改
            else {
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
}