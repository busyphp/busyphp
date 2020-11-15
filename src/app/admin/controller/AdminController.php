<?php

namespace BusyPHP\app\admin\controller;

use BusyPHP\App;
use BusyPHP\helper\util\Str;
use BusyPHP\model\Setting;
use BusyPHP\exception\VerifyException;
use BusyPHP\Controller;
use BusyPHP\helper\file\File;
use BusyPHP\helper\page\Page;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\system\logs\SystemLogs;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use think\Collection;
use think\Exception;
use think\facade\Cache;
use think\Response;

// 前端收到通知后直接跳转
define('MESSAGE_GOTO', 9999);
// 前端弹出正确的提示后跳转到对应的链接
define('MESSAGE_SUCCESS_GOTO', 9998);
// 前端弹出错误的提示后跳转到对应的链接
define('MESSAGE_WARING_GOTO', 9997);


/**
 * 后台基础类
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2015 busy^life <busy.life@qq.com>
 * @version $Id: 2015-12-20 13:06 AdminAction.php busy^life $
 */
abstract class AdminController extends Controller
{
    //+--------------------------------------
    //| 常量
    //+--------------------------------------
    /** 返回地址SESSION NAME */
    const ADMIN_LOGIN_REDIRECT_URL = 'admin_login_redirect_url';
    
    //+--------------------------------------
    //| 记录
    //+--------------------------------------
    /** 新增操作 */
    const LOG_INSERT = SystemLogs::TYPE_INSERT;
    
    /** 更新操作 */
    const LOG_UPDATE = SystemLogs::TYPE_UPDATE;
    
    /** 批量处理 */
    const LOG_BATCH = SystemLogs::TYPE_BATCH;
    
    /** 默认操作 */
    const LOG_DEFAULT = SystemLogs::TYPE_DEFAULT;
    
    /** 删除操作 */
    const LOG_DELETE = SystemLogs::TYPE_DELETE;
    
    /** 设置操作 */
    const LOG_SET = SystemLogs::TYPE_SET;
    
    //+--------------------------------------
    //| 变量
    //+--------------------------------------
    /**
     * 网站基本数据配置
     * @var array
     */
    protected $publicConfig = [];
    
    /**
     * 管理员数组
     * @var array
     */
    protected $adminUser = [];
    
    /**
     * 管理员ID
     * @var int
     */
    protected $adminUserId = 0;
    
    /**
     * 管理员账号名称
     * @var string
     */
    protected $adminUsername = '';
    
    /**
     * 当前权限组
     * @var array
     */
    protected $adminPermission = [];
    
    /**
     * 当前权限组ID
     * @var int
     */
    protected $adminPermissionId = 0;
    
    /**
     * 当前URL PATH
     * @var string
     */
    protected $urlPath = '';
    
    /**
     * 当前URL Pattern
     * @var string
     */
    protected $urlPattern = '';
    
    //+--------------------------------------
    //| 私有
    //+--------------------------------------
    /**
     * 错误的权限消息
     * @var string
     */
    private $permissionError = '';
    
    /**
     * 面包屑数组
     * @var array
     */
    private $breadcrumb = [];
    
    /**
     * 自定义左侧菜单高亮栏目路径
     * @var string
     */
    private $layoutLeftNavActive = '';
    
    
    /**
     * 在控制器中初始化基本事物
     * @param bool $checkLogin 是否验证登录，默认验证
     */
    public function initialize($checkLogin = true)
    {
        $this->publicConfig = config('user.public');
        $this->urlPattern   = SystemMenu::getUrlPattern();
        $this->urlPath      = SystemMenu::getUrlPath();
        
        // 验证登录
        if ($checkLogin) {
            $this->isLogin();
        }
    }
    
    
    /**
     * 验证登录
     */
    protected function isLogin()
    {
        if (!$this->checkLogin()) {
            // 权限错误
            if ($this->permissionError != '') {
                $message     = $this->permissionError;
                $redirectUrl = url('Common.Index/index');
                $isRedirect  = false;
            } else {
                AdminUser::outLogin();
                
                // 记录返回地址
                // POST/AJAX 记录来源操作地址为返回地址
                if ($this->isPost() || $this->isAjax()) {
                    session(self::ADMIN_LOGIN_REDIRECT_URL, $this->request->getRedirectUrl());
                } else {
                    session(self::ADMIN_LOGIN_REDIRECT_URL, URL_SELF);
                }
                
                $message     = '请登录后操作';
                $redirectUrl = url('admin_login');
                $isRedirect  = true;
            }
            
            // 抛出错误
            if ($this->isAjax()) {
                $result = $this->error($message, $redirectUrl, MESSAGE_WARING_GOTO);
            } else {
                if ($isRedirect) {
                    $result = $this->redirect($redirectUrl);
                } else {
                    $result = $this->error($message, $redirectUrl);
                }
            }
            
            $result->send();
            exit;
        }
    }
    
    
    /**
     * 校验登录
     * @return bool|array
     */
    protected function checkLogin()
    {
        try {
            $this->adminUser         = AdminUser::init()->checkLogin();
            $this->adminUserId       = $this->adminUser['id'];
            $this->adminUsername     = $this->adminUser['username'];
            $this->adminPermission   = $this->adminUser['group'];
            $this->adminPermissionId = $this->adminUser['group']['id'];
            
            // 权限校验
            if (!$this->checkPermission()) {
                $this->permissionError = '您没有权限操作该内容';
                throw new Exception($this->permissionError);
            }
            
            return $this->adminUser;
        } catch (Exception $e) {
            return false;
        }
    }
    
    
    /**
     * 验证权限
     * @return bool
     */
    protected function checkPermission()
    {
        // 系统保留分组则允许通行
        if (GROUP_NAME != SystemMenu::DEVELOP && false !== stripos(',' . SystemMenu::RETAIN_GROUP . ',', ',' . GROUP_NAME . ',')) {
            return true;
        }
        
        // 不在系统范围内的权限则允许通行
        if (!in_array($this->urlPath, array_keys(SystemMenu::init()->getPathList()))) {
            return true;
        }
        
        // 权限为空
        if (!$this->adminPermission) {
            return false;
        }
        
        // 系统权限组允许任何权限
        if ($this->adminPermission['is_system']) {
            return true;
        }
        
        // 校验是否包含权限
        if (!in_array($this->urlPath, $this->adminPermission['rule_array'])) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * 添加面包屑导航地址
     * @param string      $name 导航名称
     * @param string|bool $url 导航链接，输入null则删除对应的导航名称，输入true代表当前网址，输入false删除该面包屑
     */
    protected function addBreadcrumb($name, $url = '')
    {
        if (isset($this->breadcrumb[$name]) && false === ($this->breadcrumb[$name])) {
            return;
        }
        
        if ($url === true) {
            $url = URL_SELF;
        }
        
        $this->breadcrumb[$name] = $url;
    }
    
    
    /**
     * 设置左侧栏目高亮路径
     * @param string $path 路径
     */
    protected function setLayoutLeftNavActive($path)
    {
        $this->layoutLeftNavActive = $path;
    }
    
    
    /**
     * 记录操作记录
     * @param string $name 操作名称
     * @param mixed  $value 操作内容
     * @param int    $type 操作类型
     */
    protected function log($name, $value = '', $type = SystemLogs::TYPE_DEFAULT)
    {
        SystemLogs::init()->setUser($this->adminUserId, $this->adminUsername)->insertData($name, $value, $type);
    }
    
    
    protected function initView() : void
    {
        // 全局URL
        $this->assignUrl('base_root', URL_ROOT);
        $this->assignUrl('root', URL_APP);
        $this->assignUrl('site', URL_DOMAIN);
        $this->assignUrl('self', url($this->urlPath));
        $this->assignUrl('login', url('admin_login'));
        
        // 样式路径配置
        $appUrl = URL_ASSETS . 'admin/';
        $this->assign('skin', [
            'root'   => $appUrl,
            'css'    => $appUrl . 'css/',
            'js'     => $appUrl . 'js/',
            'images' => $appUrl . 'images/',
            'lib'    => $appUrl . 'lib/'
        ]);
        
        
        // 顶部栏目
        $menuModel    = SystemMenu::init();
        $menuArray    = $menuModel->getAdminMenu($this->adminPermissionId);
        $menuPathList = $menuModel->getPathList();
        
        // 激活选项
        $menuActive = GROUP_NAME;
        $keys       = isset($menuArray['keys']) ? $menuArray['keys'] : [];
        if (!in_array($menuActive, $keys)) {
            if ($this->adminPermission['default_group']) {
                $menuActive = ucfirst(Str::camel($this->adminPermission['default_group']));
            }
            
            $menuActive = $menuActive ?: (isset($menuArray['default']) ? $menuArray['default'] : '');
        }
        
        
        // 左侧栏目
        // 激活选项
        $navActive     = $this->urlPath;
        $navInfo       = isset($menuPathList[$this->urlPath]) ? $menuPathList[$this->urlPath] : [];
        $navParentInfo = [];
        
        // 如果是三级隐藏菜单则创建高亮激活选项
        if ($navInfo && $navInfo['type'] == 2 && intval($navInfo['is_show']) == 0 && $navInfo['higher']) {
            $navInfo['action'] = $navInfo['higher'];
            $parseInfo         = SystemMenu::parseInfo($navInfo);
            $navParentInfo     = $menuPathList[$parseInfo['path']];
            $navActive         = $navParentInfo['path'];
        }
        
        // 面包屑组合
        if ($navParentInfo) {
            $this->addBreadcrumb($navParentInfo['name'], $navParentInfo['url']);
            unset($navParentInfo);
        }
        if ($navInfo) {
            $params = [];
            if ($navInfo['params']) {
                $array = explode(',', $navInfo['params']);
                foreach ($array as $key) {
                    $params[$key] = trim(urldecode($_GET[$key]));
                }
            }
            $this->addBreadcrumb($navInfo['name'], (string) url($navInfo['path'], $params));
            unset($navInfo);
        }
        
        foreach ($this->breadcrumb as $item => $value) {
            if (false === $value) {
                unset($this->breadcrumb[$item]);
            }
        }
        
        
        // 系统变量
        $this->assign('system', [
            'menu'          => isset($menuArray['menu_list']) ? $menuArray['menu_list'] : [],
            'menu_active'   => $menuActive,
            'nav'           => $menuModel->getAdminNav($this->adminPermissionId),
            'nav_active'    => $this->layoutLeftNavActive ? $this->layoutLeftNavActive : $navActive,
            'user'          => $this->adminUser,
            'user_id'       => $this->adminUserId,
            'username'      => $this->adminUsername,
            'url_path'      => $this->urlPath,
            'url_pattern'   => $this->urlPattern,
            'public_config' => $this->publicConfig,
            'breadcrumb'    => $this->breadcrumb,
            'permission'    => $this->adminPermission
        ]);
        
        // 页面名称
        $panelTitle = '';
        if ($this->breadcrumb) {
            $keyArray   = array_keys($this->breadcrumb);
            $panelTitle = end($keyArray);
        }
        $pageTitle = $this->publicConfig['title'];
        if ($panelTitle) {
            $pageTitle = $panelTitle . ' - ' . $this->publicConfig['title'];
        }
        $this->assign('page_title', $pageTitle);
        $this->assign('panel_title', $panelTitle);
    }
    
    
    /**
     * 成功提示
     * @param mixed  $message 消息
     * @param string $jumpUrl 跳转地址
     * @param string $data 成功数据
     * @return Response
     */
    protected function success($message, $jumpUrl = '', $data = '')
    {
        if ($this->isAjax()) {
            return $this->json($this->parseAjaxReturn([
                'info'   => $this->parseMessage($message),
                'status' => true,
                'url'    => $jumpUrl,
                'data'   => $data
            ]));
        } else {
            return parent::success($message, $jumpUrl);
        }
    }
    
    
    /**
     * 错误提示
     * @param mixed  $message 错误消息
     * @param string $jumpUrl 返回地址
     * @param mixed  $data 错误数据
     * @return Response
     */
    protected function error($message, $jumpUrl = '', $data = '')
    {
        if ($message instanceof VerifyException) {
            return $this->fieldError($message);
        }
        
        if ($this->isAjax()) {
            return $this->json($this->parseAjaxReturn([
                'info'   => $this->parseMessage($message),
                'status' => false,
                'url'    => $jumpUrl,
                'data'   => $data
            ]));
        } else {
            return parent::error($message, $jumpUrl);
        }
    }
    
    
    /**
     * 字段错误
     * @param string $message 错误消息
     * @param string $name 字段名称
     * @return Response
     */
    protected function fieldError($message, $name = '')
    {
        if ($this->isAjax()) {
            if ($message instanceof VerifyException) {
                $name    = $message->getField();
                $message = $message->getMessage();
            }
            
            return $this->json($this->parseAjaxReturn([
                'info'   => $this->parseMessage($message),
                'status' => false,
                'url'    => '',
                'data'   => [],
                'name'   => $name
            ]));
        } else {
            return parent::error($message);
        }
    }
    
    
    /**
     * 解析ajax返回内容
     * @param array $return
     * @return array
     */
    private function parseAjaxReturn(array $return = []) : array
    {
        if ($this->app->isDebug()) {
            $return['trace'] = trace();
        }
        
        return $return;
    }
    
    
    /**
     * 更新缓存
     */
    protected function updateCache()
    {
        // 更新菜单缓存
        SystemMenu::init()->updateCache();
        
        // 更新用户组缓存
        AdminGroup::init()->updateCache();
        
        // 生成系统配置文件
        Setting::createConfig();
    }
    
    
    /**
     * 清空缓存
     * @param bool|array $names
     */
    protected function clearCache($names)
    {
        $apps = $this->getApps();
        if (is_bool($names) && true === $names) {
            $names = [];
            foreach ($apps as $value => $name) {
                $names[$value] = 1;
            }
        }
        
        foreach ($apps as $value => $name) {
            if (!$names[$value]) {
                continue;
            }
            
            File::deleteDir(App::runtimePath($value));
        }
        
        
        // 清理系统缓存
        File::deleteDir(App::runtimeCachePath());
        // 清理临时配置
        File::deleteDir(App::runtimeConfigPath());
        // 清理临时日志
        File::deleteDir(App::runtimePath('log'));
        // 清理基本缓存
        Cache::clear();
        
        // 生成配置
        $this->updateCache();
    }
    
    
    /**
     * 获取应用名称集合
     */
    protected function getApps()
    {
        static $apps;
        
        if (!isset($apps)) {
            $path = base_path();
            $apps = ['admin' => '后台', 'home' => '前台', 'crontab' => '任务'];
            $keys = array_keys($apps);
            foreach (scandir($path) as $value) {
                if (!is_dir($app = $path . $value) || $value === '.' || $value === '..' || in_array($value, $keys)) {
                    continue;
                }
                
                $name   = $value;
                $readme = $app . DIRECTORY_SEPARATOR . 'README.md';
                if (is_file($readme)) {
                    $name = file($readme);
                    $name = str_replace(['#', '*', ' ', '>', '~', '='], '', trim($name[0]));
                }
                
                $apps[$value] = $name;
            }
        }
        
        return $apps;
    }
    
    
    /**
     * 后台分页
     * @param array|Collection $items
     * @param int              $listRows 每页显示多少条，默认20条
     * @param int              $currentPage 当前页码
     * @param int|null         $total 总条数
     * @param bool             $simple 简洁模式
     * @return Page
     */
    protected function page($items, int $listRows, int $currentPage = 1, int $total = null, bool $simple = false)
    {
        $page         = Page::init($items, $listRows, $currentPage, $total, $simple);
        $lastPage     = $simple ? 0 : $page->lastPage();
        $defaultTheme = <<<HTML
<div class="listpage">
    <span class="page-info">共{$total}条记录&nbsp;&nbsp;{$currentPage}/{$lastPage}页</span>
    <ul class="pagination">%s %s %s</ul>
    <div class="clearfix"></div>
</div>
HTML;
        $simpleTheme  = <<<HTML
<div class="listpage">
    <span class="page-info">每页{$listRows}条&nbsp;&nbsp;第{$currentPage}页</span>
    <ul class="pager">%s %s</ul>
    <div class="clearfix"></div>
</div>
HTML;
        $page->setTheme([
            'default' => $defaultTheme,
            'simple'  => $simpleTheme
        ]);
        
        $page->setTemplate([
            'prev'     => '<li class="prev %s"><a href="%s">上一页</a></li>',
            'next'     => '<li class="next %s"><a href="%s">下一页</a></li>',
            'active'   => '<li class="active"><span>%s</span></li>',
            'disabled' => '<li class="disabled"><span>%s</span></li>',
            'link'     => '<li><a href="%s">%s</a></li>',
        ]);
        $page->setForceRender(true);
        
        return $page;
    }
    
    
    protected function parseMessage($message)
    {
        return str_replace(PHP_EOL, '<br />', parent::parseMessage($message));
    }
}