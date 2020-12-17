<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\App;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\general\controller\Verify;
use BusyPHP\exception\VerifyException;
use BusyPHP\exception\AppException;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\setting\AdminSetting;
use think\facade\Session;

/**
 * 登录
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午10:51 上午 Passport.php $
 */
class Passport extends InsideController
{
    public function initialize($checkLogin = false)
    {
        parent::initialize($checkLogin);
    }
    
    
    public function index()
    {
        return $this->redirect(url('admin_login'));
    }
    
    
    /**
     * 登录
     */
    public function login()
    {
        return $this->submit('post', function($data) {
            $username = $data['username'];
            $password = $data['password'];
            $verify   = $data['verify'];
            $isVerify = AdminSetting::init()->isVerify();
            
            try {
                $adminModel = AdminUser::init();
                $adminModel->setCallback(AdminUser::CALLBACK_PROCESS, function() use ($verify, $isVerify) {
                    if ($isVerify) {
                        try {
                            Verify::check('admin_login', $verify);
                            Verify::clear('admin_login');
                        } catch (AppException $e) {
                            throw new VerifyException($e->getMessage(), 'verify');
                        }
                    }
                });
                
                $user                = $adminModel->login($username, $password);
                $this->adminUserId   = $user['id'];
                $this->adminUsername = $user['username'];
                $this->log('登录成功');
                $redirectUrl = Session::get(self::ADMIN_LOGIN_REDIRECT_URL);
                $redirectUrl = $redirectUrl ?: URL_APP;
                
                return $this->success('登录成功', $redirectUrl, MESSAGE_SUCCESS_GOTO);
            } catch (VerifyException $e) {
                if ($e->getCode()) {
                    $this->log('密码输入错误', [
                        'username' => $username,
                        'password' => $password,
                    ]);
                }
                
                throw new VerifyException($e);
            }
        }, function() {
            $list  = glob(App::getPublicPath('assets/admin/images/login') . '*.*');
            $array = [];
            foreach ($list as $item) {
                $array[] = URL_ASSETS . 'admin/images/login/' . pathinfo($item, PATHINFO_BASENAME);
            }
            
            $this->assign('is_verify', AdminSetting::init()->isVerify());
            $this->assign('bg', $array[rand(0, count($array) - 1)]);
            $this->assign('page_title', $this->publicConfig['title']);
            $this->assign('verify_url', Verify::url('admin_login'));
            $this->assign('year', date('Y'));
            $this->assign('version', $this->app->getBusyName() . ' V' . $this->app->getBusyVersion());
        });
    }
    
    
    /**
     * 退出登录
     */
    public function out()
    {
        $this->log('退出登录');
        AdminUser::outLogin();
        
        return $this->success('退出成功', url('admin_login'), MESSAGE_SUCCESS_GOTO);
    }
}