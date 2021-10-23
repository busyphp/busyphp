<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\App;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\setting\PublicSetting;
use BusyPHP\exception\VerifyException;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\setting\AdminSetting;
use BusyPHP\facade\CaptchaUrl;
use think\Response;

/**
 * 登录
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/26 下午下午2:25 PassportController.php $
 */
class PassportController extends InsideController
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
     * @return Response
     */
    public function login()
    {
        if ($this->isPost()) {
            $username  = $this->post('username/s', 'trim');
            $password  = $this->post('password/s', 'trim');
            $verify    = $this->post('verify/s', 'trim');
            $saveLogin = $this->post('save_login/b');
            
            try {
                $adminModel = AdminUser::init();
                $adminModel->setCallback(AdminUser::CALLBACK_PROCESS, function() use ($verify) {
                    if (AdminSetting::init()->isVerify()) {
                        try {
                            captcha_check($verify, 'admin_login');
                        } catch (VerifyException $e) {
                            throw new VerifyException($e->getMessage(), 'verify');
                        }
                    }
                });
                
                $user                = $adminModel->login($username, $password, $saveLogin);
                $this->adminUserId   = $user['id'];
                $this->adminUsername = $user['username'];
                $this->log()->filterParams(['password'])->record(self::LOG_DEFAULT, '登录成功');
                
                // 回跳地址
                $redirectUrl = $this->request->getRedirectUrl($this->request->getAppUrl(), false);
                $path        = parse_url($redirectUrl, PHP_URL_PATH);
                if ($path) {
                    $path = ltrim($path, '/');
                    $path = explode('.', $path);
                    array_pop($path);
                    $path = implode('/', $path);
                    if (in_array($path, ['admin/login', 'admin/out'])) {
                        $redirectUrl = $this->request->getAppUrl();
                    }
                }
                
                return $this->success('登录成功', $path ? $redirectUrl : $this->request->getAppUrl());
            } catch (VerifyException $e) {
                if ($e->getField() == 'verify') {
                    $this->log()->setUser(0, $username)->record(self::LOG_DEFAULT, '登录错误', '验证码错误');
                } elseif ($e->getCode() > 0) {
                    $this->log()->setUser(0, $username)->record(self::LOG_DEFAULT, '登录错误', '密码错误');
                }
                
                throw $e;
            }
        }
        
        $adminSetting  = AdminSetting::init();
        $publicSetting = PublicSetting::init();
        $loginBg       = $adminSetting->getLoginBg();
        if ($loginBg && is_file(App::urlToPath($loginBg))) {
            $bg = $loginBg;
        } else {
            $bgList = [];
            foreach (glob($this->app->getPublicPath('assets/admin/images/login/*.*')) as $item) {
                $bgList[] = $this->request->getAssetsUrl() . 'admin/images/login/' . pathinfo($item, PATHINFO_BASENAME);
            }
            if (!$bgList) {
                foreach (glob($this->app->getFrameworkPath('app/admin/static/images/login/*.*')) as $item) {
                    $bgList[] = $this->request->getAssetsUrl() . 'admin/images/login/' . pathinfo($item, PATHINFO_BASENAME);
                }
            }
            $bg = $bgList[rand(0, count($bgList) - 1)];
        }
        
        $this->assign('admin_title', $adminSetting->getTitle());
        $this->assign('is_verify', $adminSetting->isVerify());
        $this->assign('save_login', $adminSetting->getSaveLogin() > 0);
        $this->assign('logo', $adminSetting->getLogoHorizontal());
        $this->assign('bg', $bg);
        $this->assign('verify_url', CaptchaUrl::key('admin_login'));
        $this->assign('copyright', $publicSetting->getCopyright());
        $this->assign('icp_no', $publicSetting->getIcpNo());
        $this->assign('police_no', $publicSetting->getPoliceNo());
        $this->setPageTitle('登录');
        
        return $this->display();
    }
    
    
    /**
     * 退出登录
     */
    public function out()
    {
        if ($this->isLogin()) {
            $this->log()->record(self::LOG_DEFAULT, '退出登录');
        }
        
        AdminUser::outLogin();
        
        return $this->success('退出成功', url('admin_login', [$this->request->getVarRedirectUrl() => $this->request->getRedirectUrl()]));
    }
}