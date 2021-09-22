<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\App;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\setting\PublicSetting;
use BusyPHP\app\general\controller\VerifyController;
use BusyPHP\exception\VerifyException;
use BusyPHP\exception\AppException;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\setting\AdminSetting;
use think\db\exception\DbException;
use think\Response;

/**
 * 登录
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午10:51 上午 Passport.php $
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
     * @throws VerifyException
     * @throws DbException
     */
    public function login()
    {
        if ($this->isPost()) {
            $username  = $this->request->post('username', '', 'trim');
            $password  = $this->request->post('password', '', 'trim');
            $verify    = $this->request->post('verify', '', 'trim');
            $saveLogin = $this->request->post('save_login', 0, 'intval') > 0;
            
            try {
                $adminModel = AdminUser::init();
                $adminModel->setCallback(AdminUser::CALLBACK_PROCESS, function() use ($verify) {
                    if (AdminSetting::init()->isVerify()) {
                        try {
                            VerifyController::check('admin_login', $verify);
                            VerifyController::clear('admin_login');
                        } catch (AppException $e) {
                            throw new VerifyException($e->getMessage(), 'verify');
                        }
                    }
                });
                
                $user                = $adminModel->login($username, $password, $saveLogin);
                $this->adminUserId   = $user['id'];
                $this->adminUsername = $user['username'];
                $this->log('登录成功');
                
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
                if ($e->getCode()) {
                    $this->log('密码输入错误', [
                        'username' => $username,
                        'password' => $password,
                    ]);
                }
                
                throw $e;
            }
        }
        
        $list  = glob(App::getPublicPath('assets/admin/images/login') . '*.*');
        $array = [];
        foreach ($list as $item) {
            $array[] = $this->request->getWebAssetsUrl() . 'admin/images/login/' . pathinfo($item, PATHINFO_BASENAME);
        }
        
        $adminSetting  = AdminSetting::init();
        $publicSetting = PublicSetting::init();
        $this->assign('admin_title', $adminSetting->getTitle());
        $this->assign('is_verify', $adminSetting->isVerify());
        $this->assign('save_login', $adminSetting->getSaveLogin() > 0);
        $this->assign('logo', $adminSetting->getLogoHorizontal());
        $this->assign('bg', $array[rand(0, count($array) - 1)]);
        $this->assign('verify_url', VerifyController::url('admin_login'));
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
        $this->log('退出登录');
        AdminUser::outLogin();
        
        return $this->success('退出成功', url('admin_login', [$this->request->getVarRedirectUrl() => $this->request->getRedirectUrl()]));
    }
}