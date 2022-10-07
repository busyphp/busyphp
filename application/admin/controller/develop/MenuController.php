<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\plugin\table\TableHandler;
use BusyPHP\app\admin\plugin\TablePlugin;
use BusyPHP\exception\VerifyException;
use BusyPHP\app\admin\model\system\menu\SystemMenuField;
use BusyPHP\Model;
use BusyPHP\model\Map;
use RangeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;
use Throwable;

/**
 * 开发模式-后台菜单管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:35 下午 MenuController.php $
 */
class MenuController extends InsideController
{
    /**
     * @var SystemMenu
     */
    private $model;
    
    
    public function initialize($checkLogin = true)
    {
        if (!$this->app->isDebug()) {
            abort(404);
        }
        
        parent::initialize($checkLogin);
        
        $this->model = SystemMenu::init();
    }
    
    
    /**
     * 栏目列表
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function index() : Response
    {
        if ($this->pluginTable) {
            $this->pluginTable->sortField = '';
            $this->pluginTable->sortOrder = '';
            $this->pluginTable->setHandler(new class extends TableHandler {
                public function query(TablePlugin $plugin, Model $model, Map $data) : void
                {
                    $model->order(SystemMenuField::sort(), 'asc');
                    $model->order(SystemMenuField::id(), 'asc');
                    
                    if (!SystemMenu::DEBUG) {
                        $model->whereEntity(SystemMenuField::system(0));
                    }
                }
            });
            
            return $this->success($this->pluginTable->build($this->model));
        }
        
        return $this->display();
    }
    
    
    /**
     * 增加菜单
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    public function add() : Response
    {
        if ($this->isPost()) {
            $this->model->createInfo(SystemMenuField::parse($this->post()), $this->post('auto/a'), $this->post('auto_suffix/s'));
            $this->updateCache();
            $this->log()->record(self::LOG_INSERT, '添加系统菜单');
            
            return $this->success('添加菜单成功');
        }
        
        $id         = $this->get('id/d');
        $parentPath = '';
        if ($id > 0) {
            $info       = $this->model->getInfo($id);
            $parentPath = $info->path;
        }
        
        $this->assign('parent_options', $this->model->getTreeOptions($parentPath));
        $this->assign('target_list', $this->model::getTargets());
        $this->assign('info', [
            'target'   => '',
            'hide'     => 0,
            'disabled' => 0,
            'system'   => 0
        ]);
        
        return $this->display();
    }
    
    
    /**
     * 修改菜单
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    public function edit() : Response
    {
        if ($this->isPost()) {
            $this->model->updateInfo(SystemMenuField::parse($this->post()));
            $this->updateCache();
            $this->log()->record(self::LOG_UPDATE, '修改系统菜单');
            
            return $this->success('修改菜单成功');
        } else {
            $info = $this->model->getInfo($this->get('id/d'));
            
            $this->assign('parent_options', $this->model->getTreeOptions($info->parentPath));
            $this->assign('target_list', $this->model::getTargets());
            $this->assign('info', $info);
            
            return $this->display('add');
        }
    }
    
    
    /**
     * 快速设置属性
     * @throws DataNotFoundException
     * @throws DbException
     * @throws VerifyException
     */
    public function set_attr() : Response
    {
        $type   = $this->get('type/s', 'trim');
        $id     = $this->get('id/d');
        $status = $this->get('status/b');
        
        switch ($type) {
            case 'disabled':
                $this->model->setDisabled($id, !$status);
            break;
            case 'hide':
                $this->model->setHide($id, !$status);
            break;
            default:
                throw new RangeException('未知类型');
        }
        
        $this->updateCache();
        $this->log()->record(self::LOG_UPDATE, '修改系统菜单属性');
        
        return $this->success('设置成功');
    }
    
    
    /**
     * 排序
     * @throws DbException
     */
    public function sort() : Response
    {
        $this->model->setSort($this->param('sort/list'));
        $this->log()->record(self::LOG_UPDATE, '排序系统菜单');
        $this->updateCache();
        
        return $this->success('排序成功');
    }
    
    
    /**
     * 删除
     * @throws Throwable
     */
    public function delete() : Response
    {
        foreach ($this->param('id/list/请选择要删除的菜单') as $id) {
            $this->model->deleteInfo($id);
        }
        
        $this->log()->record(self::LOG_DELETE, '删除系统菜单');
        $this->updateCache();
        
        return $this->success('删除成功');
    }
}