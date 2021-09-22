<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\app\admin\model\system\menu\SystemMenuField;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 开发模式-后台菜单管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:35 下午 SystemMenu.php $
 */
class SystemMenuController extends InsideController
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
            $this->pluginTable->setQueryHandler(function(SystemMenu $model) {
                $model->order(SystemMenuField::sort(), 'asc');
                $model->order(SystemMenuField::id(), 'desc');
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
            $data->setParentPath($this->request->post('parent_path', '', 'trim'));
            $data->setName($this->request->post('name', '', 'trim'));
            $data->setIcon($this->request->post('icon', '', 'trim'));
            $data->setPath($this->request->post('path', '', 'trim'));
            $data->setParams($this->request->post('params', '', 'trim'));
            $data->setTarget($this->request->post('target', '', 'trim'));
            $data->setHide(!$this->request->post('show', 0, 'intval'));
            $data->setDisabled(!$this->request->post('enable', 0, 'intval'));
            $data->setSystem($this->request->post('system', 0, 'intval'));
            
            $this->model->createMenu($data, $this->request->post('auto', []), $this->request->post('auto_suffix', '', 'trim'));
            $this->updateCache();
            
            return $this->success('添加菜单成功');
        } else {
            $id         = $this->request->get('id', 0, 'intval');
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
            $data->setId($this->request->post('id', '', 'intval'));
            $data->setParentPath($this->request->post('parent_path', '', 'trim'));
            $data->setName($this->request->post('name', '', 'trim'));
            $data->setIcon($this->request->post('icon', '', 'trim'));
            $data->setPath($this->request->post('path', '', 'trim'));
            $data->setParams($this->request->post('params', '', 'trim'));
            $data->setTarget($this->request->post('target', '', 'trim'));
            $data->setHide(!$this->request->post('show', 0, 'intval'));
            $data->setDisabled(!$this->request->post('enable', 0, 'intval'));
            $data->setSystem($this->request->post('system', 0, 'intval'));
            
            $this->model->updateMenu($data);
            $this->updateCache();
            
            return $this->success('添加菜单成功');
        } else {
            $info = $this->model->getInfo($this->request->get('id'));
            
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
        $type   = $this->request->get('type', '', 'trim');
        $id     = $this->request->get('id', '', 'intval');
        $status = $this->request->get('status', 0, 'intval') > 0;
        
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
        
        return $this->success('设置成功');
    }
    
    
    /**
     * 排序
     */
    public function set_sort()
    {
        $this->bind(self::CALL_BATCH_EACH_AFTER, function($params) {
            $data = [];
            foreach ($params as $key => $value) {
                $data[] = [
                    SystemMenuField::id()->field()   => $key,
                    SystemMenuField::sort()->field() => $value
                ];
            }
            $this->model->saveAll($data);
            $this->log('排序系统菜单', $params, self::LOG_BATCH);
            $this->updateCache();
            
            return '排序成功';
        });
        
        return $this->batch('sort');
    }
    
    
    /**
     * 删除
     */
    public function delete()
    {
        $this->bind(self::CALL_BATCH_EACH, function($id) {
            $this->model->deleteInfo($id);
        });
        
        $this->bind(self::CALL_BATCH_EACH_AFTER, function($params) {
            $this->log('删除系统菜单', ['id' => $params], self::LOG_DELETE);
            $this->updateCache();
            
            return '删除成功';
        });
        
        return $this->batch();
    }
}