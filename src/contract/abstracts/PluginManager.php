<?php
declare(strict_types = 1);

namespace BusyPHP\contract\abstracts;

use BusyPHP\app\admin\controller\AdminController;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\app\admin\model\system\plugin\SystemPlugin;
use BusyPHP\contract\structs\items\PluginInfo;
use Exception;
use think\db\exception\DbException;
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
     * @var PluginInfo
     */
    protected $info;
    
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
    
    
    protected function initView() : void
    {
        $this->assign('manager_url', $this->managerUrl);
        parent::initView();
    }
    
    
    /**
     * 设置信息
     * @param AdminUserInfo $userInfo
     * @param PluginInfo    $packageInfo
     */
    public function setParams(AdminUserInfo $userInfo, PluginInfo $packageInfo) : void
    {
        $this->adminUser     = $userInfo;
        $this->adminUserId   = $this->adminUser->id;
        $this->adminUsername = $this->adminUser->username;
        $this->info          = $packageInfo;
        $this->managerUrl    = Route::buildUrl('?package=' . $this->info->package)->build();
    }
    
    
    /**
     * 记录安装插件日志
     */
    protected function logInstall()
    {
        $this->log()->record(self::LOG_DEFAULT, '安装插件: ' . $this->info->package);
    }
    
    
    /**
     * 记录卸载插件日志
     */
    protected function logUninstall()
    {
        $this->log()->record(self::LOG_DEFAULT, '卸载插件: ' . $this->info->package);
    }
    
    
    /**
     * 记录配置插件日志
     */
    protected function logSetting()
    {
        $this->log()->record(self::LOG_UPDATE, '配置插件: ' . $this->info->package);
    }
    
    
    /**
     * 执行SQL语句
     * @param string $sql SQL语句
     * @return int
     * @throws DbException
     */
    protected function executeSQL(string $sql) : int
    {
        $model = SystemPlugin::init();
        $sql   = str_replace('#__table_prefix__#', $model->getConnection()->getConfig('prefix'), $sql);
        
        return $model->execute($sql);
    }
    
    
    /**
     * 检测表是否存在
     * @param string $name 表名称，不含前缀
     * @return bool
     * @throws DbException
     */
    protected function hasTable(string $name) : bool
    {
        $model = SystemPlugin::init();
        
        return !empty(SystemPlugin::init()
            ->query("SHOW TABLES LIKE '{$model->getConnection()->getConfig('prefix')}{$name}'"));
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