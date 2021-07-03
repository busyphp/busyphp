<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\exception\VerifyException;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\admin\user\AdminUserField;

/**
 * 后台管理员管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午12:02 下午 User.php $
 */
class UserController extends InsideController
{
    /**
     * @var AdminUser
     */
    private $model;
    
    
    public function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = AdminUser::init();
    }
    
    
    /**
     * 列表
     */
    public function index()
    {
        return $this->select($this->model);
    }
    
    
    /**
     * 增加
     */
    public function add()
    {
        return $this->submit('post', function($data) {
            $update = AdminUserField::init();
            $update->setUsername($data['username']);
            $update->setPassword($data['password'], $data['confirm_password']);
            $update->setPhone($data['phone']);
            $update->setEmail($data['email']);
            $update->setQq($data['qq']);
            $update->setGroupId($data['group_id']);
            $update->setChecked($data['checked']);
            $this->model->insertData($update);
            $this->log('添加管理员', $this->model->getHandleData(), self::LOG_INSERT);
            
            return '添加成功';
        }, function() {
            // 显示回调
            $this->bind(self::CALL_DISPLAY, function() {
                $info                  = array();
                $info['checked']       = 1;
                $info['group_options'] = AdminGroup::init()->getSelectOptions($info['group_id'] ?? 0);
                
                return $info;
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
            $update = AdminUserField::init();
            $update->setId($data['id']);
            $update->setPhone($data['phone']);
            $update->setEmail($data['email']);
            $update->setQq($data['qq']);
            $update->setGroupId($data['group_id']);
            $update->setChecked($data['checked']);
            $this->model->updateData($update);
            $this->log('修改管理员', $this->model->getHandleData(), self::LOG_UPDATE);
            
            return '修改成功';
        }, function() {
            // 显示回调
            $this->bind(self::CALL_DISPLAY, function() {
                $info                  = $this->model->getInfo($this->iGet('id'));
                $info['group_options'] = AdminGroup::init()->getSelectOptions($info['group_id']);
                
                return $info;
            });
            
            $this->setRedirectUrl();
            $this->submitName   = '修改';
            $this->templateName = 'add';
        });
    }
    
    
    /**
     * 修改密码
     */
    public function password()
    {
        return $this->submit('post', function($data) {
            $this->model->updatePassword($this->adminUserId, $data['password'], $data['confirm_password']);
            $this->log('修改管理员密码', $this->model->getHandleData(), self::LOG_UPDATE);
            
            return '修改密码成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                return $this->model->getInfo($this->iGet('id'));
            });
            
            $this->submitName = '修改密码';
            $this->setRedirectUrl();
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
            $this->log('删除管理员', array('id' => $params), self::LOG_DELETE);
            
            return '删除成功';
        });
        
        return $this->batch();
    }
    
    
    /**
     * 修改个人资料
     */
    public function personal_info()
    {
        return $this->submit('post', function($data) {
            $update = AdminUserField::init();
            $update->setId($this->adminUserId);
            $update->setPhone($data['phone']);
            $update->setEmail($data['email']);
            $update->setQq($data['qq']);
            $this->model->updateData($update);
            $this->log('修改管理员个人资料', $this->model->getHandleData(), self::LOG_UPDATE);
            
            return '修改成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                return $this->model->getInfo($this->adminUserId);
            });
            
            $this->setRedirectUrl(null);
            $this->submitName = '修改资料';
        });
    }
    
    
    /**
     * 修改个人密码
     */
    public function personal_password()
    {
        return $this->submit('post', function($data) {
            $data['old_password'] = trim($data['old_password']);
            if (!$data['old_password']) {
                throw new VerifyException('请输入旧密码', 'old_password');
            }
            if (AdminUser::createPassword($data['old_password']) != $this->adminUser['password']) {
                throw new VerifyException('旧密码输入错误', 'old_password');
            }
            
            $this->model->updatePassword($this->adminUserId, $data['password'], $data['confirm_password']);
            $this->log('修改管理员个人密码', $this->model->getHandleData(), self::LOG_UPDATE);
            
            return '修改成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                return $this->model->getInfo($this->adminUserId);
            });
            
            $this->setRedirectUrl(null);
            $this->submitName = '修改密码';
        });
    }
}