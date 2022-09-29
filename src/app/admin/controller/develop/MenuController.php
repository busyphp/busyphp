<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\plugin\table\TableHandler;
use BusyPHP\app\admin\plugin\TablePlugin;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\app\admin\model\system\menu\SystemMenuField;
use BusyPHP\Model;
use BusyPHP\model\Map;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

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
    public function index()
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
     * @throws VerifyException
     * @throws Exception
     */
    public function add()
    {
        if ($this->isPost()) {
            $data = SystemMenuField::init();
            $data->setParentPath($this->post('parent_path/s'));
            $data->setName($this->post('name/s'));
            $data->setIcon($this->post('icon/s'));
            $data->setPath($this->post('path/s'));
            $data->setParams($this->post('params/s'));
            $data->setTarget($this->post('target/s'));
            $data->setHide(!$this->post('show/d'));
            $data->setDisabled(!$this->post('enable/d'));
            $data->setTopPath($this->post('top_path/s'));
            
            $this->model->createMenu($data, $this->post('auto/a'), $this->post('auto_suffix/s'));
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
        $this->assign('target_list', SystemMenu::getTargets());
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
     * @throws VerifyException
     * @throws ParamInvalidException
     * @throws Exception
     */
    public function edit()
    {
        if ($this->isPost()) {
            $data = SystemMenuField::init();
            $data->setId($this->post('id/d'));
            $data->setParentPath($this->post('parent_path/s'));
            $data->setName($this->post('name/s'));
            $data->setIcon($this->post('icon/s'));
            $data->setPath($this->post('path/s'));
            $data->setParams($this->post('params/s'));
            $data->setTarget($this->post('target/s'));
            $data->setHide(!$this->post('show/d'));
            $data->setDisabled(!$this->post('enable/d'));
            $data->setTopPath($this->post('top_path/s'));
            
            $this->model->updateMenu($data);
            $this->updateCache();
            $this->log()->record(self::LOG_UPDATE, '修改系统菜单');
            
            return $this->success('修改菜单成功');
        } else {
            $info = $this->model->getInfo($this->get('id/d'));
            
            $this->assign('parent_options', $this->model->getTreeOptions($info->parentPath));
            $this->assign('target_list', SystemMenu::getTargets());
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
    public function set_attr()
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
                throw new VerifyException('未知类型');
        }
        
        $this->updateCache();
        $this->log()->record(self::LOG_UPDATE, '修改系统菜单属性');
        
        return $this->success('设置成功');
    }
    
    
    /**
     * 排序
     * @throws DbException
     */
    public function sort()
    {
        $this->model->setSort($this->param('sort/list'));
        $this->log()->record(self::LOG_UPDATE, '排序系统菜单');
        $this->updateCache();
        
        return $this->success('排序成功');
    }
    
    
    /**
     * 删除
     * @throws Exception
     */
    public function delete()
    {
        foreach ($this->param('id/list/请选择要删除的菜单') as $id) {
            $this->model->deleteInfo($id);
        }
        
        $this->log()->record(self::LOG_DELETE, '删除系统菜单');
        $this->updateCache();
        
        return $this->success('删除成功');
    }
}