<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\exception\VerifyException;
use Exception;
use think\Response;

/**
 * 用户通用
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/3 下午下午8:30 UserController.php $
 */
class UserController extends InsideController
{
    /**
     * 修改个人资料
     * @return Response
     * @throws Exception
     */
    public function profile()
    {
        if ($this->isPost()) {
            $update = AdminUserField::init();
            $update->setId($this->adminUserId);
            $update->setUsername($this->post('username/s', 'trim'));
            $update->setPhone($this->post('phone/s', 'trim'));
            $update->setEmail($this->post('email/s', 'trim'));
            $update->setQq($this->post('qq/s', 'trim'));
            AdminUser::init()->whereEntity(AdminUserField::id($this->adminUserId))->updateData($update);
            $this->log()->record(self::LOG_UPDATE, '修改个人资料');
            
            return $this->success('修改成功');
        }
        
        $this->assign('info', $this->adminUser);
        
        return $this->display();
    }
    
    
    /**
     * 修改个人密码
     * @return Response
     * @throws Exception
     */
    public function password()
    {
        if ($this->isPost()) {
            $oldPassword = $this->post('old_password/s', 'trim');
            if (!$oldPassword) {
                throw new VerifyException('请输入登录密码', 'old_password');
            }
            
            if (!AdminUser::verifyPassword($oldPassword, $this->adminUser->password)) {
                throw new VerifyException('登录密码输入错误', 'old_password');
            }
            
            AdminUser::init()
                ->updatePassword($this->adminUserId, $this->post('password/s', 'trim'), $this->post('confirm_password/s', 'trim'));
            $this->log()
                ->filterParams(['old_password', 'password', 'confirm_password'])
                ->record(self::LOG_UPDATE, '修改个人密码');
            
            return $this->success('修改成功');
        }
        
        return $this->display();
    }
}