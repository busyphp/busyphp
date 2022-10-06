<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\App;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\model;
use BusyPHP\model\Entity;
use RuntimeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use Throwable;

/**
 * 用户组模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/30 下午5:56 下午 AdminGroup.php $
 * @method AdminGroupInfo getInfo(int $id, string $notFoundMessage = null)
 * @method AdminGroupInfo|null findInfo(int $id = null, string $notFoundMessage = null)
 * @method AdminGroupInfo[] selectList()
 * @method AdminGroupInfo[] buildListWithField(array $values, string|Entity $key = null, string|Entity $field = null)
 * @method static AdminGroup getClass()
 */
class AdminGroup extends Model
{
    protected $dataNotFoundMessage = '用户组权限不存在';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = AdminGroupInfo::class;
    
    /** @var string 操作场景-启用/禁用角色 */
    public const SCENE_STATUS = 'status';
    
    /**
     * 权限验证放行的控制器白名单
     * @var string
     */
    public static $allowControllers = [
        'common.'
    ];
    
    
    /**
     * @inheritDoc
     */
    final protected static function defineClass() : string
    {
        return self::class;
    }
    
    
    /**
     * 获取列表
     * @param bool $must
     * @return AdminGroupInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getList($must = false) : array
    {
        $list = $this->getCache('list');
        if (!$list || $must) {
            $list = $this->order(AdminGroupField::sort(), 'asc')->order(AdminGroupField::id(), 'desc')->selectList();
            $this->setCache('list', $list);
        }
        
        return $list;
    }
    
    
    /**
     * 获取id为下标的列表
     * @return AdminGroupInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getIdList() : array
    {
        return ArrayHelper::listByKey($this->getList(), AdminGroupField::id()->name());
    }
    
    
    /**
     * 添加管理角色
     * @param AdminGroupField $data
     * @return AdminGroupInfo
     * @throws Throwable
     */
    public function createInfo(AdminGroupField $data) : AdminGroupInfo
    {
        $prepare = $this->trigger(new AdminGroupEventCreatePrepare($this, $data), true);
        
        
        return $this->transaction(function() use ($prepare, $data) {
            $this->validate($data, self::SCENE_CREATE);
            $this->trigger(new AdminGroupEventCreateBefore($this, $data, $prepare));
            $this->trigger(new AdminGroupEventCreateAfter($this, $data, $prepare, $info = $this->getInfo($this->addData())));
            
            return $info;
        });
    }
    
    
    /**
     * 修改管理角色
     * @param AdminGroupField $data
     * @param string          $scene 场景
     * @return AdminGroupInfo
     * @throws Throwable
     */
    public function updateInfo(AdminGroupField $data, string $scene = self::SCENE_UPDATE) : AdminGroupInfo
    {
        $prepare = $this->trigger(new AdminGroupEventUpdatePrepare($this, $data, $scene), true);
        
        return $this->transaction(function() use ($data, $scene, $prepare) {
            $info = $this->lock(true)->getInfo($data->id);
            $this->validate($data, $scene);
            $this->trigger(new AdminGroupEventUpdateBefore($this, $data, $scene, $prepare, $info));
            $this->saveData();
            $this->trigger(new AdminGroupEventUpdateAfter($this, $data, $scene, $prepare, $info, $finalInfo = $this->getInfo($info->id)));
            
            return $finalInfo;
        });
    }
    
    
    /**
     * 校验数据
     * @param AdminGroupField $data
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ParamInvalidException
     * @throws VerifyException
     */
    protected function checkData(AdminGroupField $data)
    {
        if (!$data->rule || !$data->name || !$data->defaultMenuId) {
            throw new ParamInvalidException('rule,name,default_menu_id');
        }
        
        $idList  = SystemMenu::init()->getIdList();
        $newRule = [];
        foreach ($data->rule as $id) {
            if (!isset($idList[$id])) {
                continue;
            }
            $newRule[] = $id;
        }
        $data->setRule($newRule);
    }
    
    
    /**
     * 删除用户组
     * @param int $data
     * @return int
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    public function deleteInfo($data) : int
    {
        $id      = (int) $data;
        $prepare = $this->trigger(new AdminGroupEventDeletePrepare($this, $id), true);
        
        return $this->transaction(function() use ($id, $prepare) {
            $info = $this->lock(true)->getInfo($id);
            if ($info->system) {
                throw new RuntimeException('系统管理权限组禁止删除');
            }
            
            $this->trigger(new AdminGroupEventDeleteBefore($this, $info->id, $info, $prepare));
            
            // 删除子角色
            $childIds = array_keys(ArrayHelper::listByKey(
                $this->getChildList($info->id),
                AdminGroupField::id()->name()
            ));
            if ($childIds) {
                $this->whereEntity(AdminGroupField::id('in', $childIds))->delete();
            }
            
            $result = parent::deleteInfo($info->id);
            $this->trigger(new AdminGroupEventDeleteAfter($this, $info->id, $info, $prepare));
            
            return $result;
        });
    }
    
    
    /**
     * 设置排序
     * @param array $data
     * @throws DbException
     */
    public function setSort(array $data)
    {
        $saveAll = [];
        foreach ($data as $id => $value) {
            $item       = AdminGroupField::init();
            $item->id   = $id;
            $item->sort = $value;
            $saveAll[]  = $item;
        }
        
        if ($saveAll) {
            $this->saveAll($saveAll);
        }
    }
    
    
    /**
     * 获取某角色的所有子角色
     * @param int $id 角色ID
     * @return AdminGroupInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getChildList($id) : array
    {
        $list = ArrayHelper::listToTree(
            $this->selectList(),
            AdminGroupField::id()->name(),
            AdminGroupField::parentId()->name(),
            AdminGroupInfo::child()->name(),
            $id
        );
        
        return ArrayHelper::treeToList($list, AdminGroupInfo::child()->name());
    }
    
    
    /**
     * 获取树状结构角色组
     * @return AdminGroupInfo[]
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function getTreeList() : array
    {
        return ArrayHelper::listToTree(
            $this->getList(),
            AdminGroupField::id()->name(),
            AdminGroupField::parentId()->name(),
            AdminGroupInfo::child()->name(),
            0
        );
    }
    
    
    /**
     * 获取菜单选项
     * @param string $selectedId
     * @param string $disabledId
     * @param array  $list
     * @param string $space
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getTreeOptions($selectedId = '', $disabledId = '', $list = [], $space = '') : string
    {
        $push = '├';
        if (!$list) {
            $list = $this->getTreeList();
            $push = '';
        }
        
        $options = '';
        foreach ($list as $item) {
            $disabled = '';
            if ($disabledId == $item->id) {
                $disabled = ' disabled';
            }
            
            $selected = '';
            if ($item->id == $selectedId) {
                $selected = ' selected="selected"';
            }
            $options .= '<option value="' . $item->id . '"' . $selected . $disabled . '>' . $space . $push . $item->name . '</option>';
            if ($item->child) {
                $options .= $this->getTreeOptions($selectedId, $disabledId, $item->child, '┊　' . $space);
            }
        }
        
        return $options;
    }
    
    
    /**
     * 更新缓存
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function updateCache()
    {
        $this->getList(true);
    }
    
    
    /**
     * 设置启用/禁用
     * @param int  $id
     * @param bool $status
     * @return AdminGroupInfo
     * @throws Throwable
     */
    public function changeStatus($id, bool $status) : AdminGroupInfo
    {
        $update = AdminGroupField::init();
        $update->setId($id);
        $update->setStatus($status);
        
        $this->listen(AdminGroupEventUpdateBefore::class, function(AdminGroupEventUpdateBefore $before) {
            if ($before->info->system) {
                throw new RuntimeException('无法禁用系统权限');
            }
        });
        
        return $this->updateInfo($update, self::SCENE_STATUS);
    }
    
    
    /**
     * @param string $method
     * @param mixed  $id
     * @param array  $options
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function onChanged(string $method, $id, array $options)
    {
        $this->updateCache();
    }
    
    
    /**
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function onSaveAll()
    {
        $this->updateCache();
    }
    
    
    /**
     * 检测权限
     * @param AdminUserInfo|null $adminUserInfo 用户数据
     * @param string             ...$paths 检测的路由路径
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     */
    public static function checkPermission(?AdminUserInfo $adminUserInfo, ?string ...$paths) : bool
    {
        static $allPaths;
        
        if (!$adminUserInfo) {
            return false;
        }
        
        // 拥有超级管理员权限，放行所有请求
        if ($adminUserInfo->groupHasSystem) {
            return true;
        }
        
        $request    = App::getInstance()->request;
        $controller = $request->controller();
        if (!$paths) {
            $paths[] = $request->getRoutePath(true);
        }
        
        if (!isset($allPaths)) {
            $allPaths = SystemMenu::init()->getPathList();
            $allPaths = array_map([StringHelper::class, 'snake'], array_keys($allPaths));
        }
        
        foreach ($paths as $path) {
            // 需要获取控制器补全
            $values = explode('/', $path) ?: [];
            if (count($values) == 1) {
                $path = sprintf('%s/%s', $controller, $values[0]);
            }
            
            // 放行白名单
            $currentPath = StringHelper::snake($path);
            foreach (self::$allowControllers as $item) {
                if (0 === strpos($currentPath, $item)) {
                    return true;
                }
            }
            
            // 不在全部规则内，则可以访问
            if (!in_array($currentPath, $allPaths)) {
                return true;
            }
            
            // 是否在规则内
            return in_array($currentPath, $adminUserInfo->groupRulePaths);
        }
        
        return false;
    }
}