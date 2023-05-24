<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\App;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\model\system\menu\SystemMenuField;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use RuntimeException;
use Throwable;

/**
 * 用户组模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/30 下午5:56 下午 AdminGroup.php $
 * @method AdminGroupField getInfo(int $id, string $notFoundMessage = null)
 * @method AdminGroupField|null findInfo(int $id = null)
 * @method AdminGroupField[] selectList()
 * @method AdminGroupField[] indexList(string|Entity $key = '')
 * @method AdminGroupField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 */
class AdminGroup extends Model implements ContainerInterface
{
    protected string $dataNotFoundMessage = '用户组权限不存在';
    
    protected string $fieldClass          = AdminGroupField::class;
    
    /** @var string 操作场景-启用/禁用角色 */
    public const SCENE_STATUS = 'status';
    
    /**
     * 权限验证放行的控制器白名单
     * @var array
     */
    public static array $allowControllers = [
        'common.'
    ];
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 获取列表
     * @param bool $force
     * @return AdminGroupField[]
     */
    public function getList(bool $force = false) : array
    {
        return $this->rememberCacheByCallback('list', function() {
            return $this->order(AdminGroupField::sort(), 'asc')->order(AdminGroupField::id(), 'desc')->selectList();
        }, $force);
    }
    
    
    /**
     * 获取id为下标的列表
     * @param bool $force
     * @return array<string, AdminGroupField>
     */
    public function getIdMap(bool $force = false) : array
    {
        static $map;
        
        if ($force || !isset($map)) {
            $map = ArrayHelper::listByKey($this->getList(), AdminGroupField::id()->name());
        }
        
        return $map;
    }
    
    
    /**
     * 获取按照id为下标的上级id集合
     * @param bool $force
     * @return array<string,string[]>
     */
    public function getIdParentMap(bool $force = false) : array
    {
        static $map;
        
        if ($force || !isset($map)) {
            $map  = [];
            $list = $this->getIdMap();
            foreach ($list as $item) {
                $map[$item->id] = [];
                ArrayHelper::upwardRecursion($list, $item, AdminGroupField::id()->name(), AdminGroupField::parentId()
                    ->name(), $map[$item->id]);
            }
        }
        
        return $map;
    }
    
    
    /**
     * 添加管理角色
     * @param AdminGroupField $data
     * @return AdminGroupField
     * @throws Throwable
     */
    public function create(AdminGroupField $data) : AdminGroupField
    {
        $prepare = $this->trigger(new AdminGroupEventCreatePrepare($this, $data), true);
        
        return $this->transaction(function() use ($prepare, $data) {
            $this->validate($data, static::SCENE_CREATE);
            $this->trigger(new AdminGroupEventCreateBefore($this, $data, $prepare));
            $this->trigger(new AdminGroupEventCreateAfter($this, $data, $prepare, $info = $this->getInfo($this->insert())));
            
            return $info;
        });
    }
    
    
    /**
     * 修改管理角色
     * @param AdminGroupField $data
     * @param string          $scene 场景
     * @return AdminGroupField
     * @throws Throwable
     */
    public function modify(AdminGroupField $data, string $scene = self::SCENE_UPDATE) : AdminGroupField
    {
        $prepare = $this->trigger(new AdminGroupEventUpdatePrepare($this, $data, $scene), true);
        
        return $this->transaction(function() use ($data, $scene, $prepare) {
            $info = $this->lock(true)->getInfo($data->id);
            $this->validate($data, $scene, $info);
            $this->trigger(new AdminGroupEventUpdateBefore($this, $data, $scene, $prepare, $info));
            $this->update();
            $this->trigger(new AdminGroupEventUpdateAfter($this, $data, $scene, $prepare, $info, $finalInfo = $this->getInfo($info->id)));
            
            return $finalInfo;
        });
    }
    
    
    /**
     * 删除用户组
     * @param int $id
     * @return int
     * @throws Throwable
     */
    public function remove(int $id) : int
    {
        $prepare = $this->trigger(new AdminGroupEventDeletePrepare($this, $id), true);
        
        return $this->transaction(function() use ($id, $prepare) {
            $info = $this->lock(true)->getInfo($id);
            if ($info->system) {
                throw new RuntimeException('禁止删除系统权限');
            }
            
            $this->trigger(new AdminGroupEventDeleteBefore($this, $info->id, $info, $prepare));
            
            $deleteIds = array_column($this->getAllSubRoles($info->id, true), AdminGroupField::id()->name());
            $result    = $this->where(AdminGroupField::system(0))
                ->where(AdminGroupField::id('in', $deleteIds))
                ->delete();
            
            $this->trigger(new AdminGroupEventDeleteAfter($this, $info->id, $info, $prepare));
            
            return $result;
        });
    }
    
    
    /**
     * 获取某角色的所有子角色
     * @param int  $id 角色ID
     * @param bool $hasSelf 是否包含自己
     * @return AdminGroupField[]
     */
    public function getAllSubRoles(int $id, bool $hasSelf = false) : array
    {
        $list = ArrayHelper::listToTree(
            $this->getList(),
            AdminGroupField::id()->name(),
            AdminGroupField::parentId()->name(),
            AdminGroupField::child()->name(),
            $id
        );
        
        $list = ArrayHelper::treeToList($list, AdminGroupField::child()->name());
        if ($hasSelf && $self = ($this->getIdMap()[$id] ?? null)) {
            array_unshift($list, $self);
        }
        
        return $list;
    }
    
    
    /**
     * 获取树状结构角色组
     * @return AdminGroupField[]
     */
    public function getTree() : array
    {
        return ArrayHelper::listToTree(
            $this->getList(),
            AdminGroupField::id()->name(),
            AdminGroupField::parentId()->name(),
            AdminGroupField::child()->name(),
            0
        );
    }
    
    
    /**
     * 更新缓存
     */
    public function updateCache()
    {
        $this->getList(true);
    }
    
    
    /**
     * 设置启用/禁用
     * @param int  $id
     * @param bool $status
     * @return AdminGroupField
     * @throws Throwable
     */
    public function changeStatus($id, bool $status) : AdminGroupField
    {
        $update = AdminGroupField::init();
        $update->setId($id);
        $update->setStatus($status);
        
        $this->listen(AdminGroupEventUpdateBefore::class, function(AdminGroupEventUpdateBefore $before) {
            if ($before->info->system) {
                throw new RuntimeException('无法禁用系统权限');
            }
        });
        
        return $this->modify($update, static::SCENE_STATUS);
    }
    
    
    /**
     * @inheritDoc
     */
    protected function onChanged(string $method, $id, array $options)
    {
        $this->updateCache();
    }
    
    
    /**
     * @inheritDoc
     */
    protected function onUpdateAll()
    {
        $this->updateCache();
    }
    
    
    /**
     * 检测权限
     * @param AdminUserField|null $AdminUserField 用户数据
     * @param string              ...$paths 检测的路由路径
     * @return bool
     */
    public static function checkPermission(?AdminUserField $AdminUserField, ?string ...$paths) : bool
    {
        static $allPaths;
        
        if (!$AdminUserField) {
            return false;
        }
        
        // 拥有超级管理员权限，放行所有请求
        if ($AdminUserField->groupHasSystem) {
            return true;
        }
        
        $request    = App::getInstance()->request;
        $controller = $request->controller();
        if (!$paths) {
            $paths[] = $request->getRoutePath(true);
        }
        
        if (!isset($allPaths)) {
            $allPaths = array_column(SystemMenu::instance()->getList(), SystemMenuField::routePath()->name());
        }
        
        foreach ($paths as $path) {
            // 需要获取控制器补全
            $values = explode('/', $path) ?: [];
            if (count($values) == 1) {
                $path = sprintf('%s/%s', $controller, $values[0]);
            }
            
            // 放行白名单
            $currentPath = StringHelper::snake($path);
            foreach (static::$allowControllers as $item) {
                if (str_starts_with($currentPath, $item)) {
                    return true;
                }
            }
            
            // 不在全部规则内，则可以访问
            if (!in_array($currentPath, $allPaths)) {
                return true;
            }
            
            // 是否在规则内
            return in_array($currentPath, $AdminUserField->groupRulePaths);
        }
        
        return false;
    }
}