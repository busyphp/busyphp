<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\App;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\admin\user\AdminUserEventLoginPrepare;
use BusyPHP\app\admin\model\system\token\SystemToken;
use BusyPHP\app\admin\setting\PublicSetting;
use BusyPHP\exception\VerifyException;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\setting\AdminSetting;
use BusyPHP\facade\Captcha;
use RuntimeException;
use think\facade\Session;
use think\Response;
use think\response\Redirect;
use Throwable;

/**
 * 登录
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/26 下午下午2:25 PassportController.php $
 */
class PassportController extends InsideController
{
    const SESSION_VERIFY_STATUS_KEY = 'admin_login_need_verify';
    
    
    protected function initialize($checkLogin = false)
    {
        parent::initialize($checkLogin);
    }
    
    
    public function index() : Redirect
    {
        return $this->redirect(url('admin_login'));
    }
    
    
    /**
     * 登录
     * @return Response
     * @throws Throwable
     */
    public function login() : Response
    {
        if ($this->isPost()) {
            return $this->doLogin();
        }
        
        return $this->renderLogin();
    }
    
    
    /**
     * 执行登录
     * @return Response
     * @throws Throwable
     */
    protected function doLogin() : Response
    {
        $needCheckVerify = Session::get(self::SESSION_VERIFY_STATUS_KEY) ?: false;
        $username        = $this->post('username/s', 'trim');
        $password        = $this->post('password/s', 'trim');
        $verify          = $this->post('verify/s', 'trim');
        $saveLogin       = $this->post('save_login/b');
        
        try {
            $model     = AdminUser::init();
            $tokenInfo = $model
                ->listen(AdminUserEventLoginPrepare::class, function() use ($verify, $needCheckVerify) {
                    if ($needCheckVerify && AdminSetting::instance()->isVerify()) {
                        try {
                            Captcha::id('admin_login')->check($verify, false, true);
                        } catch (VerifyException $e) {
                            throw new VerifyException($e->getMessage(), 'verify');
                        }
                    }
                })
                ->login($username, $password, SystemToken::class()::DEFAULT_TYPE);
            
            // 保存登录条件
            $user = $model->getInfo($tokenInfo->userId);
            $this->handle->saveLogin($tokenInfo, $user, $saveLogin);
            
            // 记录操作日志
            $this->adminUserId   = $user->id;
            $this->adminUsername = $user->username;
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
            
            Session::delete(self::SESSION_VERIFY_STATUS_KEY);
            
            return $this->success('登录成功', $path ? $redirectUrl : $this->request->getAppUrl());
        } catch (VerifyException $e) {
            $this->log()->setUser(0, $username)->record(self::LOG_DEFAULT, '登录错误', $e->getMessage());
            
            Session::set(self::SESSION_VERIFY_STATUS_KEY, true);
            
            switch ($e->getField()) {
                case 'disabled':
                case 'locked':
                    throw new RuntimeException($e->getMessage(), -1);
            }
            
            throw $e;
        }
    }
    
    
    /**
     * 渲染登录页面
     * @return Response
     */
    protected function renderLogin() : Response
    {
        $needCheckVerify = Session::get(self::SESSION_VERIFY_STATUS_KEY) ?: false;
        $adminSetting    = AdminSetting::instance();
        $publicSetting   = PublicSetting::instance();
        $loginBg         = $adminSetting->getLoginBg();
        if ($loginBg && is_file(App::urlToPath($loginBg))) {
            $bg = $loginBg;
        } else {
            $bgList = [];
            foreach (glob($this->app->getPublicPath('assets/admin/images/login/*.*')) as $item) {
                $bgList[] = $this->request->getAssetsUrl() . 'admin/images/login/' . pathinfo($item, PATHINFO_BASENAME);
            }
            if (!$bgList) {
                foreach (glob(__DIR__ . '/../../../../assets/admin/images/login/*.*') as $item) {
                    $bgList[] = $this->request->getAssetsUrl() . 'admin/images/login/' . pathinfo($item, PATHINFO_BASENAME);
                }
            }
            $bg = $bgList[rand(0, count($bgList) - 1)];
        }
        
        $this->assign('admin_title', $adminSetting->getTitle());
        $this->assign('is_verify', $adminSetting->isVerify());
        $this->assign('show_verify', $needCheckVerify ? 1 : 0);
        $this->assign('save_login', $adminSetting->getSaveLogin() > 0);
        $this->assign('logo', $adminSetting->getLogoHorizontal());
        $this->assign('bg', $bg);
        $this->assign('verify_url', Captcha::id('admin_login')->url());
        $this->assign('copyright', $publicSetting->getCopyright());
        $this->assign('icp_no', $publicSetting->getIcpNo());
        $this->assign('police_no', $publicSetting->getPoliceNo());
        $this->setPageTitle('登录');
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 退出登录
     */
    public function out() : Response
    {
        if ($this->isLogin()) {
            $this->log()->record(self::LOG_DEFAULT, '退出登录');
        }
        
        $this->handle->outLogin();
        
        return $this->success('退出成功', url('admin_login', [$this->request->getVarRedirectUrl() => $this->request->getRedirectUrl()]));
    }
}