<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\config\SystemConfig as Model;
use BusyPHP\app\admin\model\system\config\SystemConfigField;

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
     * 列表
     */
    public function index()
    {
        $this->setSelectLimit(false);
        
        return $this->select($this->model);
    }
    
    
    /**
     * 增加
     */
    public function add()
    {
        return $this->submit('post', function($data) {
            $insert = SystemConfigField::init();
            $insert->setName($data['name']);
            $insert->setType($data['type']);
            $insert->setIsSystem($data['is_system']);
            $insert->setIsAppend($data['is_append']);
            $this->model->insertData($insert);
            $this->log('增加系统配置', $this->model->getHandleData(), self::LOG_INSERT);
            
            return '添加成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                return [
                    'is_system' => 0,
                    'type'      => ''
                ];
            });
            
            $this->setRedirectUrl(url('index'));
            $this->submitName = '添加';
        });
    }
    
    
    /**
     * 修改
     */
    public function edit()
    {
        return $this->submit('post', function($data) {
            $insert = SystemConfigField::init();
            $insert->setId($data['id']);
            $insert->setName($data['name']);
            $insert->setIsAppend($data['is_append']);
            $this->model->updateData($insert);
            $this->log('修改系统配置', $this->model->getHandleData(), self::LOG_INSERT);
            
            return '修改成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                return $this->model->getInfo($this->iGet('id'));
            });
            
            $this->setRedirectUrl();
            $this->templateName = 'add';
            $this->submitName   = '修改';
        });
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
            $this->log('删除系统配置', ['id' => $params], self::LOG_DELETE);
            
            return '删除成功';
        });
        
        return $this->batch();
    }
} 