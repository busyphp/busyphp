<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\App;
use BusyPHP\app\admin\event\model\group\CreateAdminGroupAfterEvent;
use BusyPHP\app\admin\event\model\group\CreateAdminGroupBeforeEvent;
use BusyPHP\app\admin\event\model\group\CreateAdminGroupTakeParamsEvent;
use BusyPHP\app\admin\event\model\group\DeleteAdminGroupAfterEvent;
use BusyPHP\app\admin\event\model\group\DeleteAdminGroupBeforeEvent;
use BusyPHP\app\admin\event\model\group\DeleteAdminGroupTakeParamsEvent;
use BusyPHP\app\admin\event\model\group\UpdateAdminGroupAfterEvent;
use BusyPHP\app\admin\event\model\group\UpdateAdminGroupBeforeEvent;
use BusyPHP\app\admin\event\model\group\UpdateAdminGroupTakeParamsEvent;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\helper\StringHelper;
use BusyPHP\model;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\facade\Event;
use Throwable;

/**
 * 用户组模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/30 下午5:56 下午 AdminGroup.php $
 * @method AdminGroupInfo findInfo($data = null, $notFoundMessage = null)
 * @method AdminGroupInfo getInfo($data, $notFoundMessage = null)
 * @method AdminGroupInfo[] selectList()
 * @method AdminGroupInfo[] buildListWithField(array $values, $key = null, $field = null) : array()
 */
class AdminGroup extends Model
{
    protected $dataNotFoundMessage = '用户组权限不存在';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = AdminGroupInfo::class;
    
    /**
     * 权限验证放行的控制器白名单
     * @var string
     */
    public static $allowControllers = [
        'common.'
    ];
    
    
    /**
     * 获取列表
     * @param bool $must
     * @return AdminGroupInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getList($must = false)
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
     * @param AdminGroupField $insert
     * @param bool            $triggerEvent 是否触发事件，否则触发回调
     * @return int
     * @throws Throwable
     */
    public function createGroup(AdminGroupField $insert, bool $triggerEvent = true)
    {
        // 触发参数处理事件
        $event      = new CreateAdminGroupTakeParamsEvent();
        $takeParams = null;
        if ($triggerEvent) {
            $event->data = $insert;
            $takeParams  = Event::trigger($event, [], true);
        }
        
        $this->startTrans();
        try {
            $this->checkData($insert);
            
            // 触发创建前事件
            $event             = new CreateAdminGroupBeforeEvent();
            $event->data       = $insert;
            $event->takeParams = $takeParams;
            $triggerEvent ? Event::trigger($event) : $this->triggerCallback(self::CALLBACK_BEFORE, $event);
            
            $id = $this->addData($insert);
            
            // 触发创建后事件
            $event             = new CreateAdminGroupAfterEvent();
            $event->data       = $insert;
            $event->takeParams = $takeParams;
            $event->info       = $this->getInfo($id);
            $triggerEvent ? Event::trigger($event) : $this->triggerCallback(self::CALLBACK_AFTER, $event);
            
            $this->commit();
            
            return $id;
        } catch (Throwable $e) {
            $this->rollback();
            
            throw $e;
        }
    }
    
    
    /**
     * 修改管理角色
     * @param AdminGroupField $update $id
     * @param bool            $triggerEvent 是否触发事件，否则触发回调
     * @throws Throwable
     */
    public function updateGroup(AdminGroupField $update, bool $triggerEvent = true)
    {
        if ($update->id < 1) {
            throw new ParamInvalidException('$update->id');
        }
        
        // 触发参数处理事件
        $event      = new UpdateAdminGroupTakeParamsEvent();
        $takeParams = null;
        if ($triggerEvent) {
            $event->data    = $update;
            $event->operate = UpdateAdminGroupTakeParamsEvent::OPERATE_DEFAULT;
            $takeParams     = Event::trigger($event, [], true);
        }
        
        $this->startTrans();
        try {
            $this->checkData($update);
            $info = $this->lock(true)->getInfo($update->id);
            if ($info->system && !$update->status) {
                throw new VerifyException('无法禁用系统权限');
            }
            
            // 触发更新前事件
            $event             = new UpdateAdminGroupBeforeEvent();
            $event->data       = $update;
            $event->info       = $info;
            $event->takeParams = $takeParams;
            $event->operate    = UpdateAdminGroupTakeParamsEvent::OPERATE_DEFAULT;
            $triggerEvent ? Event::trigger($event) : $this->triggerCallback(self::CALLBACK_BEFORE, $event);
            
            $this->whereEntity(AdminGroupField::id($update->id))->saveData($update);
            
            // 触发更新后事件
            $event             = new UpdateAdminGroupAfterEvent();
            $event->data       = $update;
            $event->info       = $this->getInfo($info->id);
            $event->operate    = UpdateAdminGroupTakeParamsEvent::OPERATE_DEFAULT;
            $event->takeParams = $takeParams;
            $triggerEvent ? Event::trigger($event) : $this->triggerCallback(self::CALLBACK_AFTER, $event);
            
            $this->commit();
        } catch (Throwable $e) {
            $this->rollback();
            
            throw $e;
        }
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
        foreach (explode(',', $data->rule) as $id) {
            if (!isset($idList[$id])) {
                continue;
            }
            $newRule[] = $id;
        }
        $data->setRule($newRule);
    }
    
    
    /**
     * 删除用户组
     * @param int  $data
     * @param bool $triggerEvent 是否触发事件，否则触发回调
     * @return int
     * @throws Throwable
     */
    public function deleteInfo($data, bool $triggerEvent = true) : int
    {
        // 触发参数处理事件
        $event      = new DeleteAdminGroupTakeParamsEvent();
        $takeParams = null;
        if ($triggerEvent) {
            $event->id  = $data;
            $takeParams = Event::trigger($event, [], true);
        }
        
        $this->startTrans();
        try {
            $info = $this->getInfo($data);
            if ($info->system) {
                throw new VerifyException('系统管理权限组禁止删除');
            }
            
            // 触发删除前事件
            $event             = new DeleteAdminGroupBeforeEvent();
            $event->info       = $info;
            $event->takeParams = $takeParams;
            $triggerEvent ? Event::trigger($event) : $this->triggerCallback(self::CALLBACK_BEFORE, $event);
            
            // 删除子角色
            $childIds = array_keys(ArrayHelper::listByKey(
                $this->getChildList($info->id),
                AdminGroupField::id()->name()
            ));
            if ($childIds) {
                $this->whereEntity(AdminGroupField::id('in', $childIds))->delete();
            }
            
            $res = parent::deleteInfo($data);
            
            // 触发删除后事件
            $event             = new DeleteAdminGroupAfterEvent();
            $event->info       = $info;
            $event->takeParams = $takeParams;
            $triggerEvent ? Event::trigger($event) : $this->triggerCallback(self::CALLBACK_AFTER, $event);
            
            $this->commit();
            
            return $res;
        } catch (Throwable $e) {
            $this->rollback();
            
            throw $e;
        }
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
        $list = ArrayHelper::treeToList($list, AdminGroupInfo::child()->name());
        
        return $list;
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
     * @param bool $triggerEvent 是否触发事件，否则触发回调
     * @throws Throwable
     */
    public function changeStatus($id, bool $status, bool $triggerEvent = true)
    {
        $update = AdminGroupField::init();
        $update->setId($id);
        $update->setStatus($status);
        
        // 触发参数处理事件
        $event      = new UpdateAdminGroupTakeParamsEvent();
        $takeParams = null;
        if ($triggerEvent) {
            $event->data    = $update;
            $event->operate = UpdateAdminGroupTakeParamsEvent::OPERATE_STATUS;
            $takeParams     = Event::trigger($event, [], true);
        }
        
        $this->startTrans();
        try {
            $info = $this->lock(true)->getInfo($id);
            if ($info->system) {
                throw new VerifyException('无法禁用系统权限');
            }
            
            // 触发更新前事件
            $event             = new UpdateAdminGroupBeforeEvent();
            $event->data       = $update;
            $event->info       = $info;
            $event->operate    = UpdateAdminGroupTakeParamsEvent::OPERATE_STATUS;
            $event->takeParams = $takeParams;
            $triggerEvent ? Event::trigger($event) : $this->triggerCallback(self::CALLBACK_BEFORE, $event);
            
            $this->whereEntity(AdminGroupField::id($id))->saveData($update);
            
            // 触发更新后事件
            $event             = new UpdateAdminGroupAfterEvent();
            $event->data       = $update;
            $event->info       = $this->getInfo($info->id);
            $event->operate    = UpdateAdminGroupTakeParamsEvent::OPERATE_STATUS;
            $event->takeParams = $takeParams;
            $triggerEvent ? Event::trigger($event) : $this->triggerCallback(self::CALLBACK_AFTER, $event);
            
            $this->commit();
        } catch (Throwable $e) {
            $this->rollback();
            
            throw $e;
        }
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