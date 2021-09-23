<?php

namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\model;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Arr;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
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
            $list = $this->order(AdminGroupField::id(), 'asc')->selectList();
            $list = Arr::listByKey($list, AdminGroupField::id());
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
     * @throws DataNotFoundException
     * @throws DbException
     * @throws VerifyException
     */
    public function deleteInfo($data) : int
    {
        $info = $this->getInfo($data);
        if ($info->system) {
            throw new VerifyException('系统管理权限组禁止删除');
        }
        
        return parent::deleteInfo($data);
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
     * @param string $disabled
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
        $this->clearCache();
        
        $list      = $this->getList(true);
        $menuModel = SystemMenu::init();
        foreach ($list as $id => $r) {
            $menuModel->getAdminMenu($r->id, true);
            $menuModel->getAdminNav($r->id, true);
        }
    }
    
    
    public function onChanged(string $method, $id, array $options)
    {
        $this->getList(true);
    }
}