<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\menu;

use BusyPHP\App;
use BusyPHP\app\admin\annotation\IgnoreLogin;
use BusyPHP\app\admin\annotation\MenuGroup;
use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\controller\AdminController;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\app\admin\model\system\file\SystemFileField;
use BusyPHP\helper\AppHelper;
use BusyPHP\helper\CacheHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\model;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\model\Entity;
use BusyPHP\Service;
use FilesystemIterator;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use SplFileInfo;
use think\Container;
use think\db\exception\DbException;
use think\facade\Route;
use Throwable;

/**
 * 后台菜单模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午2:45 下午 SystemMenu.php $
 * @method SystemMenuField getInfo(int $id, string $notFoundMessage = null)
 * @method SystemMenuField|null findInfo(int $id = null)
 * @method SystemMenuField[] selectList()
 * @method SystemMenuField[] indexList(string|Entity $key = '')
 * @method SystemMenuField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 */
class SystemMenu extends Model implements ContainerInterface
{
    //+--------------------------------------
    //| 外部链接打开方式
    //+--------------------------------------
    /** @var string 当前窗口 */
    public const TARGET_SELF = '';
    
    /** @var string 新建窗口 */
    public const TARGET_BLANK = '_blank';
    
    /** @var string Iframe窗口 */
    public const TARGET_IFRAME = 'iframe';
    
    /** @var string 开发模式菜单路径 */
    public const DEVELOPER_PATH = '#developer';
    
    protected string $dataNotFoundMessage = '菜单不存在';
    
    protected string $fieldClass          = SystemMenuField::class;
    
    /** @var array 注册的控制器集合 */
    protected static array $annotationList = [];
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 获取打开方式
     * @param string|null $var
     * @return array|string
     */
    public static function getTargets(string $var = null)
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstAttrs(self::class, 'TARGET_', ClassHelper::ATTR_NAME), $var);
    }
    
    
    /**
     * 添加菜单
     * @param SystemMenuField $data 添加的数据
     * @return int
     * @throws Throwable
     */
    public function create(SystemMenuField $data) : int
    {
        return (int) $this->validate($data, static::SCENE_CREATE)->insert();
    }
    
    
    /**
     * 修改菜单
     * @param SystemMenuField $data
     * @param string          $scene
     * @throws Throwable
     */
    public function modify(SystemMenuField $data, string $scene = self::SCENE_UPDATE)
    {
        $this->transaction(function() use ($data, $scene) {
            $info = $this->lock(true)->getInfo($data->id);
            
            $this->validate($data, $scene, $info);
            
            // 更新子菜单关系
            if (!$info->system) {
                $this->where(SystemMenuField::parentPath($info->path))
                    ->setField(SystemMenuField::parentPath(), $data->path);
            }
            
            $this->update($data);
        });
    }
    
    
    /**
     * 删除菜单
     * @param int  $id 菜单ID
     * @param bool $disabledTrans 是否禁用事物
     * @return int
     * @throws Throwable
     */
    public function remove(int $id, bool $disabledTrans = false) : int
    {
        return $this->transaction(function() use ($id) {
            $info = $this->lock(true)->getInfo($id);
            
            if ($info->system) {
                throw new RuntimeException('系统菜单禁止删除');
            }
            
            // 删除子菜单
            $list       = ArrayHelper::listToTree(
                $this->selectList(),
                SystemMenuField::path()->name(),
                SystemMenuField::parentPath()->name(),
                SystemMenuField::child()->name(),
                $info->path
            );
            $list       = ArrayHelper::treeToList($list, SystemMenuField::child()->name());
            $childIds   = array_column($list, SystemFileField::id()->name());
            $childIds[] = $info->id;
            
            return $this->where(SystemMenuField::system(0))->where(SystemMenuField::id('in', $childIds))->delete();
        }, $disabledTrans);
    }
    
    
    /**
     * 通过路径删除菜单
     * @param string $path 菜单路径
     * @param bool   $disabledTrans 是否禁用事物
     * @return int
     * @throws Throwable
     */
    public function deleteByPath(string $path, bool $disabledTrans = false) : int
    {
        $info = $this->where(SystemFileField::path($path))->findInfo();
        if (!$info) {
            return 0;
        }
        
        return $this->remove($info->id, $disabledTrans);
    }
    
    
    /**
     * 获取某菜单下的子菜单
     * @param string $path 菜单路径
     * @param bool   $self 是否含自己
     * @param bool   $hide 是否只查询隐藏的菜单
     * @return SystemMenuField[]
     * @throws Throwable
     */
    public function getChildList(string $path, bool $self = false, bool $hide = false) : array
    {
        $list = array_filter($this->getList(), function(SystemMenuField $item) use ($path, $self, $hide) {
            if ($self && $item->path == $path) {
                return true;
            }
            
            if ($item->parentPath != $path || $item->hide !== $hide || $item->disabled) {
                return false;
            }
            
            return true;
        });
        
        return array_values($list);
    }
    
    
    /**
     * 更新缓存
     * @throws Throwable
     */
    public function updateCache()
    {
        static::extractAnnotation(true);
        
        $this->clearCache();
        $this->getList(true);
    }
    
    
    /**
     * 获取所有菜单
     * @param bool $force
     * @return SystemMenuField[]
     */
    public function getList(bool $force = false) : array
    {
        static $list;
        
        if ($force || !isset($list)) {
            $list = $this->rememberCacheByCallback('list', function() {
                return $this->selectList();
            }, $force);
            
            // 去除所有数据库中的hash，然后遍历注解中的菜单，如果存在则过滤掉
            $hashList = array_column($list, SystemMenuField::hash()->name());
            foreach (static::getAnnotationMenus() ?? [] as $item) {
                if (in_array($item->hash, $hashList)) {
                    continue;
                }
                $list[] = $item;
            }
            
            // 排序
            $sorts = array_column($list, SystemMenuField::sort()->name());
            $ids   = array_column($list, SystemFileField::id()->name());
            array_multisort($sorts, SORT_ASC, $ids, SORT_ASC, $list);
        }
        
        return $list;
    }
    
    
    /**
     * 获取按照hash为下标的列表
     * @param bool $force
     * @return array<string, SystemMenuField>
     */
    public function getHashMap(bool $force = false) : array
    {
        static $map;
        
        if ($force || !isset($map)) {
            $map = ArrayHelper::listByKey($this->getList(), SystemMenuField::hash()->name());
        }
        
        return $map;
    }
    
    
    /**
     * 获取菜单树
     * @param bool $force
     * @return SystemMenuField[]
     */
    public function getTree(bool $force = false) : array
    {
        static $tree;
        
        if ($force || !isset($tree)) {
            $tree = ArrayHelper::listToTree(
                $this->getList(),
                SystemMenuField::path()->name(),
                SystemMenuField::parentPath()->name(),
                SystemMenuField::child()->name(),
                ""
            );
        }
        
        return $tree;
    }
    
    
    /**
     * 获取按照hash为下标的上级hash集合
     * @return array<string,string[]>
     */
    public function getHashParentMap(bool $force = false) : array
    {
        static $map;
        
        if ($force || !isset($map)) {
            $map  = [];
            $list = $this->getHashMap();
            foreach ($list as $item) {
                $map[$item->hash] = [];
                ArrayHelper::upwardRecursion($list, $item, SystemMenuField::hash()
                    ->name(), SystemMenuField::parentHash()
                    ->name(), $map[$item->hash]);
            }
        }
        
        return $map;
    }
    
    
    /**
     * 获取后台菜单
     * @param AdminUserField $AdminUserField 用户信息
     * @return SystemMenuField[]
     */
    public function getNav(AdminUserField $AdminUserField) : array
    {
        $hashParentMap = $this->getHashParentMap();
        $hashMap       = $this->getHashMap();
        
        return ArrayHelper::listToTree(
            $hashMap,
            SystemMenuField::path()->name(),
            SystemMenuField::parentPath()->name(),
            SystemMenuField::child()->name(),
            "",
            function(SystemMenuField $info) use ($AdminUserField, $hashParentMap, $hashMap) {
                if ($info->hide && isset($hashParentMap[$info->hash])) {
                    $parentHash = array_shift($hashParentMap[$info->hash]);
                    if (isset($hashMap[$parentHash])) {
                        $hashMap[$parentHash]->hides[] = $info;
                    }
                }
                
                // 禁用和隐藏的菜单不输出
                if ($info->disabled || $info->hide) {
                    return false;
                }
                
                // 系统管理员
                if ($AdminUserField->groupHasSystem) {
                    // 系统菜单在非开发模式下不输出
                    if (!App::getInstance()->isDebug() && $info->path == static::DEVELOPER_PATH) {
                        return false;
                    }
                } else {
                    // 不在规则内
                    // 不是系统菜单
                    if (!in_array($info->hash, $AdminUserField->groupRuleIds) || $info->system) {
                        return false;
                    }
                }
                
                return true;
            });
    }
    
    
    /**
     * 设置是否禁用
     * @param int  $id
     * @param bool $status
     * @throws DbException
     */
    public function setDisabled(int $id, bool $status)
    {
        $this->where(SystemFileField::id($id))->setField(SystemMenuField::disabled(), $status ? 1 : 0);
    }
    
    
    /**
     * 设置是否隐藏
     * @param int  $id
     * @param bool $status
     * @throws DbException
     */
    public function setHide(int $id, bool $status)
    {
        $this->where(SystemFileField::id($id))->setField(SystemMenuField::hide(), $status ? 1 : 0);
    }
    
    
    /**
     * 注册要扫描注解目录名或类名
     * @param string $controller
     */
    public static function registerAnnotation(string $controller)
    {
        static::$annotationList[] = $controller;
    }
    
    
    /**
     * 提取菜单
     * @param bool $force 是否强制提取
     * @return array{list: array<SystemMenuField>, route: array<string, array>, exclude_login: array<string>}
     */
    public static function extractAnnotation(bool $force = false) : array
    {
        static $data;
        
        if ($force || !isset($data)) {
            // 从缓存中提取
            $key = 'annotation_data';
            if (!$force && !App::getInstance()->isDebug()) {
                $data = CacheHelper::get(self::class, $key);
            }
            
            if (!$data) {
                $classList = [];
                foreach (static::$annotationList as $annotation) {
                    if (str_contains($annotation, '/')) {
                        if (is_dir($annotation)) {
                            $list = [];
                            static::scanAnnotation($annotation, $list);
                            $classList = array_merge($classList, $list);
                        }
                    } elseif (class_exists($annotation) && is_subclass_of($annotation, AdminController::class)) {
                        $classList[] = ClassHelper::getAbsoluteClassname($annotation);
                    }
                }
                
                $list         = [];
                $routes       = [];
                $excludeLogin = [];
                $id           = 0;
                foreach (array_unique($classList) as $classname) {
                    try {
                        $methods = [];
                        $reflect = new ReflectionClass($classname);
                        if ($reflect->getAttributes(IgnoreLogin::class)) {
                            $excludeLogin[] = $classname;
                        }
                        
                        foreach ($reflect->getMethods() as $method) {
                            $methodName = $method->getName();
                            if (!$method->isPublic() || $method->class != $reflect->name || str_starts_with($methodName, '__')) {
                                continue;
                            }
                            
                            // 不校验登录
                            if ($method->getAttributes(IgnoreLogin::class)) {
                                $excludeLogin[] = $classname . '::' . $methodName;
                            }
                            
                            // 菜单节点
                            if ($nodeNodes = $method->getAttributes(MenuNode::class)) {
                                /** @var MenuNode $nodeNode */
                                $nodeNode = $nodeNodes[0]->newInstance();
                                
                                // 菜单名称
                                $res      = ClassHelper::extractDocAttrs($reflect, $methodName, '', $method->getDocComment());
                                $nodeName = trim($nodeNode->getName());
                                $nodeName = $nodeName === '' ? ($res[ClassHelper::ATTR_NAME] ?: $methodName) : $nodeName;
                                
                                $methods[$methodName] = [
                                    'name'   => $nodeName,
                                    'action' => $methodName,
                                    'node'   => $nodeNode->isMenu(),
                                    'icon'   => trim($nodeNode->getIcon()),
                                    'params' => trim($nodeNode->getParams()),
                                    'parent' => trim($nodeNode->getParent()),
                                    'sort'   => $nodeNode->getSort()
                                ];
                            }
                        }
                        
                        if ($methods) {
                            $res          = ClassHelper::extractDocAttrs($reflect, $reflect->getShortName(), '', $reflect->getDocComment());
                            $controller   = '';
                            $routeToClass = false;
                            $parent       = '';
                            if ($menuRoutes = $reflect->getAttributes(MenuRoute::class)) {
                                /** @var MenuRoute $menuRoute */
                                $menuRoute    = $menuRoutes[0]->newInstance();
                                $controller   = $menuRoute->getPath();
                                $routeToClass = $menuRoute->isClass();
                            }
                            
                            // 路由转发
                            $controller       = AppHelper::trimController($controller === '' ? $reflect->getShortName() : $controller);
                            $sourceController = AppHelper::trimController($reflect->getShortName());
                            if ($controller != $sourceController) {
                                $routes[$controller] = [
                                    'classname'  => $reflect->name,
                                    'controller' => $sourceController,
                                    'class'      => $routeToClass
                                ];
                            }
                            
                            // 分组节点
                            if ($menuGroups = $reflect->getAttributes(MenuGroup::class)) {
                                /** @var MenuGroup $menuGroup */
                                $menuGroup = $menuGroups[0]->newInstance();
                                $sort      = $menuGroup->getSort();
                                $icon      = trim($menuGroup->getIcon());
                                $path      = trim($menuGroup->getPath());
                                
                                // 分组名称
                                $menuName = trim($menuGroup->getName());
                                if ($menuName === '') {
                                    $menuName = $res[ClassHelper::ATTR_NAME] ?: ucfirst(StringHelper::snake($reflect->getShortName(), ' '));
                                }
                                
                                $id--;
                                $item = SystemMenuField::init();
                                $item->setId($id);
                                $item->setName($menuName);
                                $item->setPath('#' . ltrim($path === '' ? $controller : $path, '#'));
                                $item->setParentPath(trim($menuGroup->getParent()));
                                $item->setIcon($icon ?: 'fa fa-folder');
                                $item->setSort($sort === false ? abs($id) : $sort);
                                $item->setParams('');
                                $item->setHide(false);
                                $item->setDisabled(false);
                                $item->setSystem(false);
                                $item->setTopPath('');
                                $item->setTarget('');
                                $item   = SystemMenuField::parse($item);
                                $parent = $item->path;
                                if ($item->parentPath) {
                                    $list[] = $item;
                                }
                            }
                            
                            // 叶子节点
                            foreach ($methods as $vo) {
                                $id--;
                                
                                // 上级节点名称
                                $parentPath = $vo['parent'];
                                if ($parentPath && str_starts_with($parentPath, '/') && strlen($parentPath) > 1) {
                                    $parentPath = $controller . $parentPath;
                                }
                                if (!$parentPath) {
                                    $parentPath = $parent;
                                }
                                
                                $item = SystemMenuField::init();
                                $item->setId($id);
                                $item->setName($vo['name']);
                                $item->setPath($controller . '/' . $vo['action']);
                                $item->setHide(!$vo['node']);
                                $item->setParentPath($parentPath);
                                $item->setParams($vo['params']);
                                $item->setIcon($vo['icon'] ?: 'fa fa-file');
                                $item->setSort($vo['sort'] === false ? abs($id) : $vo['sort']);
                                $item->setDisabled(false);
                                $item->setSystem(false);
                                $item->setTopPath('');
                                $item->setTarget('');
                                $item = SystemMenuField::parse($item);
                                if ($item->parentPath) {
                                    $list[] = $item;
                                }
                            }
                        }
                    } catch (ReflectionException $e) {
                    }
                }
                
                $data = [
                    'list'          => $list,
                    'route'         => $routes,
                    'exclude_login' => $excludeLogin
                ];
                CacheHelper::set(self::class, $key, $data, 0);
            }
        }
        
        return $data;
    }
    
    
    /**
     * 加载注解路由
     */
    public static function loadAnnotationRoutes()
    {
        $pattern   = '<' . Service::ROUTE_VAR_ACTION . '>';
        $container = Container::getInstance();
        foreach (static::extractAnnotation()['route'] as $name => $item) {
            // 转发到类
            if ($item['class']) {
                $class = $container->getAlias($item['classname']);
                Route::rule($name . '/' . $pattern, $class . '@' . $pattern)->append([
                    Service::ROUTE_VAR_TYPE    => Service::ROUTE_TYPE_PLUGIN,
                    Service::ROUTE_VAR_CONTROL => $name
                ]);
            }
            
            //
            // 转发到URL
            else {
                Route::rule($name . '/' . $pattern, $item['controller'] . '/' . $pattern)->append([
                    Service::ROUTE_VAR_TYPE    => Service::ROUTE_TYPE_PLUGIN,
                    Service::ROUTE_VAR_CONTROL => $item['controller']
                ]);
            }
        }
    }
    
    
    /**
     * 获取注解菜单集合
     * @return SystemMenuField[]
     */
    public static function getAnnotationMenus() : array
    {
        return static::extractAnnotation()['list'];
    }
    
    
    /**
     * 是否排除验证登录
     * @param string $controllerClassname 控制器类名
     * @param string $actionName 方法名
     * @return bool
     */
    public static function isExcludeLogin(string $controllerClassname, string $actionName) : bool
    {
        return in_array($controllerClassname . '::' . $actionName, static::extractAnnotation()['exclude_login'], true) || in_array($controllerClassname, static::extractAnnotation()['exclude_login'], true);
    }
    
    
    /**
     * 递归扫描菜单目录并获取控制器类
     * @param string $dir 目录
     * @param array  $list 获取的类数据
     */
    protected static function scanAnnotation(string $dir, array &$list = [])
    {
        /** @var SplFileInfo $item */
        foreach (new FilesystemIterator($dir) as $item) {
            if ($item->isDir()) {
                static::scanAnnotation($item->getRealPath(), $list);
            } else {
                $content = file_get_contents($item->getRealPath());
                if (!preg_match('/<\?php.*namespace\s(.*?);/is', $content, $match)) {
                    continue;
                }
                $classname = $match[1] . '\\' . $item->getBasename('.php');
                if (class_exists($classname) && is_subclass_of($classname, AdminController::class)) {
                    $list[] = ClassHelper::getAbsoluteClassname($classname);
                }
            }
        }
    }
}