<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\config\SystemConfig as Model;
use BusyPHP\app\admin\model\system\config\SystemConfigField;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 开发模式-系统键值对配置管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:35 下午 SystemConfig.php $
 */
class SystemConfigController extends InsideController
{
    /**
     * @var Model
     */
    private $model;
    
    
    public function initialize($checkLogin = true)
    {
        if (!$this->app->isDebug()) {
            abort(404);
        }
        
        parent::initialize($checkLogin);
        
        $this->model = Model::init();
    }
    
    
    /**
     * 配置列表
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function index()
    {
        if ($this->pluginTable) {
            return $this->success($this->pluginTable->build($this->model));
        }
        
        return $this->display();
    }
    
    
    /**
     * 添加配置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function add()
    {
        if ($this->isPost()) {
            $insert = SystemConfigField::init();
            $insert->setName($this->post('name/s'));
            $insert->setType($this->post('type/s'));
            $insert->setSystem($this->post('system/b'));
            $insert->setAppend($this->post('append/b'));
            $this->model->insertData($insert);
            $this->log()->record(self::LOG_INSERT, '增加系统配置');
            
            return $this->success('添加成功');
        }
        
        return $this->display();
    }
    
    
    /**
     * 修改配置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     */
    public function edit()
    {
        if ($this->isPost()) {
            $update = SystemConfigField::init();
            $update->setId($this->post('id/d'));
            $update->setName($this->post('name/s'));
            $update->setType($this->post('type/s'));
            $update->setSystem($this->post('system/b'));
            $update->setAppend($this->post('append/b'));
            $this->model->updateData($update);
            $this->log()->record(self::LOG_UPDATE, '修改系统配置');
            
            return $this->success('修改成功');
        }
        
        $this->assign('info', $this->model->getInfo($this->get('id/d')));
        
        return $this->display('add');
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
            $this->log()->record(self::LOG_DELETE, '删除系统配置');
            
            return '删除成功';
        });
        
        return $this->batch();
    }
} 