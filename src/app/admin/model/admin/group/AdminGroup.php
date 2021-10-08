<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\App;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\helper\util\Str;
use BusyPHP\model;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Arr;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 用户组模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/30 下午5:56 下午 AdminGroup.php $
 * @method AdminGroupInfo findInfo($data = null, $notFoundMessage = null)
 * @method AdminGroupInfo getInfo($data, $notFoundMessage = null)
 * @method AdminGroupInfo[] selectList()
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
        return Arr::listByKey($this->getList(), AdminGroupField::id());
    }
    
    
    /**
     * 添加管理角色
     * @param AdminGroupField $insert
     * @return int
     * @throws DbException
     * @throws ParamInvalidException
     * @throws VerifyException
     */
    public function insertData(AdminGroupField $insert)
    {
        $this->checkData($insert);
        
        return $this->addData($insert);
    }
    
    
    /**
     * 修改管理角色
     * @param AdminGroupField $update $id
     * @throws ParamInvalidException
     * @throws DbException
     * @throws VerifyException
     */
    public function updateData(AdminGroupField $update)
    {
        if ($update->id < 1) {
            throw new ParamInvalidException('id');
        }
        
        $this->checkData($update);
        $info = $this->getInfo($update->id);
        if ($info->system && !$update->status) {
            throw new VerifyException('无法禁用系统权限');
        }
        
        $this->whereEntity(AdminGroupField::id($update->id))->saveData($update);
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
     * @param int $data
     * @return int
     * @throws VerifyException
     * @throws \Exception
     */
    public function deleteInfo($data) : int
    {
        $this->startTrans();
        try {
            $info = $this->getInfo($data);
            if ($info->system) {
                throw new VerifyException('系统管理权限组禁止删除');
            }
            
            // 删除子角色
            $childIds = array_keys(Arr::listByKey($this->getChildList($info->id), AdminGroupField::id()));
            if ($childIds) {
                $this->whereEntity(AdminGroupField::id('in', $childIds))->delete();
            }
            
            $res = parent::deleteInfo($data);
            $this->commit();
            
            return $res;
        } catch (Exception $e) {
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
        $list = Arr::listToTree($this->selectList(), AdminGroupField::id(), AdminGroupField::parentId(), AdminGroupInfo::child(), $id);
        $list = Arr::treeToList($list, AdminGroupInfo::child());
        
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
        return Arr::listToTree($this->getList(), AdminGroupField::id(), AdminGroupField::parentId(), AdminGroupInfo::child(), 0);
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
    public function getTreeOptions($selectedId = '', $disabledId = '', $list = [], $space = '')
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
     * @param $id
     * @param $status
     * @throws DbException
     */
    public function changeStatus($id, bool $status)
    {
        $info = $this->getInfo($id);
        if ($info->system) {
            throw new VerifyException('无法禁用系统权限');
        }
        
        $this->whereEntity(AdminGroupField::id($id))->setField(AdminGroupField::status(), $status ? 1 : 0);
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
     * @param AdminUserInfo $adminUserInfo 用户数据
     * @param string        $path 检测的路由路径
     * @return bool
     */
    public static function checkPermission(?AdminUserInfo $adminUserInfo, $path = null) : bool
    {
        if (!$adminUserInfo) {
            return false;
        }
        
        // 拥有超级管理员权限，放行所有请求
        if ($adminUserInfo->groupHasSystem) {
            return true;
        }
        
        // 放行白名单
        $request     = App::getInstance()->request;
        $currentPath = Str::snake($path ?: $request->controller() . '/' . $request->action());
        foreach (self::$allowControllers as $item) {
            if (0 === strpos($currentPath, $item)) {
                return true;
            }
        }
        
        // 是否在规则内
        return in_array(Str::snake($path ?: App::getInstance()->request->getPath()), $adminUserInfo->groupRulePaths);
    }
}