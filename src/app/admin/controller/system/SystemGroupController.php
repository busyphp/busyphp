<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\App;
use BusyPHP\app\admin\controller\AdminController;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\plugin\table\TableHandler;
use BusyPHP\app\admin\plugin\TablePlugin;
use BusyPHP\app\admin\plugin\tree\TreeFlatItemStruct;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\group\AdminGroupField;
use BusyPHP\app\admin\model\admin\group\AdminGroupInfo;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\model\system\menu\SystemMenuField;
use BusyPHP\app\admin\model\system\menu\SystemMenuInfo;
use BusyPHP\app\admin\plugin\tree\TreeHandler;
use BusyPHP\app\admin\plugin\TreePlugin;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\TransHelper;
use BusyPHP\Model;
use BusyPHP\model\Map;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 后台用户组权限管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
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
            $this->pluginTable->setHandler(new class extends TableHandler {
                public function query(TablePlugin $plugin, Model $model, Map $data) : void
                {
                    $model->order(AdminGroupField::sort(), 'asc');
                    $model->order(AdminGroupField::id(), 'desc');
                }
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
            $insert->setParentId($this->post('parent_id/d'));
            $insert->setName($this->post('name/s', 'trim'));
            $insert->setDefaultMenuId($this->post('default_menu_id/d'));
            $insert->setRule($this->hashToId($this->post('rule/a')));
            $insert->setStatus($this->post('status/b'));
            $this->model->insertData($insert);
            $this->log()->record(self::LOG_INSERT, '添加管理角色');
            
            return $this->success('添加成功');
        }
        
        
        // 权限
        if ($this->pluginTree) {
            return $this->ruleList();
        }
        
        // 显示修改
        $this->assign('info', ['status' => true, 'system' => false]);
        $this->assign('menu_options', TransHelper::toOptionHtml(SystemMenu::init()
            ->getSafeTree(), null, SystemMenuField::id(), SystemMenuField::name()));
        $this->assign('group_options', $this->model->getTreeOptions($this->get('id/s')));
        
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
            $update->setId($this->post('id/d'));
            $update->setParentId($this->post('parent_id/d'));
            $update->setName($this->post('name/s', 'trim'));
            $update->setDefaultMenuId($this->post('default_menu_id/d'));
            $update->setRule($this->hashToId($this->post('rule/a', [])));
            $update->setStatus($this->post('status/b'));
            $this->model->updateData($update);
            $this->log()->record(self::LOG_UPDATE, '修改管理角色');
            
            return $this->success('修改成功');
        }
        
        // 权限列表
        $id   = $this->get('id/d');
        $info = $this->model->getInfo($id);
        if ($this->pluginTree) {
            return $this->ruleList($info);
        }
        
        // 修改显示
        $this->assign('info', $info);
        $this->assign('menu_options', TransHelper::toOptionHtml(SystemMenu::init()
            ->getSafeTree(), $info->defaultMenuId, SystemMenuField::id(), SystemMenuField::name()));
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
     * 权限列表
     * @param AdminGroupInfo $info
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    private function ruleList(?AdminGroupInfo $info = null) : Response
    {
        $this->pluginTree->setHandler(new class($info, $this, $this->model) extends TreeHandler {
            /**
             * @var AdminGroupInfo|null
             */
            private $info;
            
            /**
             * @var AdminController
             */
            private $controller;
            
            /**
             * @var AdminGroup
             */
            private $adminGroupModel;
            
            
            public function __construct(?AdminGroupInfo $info, AdminController $controller, AdminGroup $adminGroupModel)
            {
                $this->info            = $info;
                $this->controller      = $controller;
                $this->adminGroupModel = $adminGroupModel;
            }
            
            
            /**
             * @param TreePlugin       $plugin
             * @param SystemMenu|Model $model
             */
            public function query(TreePlugin $plugin, Model $model) : void
            {
                // 继承父角色节点
                $groupId = $this->controller->get('group_id/d');
                if ($this->info && $groupId == $this->info->id) {
                    throw new VerifyException('父角色不能是自己');
                }
                
                if ($groupId > 1) {
                    $groupInfo = $this->adminGroupModel->getInfo($groupId);
                    if ($groupInfo->ruleIds) {
                        $model->whereEntity(SystemMenuField::id('in', $groupInfo->ruleIds));
                    } else {
                        throw new VerifyException('该角色权限信息异常');
                    }
                }
                
                $model->whereSafe()->orderSort();
            }
            
            
            /**
             * @param SystemMenuInfo     $item
             * @param TreeFlatItemStruct $node
             */
            public function node($item, TreeFlatItemStruct $node) : void
            {
                $node->setParent($item->parentHash);
                $node->setText($item->name);
                $node->setId($item->hash);
                $node->setIcon($item->icon);
                $node->setAAttr('data-id', $item->id);
                
                if (!$this->info) {
                    return;
                }
                
                // 展开选中项的父节点
                if (in_array($item->id, $this->info->ruleIndeterminate)) {
                    $node->state->setOpened(true);
                }
                
                // 设为选中
                if (in_array($item->id, $this->info->rule)) {
                    $node->state->setSelected(true);
                }
            }
        });
        
        return $this->success($this->pluginTree->build(SystemMenu::init()));
    }
    
    
    /**
     * 删除管理角色
     * @throws Exception
     */
    public function delete()
    {
        foreach ($this->param('id/list/请选择要删除的角色') as $id) {
            $this->model->deleteInfo($id);
        }
        
        $this->log()->record(self::LOG_DELETE, '删除管理角色');
        
        return $this->success('删除成功');
    }
    
    
    /**
     * 启用/禁用角色
     * @throws DbException
     */
    public function change_status()
    {
        $status = $this->get('status/b');
        $this->model->changeStatus($this->get('id/d'), $status);
        $this->log()->record(self::LOG_UPDATE, '启用/禁用角色');
        
        return $this->success($status ? '启用成功' : '禁用成功');
    }
    
    
    /**
     * 排序
     * @throws DbException
     */
    public function sort()
    {
        $this->model->setSort($this->param('sort/list'));
        $this->log()->record(self::LOG_UPDATE, '排序管理角色');
        $this->updateCache();
        
        return $this->success('排序成功');
    }
}