<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\js\struct\TreeFlatItemStruct;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\group\AdminGroupField;
use BusyPHP\app\admin\model\admin\group\AdminGroupInfo;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\model\system\menu\SystemMenuField;
use BusyPHP\app\admin\model\system\menu\SystemMenuInfo;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Transform;
use BusyPHP\model\Map;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 后台用户组权限管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午12:11 下午 Group.php $
 */
class SystemGroupController extends InsideController
{
    /**
     * @var AdminGroup
     */
    private $model;
    
    
    public function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = AdminGroup::init();
    }
    
    
    /**
     * 角色列表
     * @return Response
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function index()
    {
        // 角色列表数据
        if ($this->pluginTable) {
            $this->pluginTable->sortField = '';
            $this->pluginTable->sortOrder = '';
            $this->pluginTable->setQueryHandler(function(AdminGroup $model, Map $data) {
                $model->order(AdminGroupField::sort(), 'asc');
                $model->order(AdminGroupField::id(), 'desc');
            });
            
            return $this->success($this->pluginTable->build($this->model));
        }
        
        return $this->display();
    }
    
    
    /**
     * 增加管理角色
     * @return Response
     * @throws VerifyException
     * @throws DbException
     * @throws ParamInvalidException
     */
    public function add()
    {
        // 添加
        if ($this->isPost()) {
            $insert = AdminGroupField::init();
            $insert->setParentId($this->request->post('parent_id', 0, 'intval'));
            $insert->setName($this->request->post('name', '', 'trim'));
            $insert->setDefaultMenuId($this->request->post('default_menu_id', 0, 'intval'));
            $insert->setRule($this->hashToId($this->request->post('rule', [])));
            $insert->setStatus($this->request->post('status', 0, 'intval') > 0);
            $this->model->insertData($insert);
            
            $this->log('增加管理角色', $this->model->getHandleData(), self::LOG_INSERT);
            
            return $this->success('添加成功');
        }
        
        
        // 权限
        if ($this->pluginTree) {
            return $this->ruleList();
        }
        
        // 默认菜单选项
        if ($this->pluginSelectPicker) {
            return $this->menuList();
        }
        
        // 显示修改
        $this->assign('info', ['status' => true]);
        $this->assign('menu_options', Transform::arrayToOption(SystemMenu::init()
            ->getSafeTree(), SystemMenuField::id(), SystemMenuField::name()));
        $this->assign('group_options', $this->model->getTreeOptions());
        
        return $this->display();
    }
    
    
    /**
     * 修改管理角色
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ParamInvalidException
     * @throws VerifyException
     */
    public function edit()
    {
        // 修改
        if ($this->isPost()) {
            $update = AdminGroupField::init();
            $update->setId($this->request->post('id', 0, 'intval'));
            $update->setParentId($this->request->post('parent_id', 0, 'intval'));
            $update->setName($this->request->post('name', '', 'trim'));
            $update->setDefaultMenuId($this->request->post('default_menu_id', 0, 'intval'));
            $update->setRule($this->hashToId($this->request->post('rule', [])));
            $update->setStatus($this->request->post('status', 0, 'intval') > 0);
            $this->model->updateData($update);
            $this->log('修改管理角色', $this->model->getHandleData(), self::LOG_UPDATE);
            
            return $this->success('修改成功');
        }
        
        // 权限列表
        $id   = $this->request->get('id', 0, 'intval');
        $info = $this->model->getInfo($id);
        if ($this->pluginTree) {
            return $this->ruleList($info);
        }
        
        // 默认菜单选项
        if ($this->pluginSelectPicker) {
            return $this->menuList();
        }
        
        // 修改显示
        $this->assign('info', $info);
        $this->assign('menu_options', Transform::arrayToOption(SystemMenu::init()
            ->getSafeTree(), SystemMenuField::id(), SystemMenuField::name(), $info->defaultMenuId));
        $this->assign('group_options', $this->model->getTreeOptions($info->parentId, $info->id));
        
        return $this->display('add');
    }
    
    
    /**
     * Hash转ID集合
     * @param $rule
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     */
    private function hashToId(array $rule) : array
    {
        $idRule   = [];
        $hashList = SystemMenu::init()->getHashList(true);
        foreach ($rule as $item) {
            if (!isset($hashList[$item])) {
                continue;
            }
            
            $idRule[] = $hashList[$item]->id;
        }
        
        return $idRule;
    }
    
    
    /**
     * 顶级菜单列表
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    private function menuList()
    {
        $this->pluginSelectPicker->setQueryHandler(function(SystemMenu $model) {
            // 继承父角色节点
            $groupId = $this->request->get('group_id', 0, 'intval');
            if ($groupId > 1) {
                $groupInfo = $this->model->getInfo($groupId);
                $model->whereEntity(SystemMenuField::id('in', $groupInfo->ruleIds));
            }
            
            $model->whereSafe()->orderSort();
            $model->whereEntity(SystemMenuField::parentPath(''));
        });
        
        return $this->success($this->pluginSelectPicker->build(SystemMenu::init()));
    }
    
    
    /**
     * 权限列表
     * @param AdminGroupInfo $info
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    private function ruleList(?AdminGroupInfo $info = null) : Response
    {
        $this->pluginTree->setQueryHandler(function(SystemMenu $model) use ($info) {
            // 继承父角色节点
            $groupId = $this->request->get('group_id', 0, 'intval');
            if ($info && $groupId == $info->id) {
                throw new VerifyException('父角色不能是自己');
            }
            
            if ($groupId > 1) {
                $groupInfo = $this->model->getInfo($groupId);
                if ($groupInfo->ruleIds) {
                    $model->whereEntity(SystemMenuField::id('in', $groupInfo->ruleIds));
                } else {
                    throw new VerifyException('该角色权限信息异常');
                }
            }
            
            $model->whereSafe()->orderSort();
        });
        $this->pluginTree->setNodeHandler(function(SystemMenuInfo $item, TreeFlatItemStruct $node) use ($info) {
            $node->setParent($item->parentHash);
            $node->setText($item->name);
            $node->setId($item->hash);
            $node->setIcon($item->icon);
            
            if (!$info) {
                return;
            }
            
            // 展开选中项的父节点
            if (in_array($item->id, $info->ruleIndeterminate)) {
                $node->state->setOpened(true);
            }
            
            // 设为选中
            if (in_array($item->id, $info->rule)) {
                $node->state->setSelected(true);
            }
        });
        
        return $this->success($this->pluginTree->build(SystemMenu::init()));
    }
    
    
    /**
     * 删除管理角色
     */
    public function delete()
    {
        $this->bind(self::CALL_BATCH_EACH, function($id) {
            $this->model->deleteInfo($id);
        });
        
        $this->bind(self::CALL_BATCH_EACH_AFTER, function($params) {
            $this->log('删除管理角色', ['id' => $params], self::LOG_DELETE);
            
            return '删除成功';
        });
        
        return $this->batch();
    }
    
    
    /**
     * 排序
     */
    public function sort()
    {
        $this->bind(self::CALL_BATCH_EACH_AFTER, function($params) {
            $data = [];
            foreach ($params as $key => $value) {
                $data[] = [
                    AdminGroupField::id()->field()   => $key,
                    AdminGroupField::sort()->field() => $value
                ];
            }
            $this->model->saveAll($data);
            $this->log('排序管理角色', $params, self::LOG_BATCH);
            $this->updateCache();
            
            return '排序成功';
        });
        
        return $this->batch('sort');
    }
}