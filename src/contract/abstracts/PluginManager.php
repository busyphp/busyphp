<?php
declare(strict_types = 1);

namespace BusyPHP\contract\abstracts;

use BusyPHP\app\admin\controller\AdminController;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\contract\structs\items\PackageInfo;
use Exception;
use think\facade\Route;
use think\Response;

/**
 * 插件管理接口
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/31 下午下午4:09 PluginManager.php $
 */
abstract class PluginManager extends AdminController
{
    /**
     * 包信息
     * @var PackageInfo
     */
    protected $packageInfo;
    
    /**
     * 当前URL
     * @var string
     */
    protected $managerUrl;
    
    
    protected function initialize($checkLogin = true)
    {
        // 设置模板路径
        $viewPath = str_replace('\\', '/', $this->viewPath());
        $viewPath = rtrim($viewPath, '/') . '/';
        $viewPath = str_replace('/', DIRECTORY_SEPARATOR, $viewPath);
        $this->app->config->set(['view_path' => $viewPath], 'view');
        
        parent::initialize($checkLogin);
    }
    
    
    protected function checkLogin()
    {
        // 屏蔽验证登录
    }
    
    
    protected function isLogin() : ?AdminUserInfo
    {
        // 强制返回用户信息
        return $this->adminUser;
    }
    
    
    /**
     * 设置信息
     * @param AdminUserInfo $userInfo
     * @param PackageInfo   $packageInfo
     */
    public function setParams(AdminUserInfo $userInfo, PackageInfo $packageInfo) : void
    {
        $this->adminUser     = $userInfo;
        $this->adminUserId   = $this->adminUser->id;
        $this->adminUsername = $this->adminUser->username;
        $this->packageInfo   = $packageInfo;
        $this->managerUrl    = Route::buildUrl('?package=' . $this->packageInfo->package)->build();
    }
    
    
    protected function initView() : void
    {
        $this->assign('manager_url', $this->managerUrl);
        parent::initView();
    }
    
    
    /**
     * 返回模板路径
     * @return string
     */
    abstract protected function viewPath() : string;
    
    
    /**
     * 安装插件
     * @return Response
     * @throws Exception
     */
    abstract public function install() : Response;
    
    
    /**
     * 卸载插件
     * @return Response
     * @return Exception
     */
    abstract public function uninstall() : Response;
    
    
    /**
     * 设置插件
     * @return Response
     * @return Exception
     */
    abstract public function setting() : Response;
}