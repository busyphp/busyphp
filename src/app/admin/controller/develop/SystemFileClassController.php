<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassField;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass as Model;

/**
 * 开发模式-系统附件分类管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:34 下午 SystemFileClass.php $
 */
class SystemFileClassController extends InsideController
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
        $this->assign('type_options', Transform::arrayToOption(SystemFile::getTypes()));
        $this->setSelectLimit(false);
        $this->setSelectOrder('sort ASC,id DESC');
        
        return $this->select($this->model);
    }
    
    
    /**
     * 增加
     */
    public function add()
    {
        return $this->submit('post', function($data) {
            $insert = SystemFileClassField::init();
            $insert->setName($data['name']);
            $insert->setVar($data['var']);
            $insert->setType($data['type']);
            $insert->setAdminShow($data['admin_show']);
            $insert->setHomeShow($data['home_show']);
            $insert->setHomeUpload($data['home_upload']);
            $insert->setHomeLogin($data['home_login']);
            $insert->setIsSystem($data['is_system']);
            $insert->setSort($data['sort']);
            $this->model->insertData($insert);
            $this->log('增加系统附件分类', $this->model->getHandleData(), self::LOG_INSERT);
            
            return '添加成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                $array['sort']         = 50;
                $array['is_system']    = 0;
                $array['admin_show']   = 1;
                $array['type_options'] = Transform::arrayToOption(SystemFile::getTypes());
                
                return $array;
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
            $update = SystemFileClassField::init();
            $update->setId($data['id']);
            $update->setName($data['name']);
            $update->setAdminShow($data['admin_show']);
            $update->setHomeShow($data['home_show']);
            $update->setHomeUpload($data['home_upload']);
            $update->setHomeLogin($data['home_login']);
            $update->setSort($data['sort']);
            $this->model->updateData($update);
            $this->log('修改系统附件分类', $this->model->getHandleData(), self::LOG_UPDATE);
            
            return '修改成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                $info                 = $this->model->getInfo($this->iGet('id'));
                $info['type_options'] = Transform::arrayToOption(SystemFile::getTypes(), '', '', $info['type']);
                $info['type_name']    = SystemFile::getTypes($info['type']);
                
                return $info;
            });
            
            $this->templateName = 'add';
            $this->setRedirectUrl();
            $this->submitName = '修改';
        });
    }
    
    
    /**
     * 定义排序
     */
    public function set_sort()
    {
        $this->bind(self::CALL_BATCH_EACH_AFTER, function($params) {
            foreach ($params as $id => $value) {
                $this->model->setSort($id, $value);
            }
            $this->log('排序系统附件分类', $params, self::LOG_UPDATE);
            
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
            $this->log('删除系统附件分类', ['id' => $params], self::LOG_DELETE);
            
            return '删除成功';
        });
        
        return $this->batch();
    }
} 