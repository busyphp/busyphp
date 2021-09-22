<?php

namespace BusyPHP\app\admin\controller;

use BusyPHP\App;
use BusyPHP\app\admin\js\AutocompletePlugin;
use BusyPHP\app\admin\js\SelectPickerPlugin;
use BusyPHP\app\admin\js\TablePlugin;
use BusyPHP\app\admin\model\admin\group\AdminGroupInfo;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\app\admin\setting\AdminSetting;
use BusyPHP\app\admin\setting\PublicSetting;
use BusyPHP\app\admin\subscribe\MessageAgencySubscribe;
use BusyPHP\app\admin\subscribe\MessageNoticeSubscribe;
use BusyPHP\helper\util\Str;
use BusyPHP\model\Setting;
use BusyPHP\Controller;
use BusyPHP\helper\file\File;
use BusyPHP\helper\page\Page;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\system\logs\SystemLogs;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\Url;
use Exception;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\exception\HttpResponseException;
use think\facade\Cache;
use think\Response;
use Throwable;

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
     * @var AdminUserInfo
     */
    protected $adminUser;
    
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
     * @var AdminGroupInfo
     */
    protected $adminPermission;
    
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
     * 页面名称
     * @var string
     */
    private $pageTitle;
    
    /**
     * 是否记录操作时长
     * @var bool
     */
    private $saveOperate = true;
    
    /**
     * 请求的插件名称
     * @var bool
     */
    protected $requestPluginName;
    
    /**
     * JS SelectPicker 插件
     * @var SelectPickerPlugin
     */
    protected $pluginSelectPicker;
    
    /**
     * JS Autocomplete 插件
     * @var AutocompletePlugin
     */
    protected $pluginAutocomplete;
    
    /**
     * Js Table 插件
     * @var TablePlugin
     */
    protected $pluginTable;
    
    
    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    
    
    protected function initializeBefore()
    {
        $this->requestPluginName = $this->request->header('Busy-Admin-Plugin', '');
    }
    
    
    /**
     * 初始化
     * @param bool $checkLogin 是否验证登录，默认验证
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function initialize($checkLogin = true)
    {
        $this->publicConfig = config('user.public');
        $this->urlPath      = SystemMenu::getUrlPath();
        
        // 验证登录
        if ($checkLogin) {
            $this->isLogin();
        }
        
        switch ($this->requestPluginName) {
            // 自动处理SelectPicker插件
            case 'SelectPicker':
                $this->pluginSelectPicker = new SelectPickerPlugin();
                if ($result = $this->pluginSelectPicker->build()) {
                    throw new HttpResponseException($this->success($result));
                }
            break;
            
            // 自动处理Autocomplete插件
            case 'Autocomplete':
                $this->pluginAutocomplete = new AutocompletePlugin();
                if ($result = $this->pluginAutocomplete->build()) {
                    throw new HttpResponseException($this->success($result));
                }
            break;
            
            // 自动处理Table插件
            case 'Table':
                $this->pluginTable = new TablePlugin();
                if ($result = $this->pluginTable->build()) {
                    throw new HttpResponseException($this->success($result));
                }
            break;
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
                    $redirectUrl = $this->request->getRedirectUrl();
                } else {
                    $redirectUrl = $this->request->url();
                }
                
                $message     = '请登录后操作';
                $redirectUrl = url('admin_login', [$this->request->getVarRedirectUrl() => $redirectUrl]);
                $isRedirect  = true;
            }
            
            // 抛出错误
            if ($this->isAjax()) {
                $result = $this->error($message, $redirectUrl);
            } else {
                if ($isRedirect) {
                    $result = $this->redirect($redirectUrl);
                } else {
                    $result = $this->error($message, $redirectUrl);
                }
            }
            
            throw new HttpResponseException($result);
        }
    }
    
    
    /**
     * 校验登录
     * @return bool|AdminUserInfo
     */
    protected function checkLogin()
    {
        try {
            $this->adminUser         = AdminUser::init()->checkLogin($this->saveOperate);
            $this->adminUserId       = $this->adminUser->id;
            $this->adminUsername     = $this->adminUser->username;
            $this->adminPermission   = $this->adminUser->group;
            $this->adminPermissionId = $this->adminUser->groupId;
            
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
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function checkPermission()
    {
        // 系统保留分组则允许通行
        if ($this->request->group() != SystemMenu::DEVELOP && false !== stripos(',' . SystemMenu::RETAIN_GROUP . ',', ',' . $this->request->group() . ',')) {
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
        if ($this->adminPermission->isSystem) {
            return true;
        }
        
        // 校验是否包含权限
        if (!in_array($this->urlPath, $this->adminPermission->ruleArray)) {
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
            $url = $this->request->url();
        }
        
        $this->breadcrumb[$name] = $url;
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
    
    
    /**
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function initView() : void
    {
        // 全局URL
        $this->assignUrl('base_root', $this->request->getWebUrl());
        $this->assignUrl('root', $this->request->getAppUrl());
        $this->assignUrl('site', $this->request->domain());
        $this->assignUrl('self', url($this->urlPath));
        $this->assignUrl('login', url('admin_login'));
        $this->assignUrl('group', $this->request->group());
        $this->assignUrl('controller', $this->request->controller(false, true));
        $this->assignUrl('action', $this->request->action());
        
        // 样式路径配置
        $appUrl = $this->request->getWebAssetsUrl() . 'admin/';
        $this->assign('skin', [
            'root'   => $appUrl,
            'css'    => $appUrl . 'css/',
            'js'     => $appUrl . 'js/',
            'images' => $appUrl . 'images/',
            'lib'    => $appUrl . 'lib/'
        ]);
        
        
        $systemInfo = [
            'title'           => AdminSetting::init()->getTitle(),
            'favicon'         => PublicSetting::init()->getFavicon(),
            'logo_icon'       => AdminSetting::init()->getLogoIcon(),
            'logo_horizontal' => AdminSetting::init()->getLogoHorizontal(),
        ];
        
        if ($this->adminUser) {
            // 顶部栏目
            $menuModel    = SystemMenu::init();
            $menuStruct   = $menuModel->getAdminMenu($this->adminPermissionId, true);
            $menuPathList = $menuModel->getPathList();
            
            // 当前激活面板
            $menuActive = $this->request->group();
            if (!in_array($menuActive, $menuStruct->paths)) {
                if ($this->adminPermission->defaultGroup) {
                    $menuActive = Str::studly($this->adminPermission->defaultGroup);
                }
                
                $menuActive = $menuActive ?: $menuStruct->defaultPath;
            }
            
            
            // 左侧栏目
            // 激活选项
            $navActive     = $this->urlPath;
            $navInfo       = $menuPathList[$this->urlPath] ?? null;
            $navParentInfo = null;
            
            
            // 如果是隐藏菜单则创建高亮激活选项
            /*if ($navInfo && $navInfo->isMenu && $navInfo->isHide && $navInfo->higher) {
                $navInfo->action = $navInfo->higher;
                $navInfo->path   = SystemMenu::createUrlPath($navInfo);
                $navParentInfo   = $menuPathList[$navInfo->path] ?? null;
                $navActive       = $navParentInfo->path;
                $this->addBreadcrumb($navParentInfo->name, $navParentInfo->url);
            }
            */
            
            if ($navInfo) {
                $params = [];
                if ($navInfo->params) {
                    $array = explode(',', $navInfo->params);
                    foreach ($array as $key) {
                        $params[$key] = $this->iGet($key);
                    }
                }
                $this->addBreadcrumb($navInfo->name, (string) url($navInfo->path, $params));
                unset($navInfo);
            }
            
            foreach ($this->breadcrumb as $item => $value) {
                if (false === $value) {
                    unset($this->breadcrumb[$item]);
                }
            }
            
            // 系统变量
            $systemInfo['menu_active'] = $menuActive;
            $systemInfo['nav_active']  = $navActive;
            $systemInfo['user']        = $this->adminUser;
            $systemInfo['user_id']     = $this->adminUserId;
            $systemInfo['username']    = $this->adminUsername;
            $systemInfo['breadcrumb']  = $this->breadcrumb;
            $systemInfo['permission']  = $this->adminPermission;
        }
        $this->assign('system', $systemInfo);
        
        
        // 页面名称
        if (!$this->pageTitle) {
            if ($this->breadcrumb) {
                $keyArray        = array_keys($this->breadcrumb);
                $this->pageTitle = end($keyArray);
            }
        }
        $this->assign('page_title', $this->pageTitle . ' - ' . AdminSetting::init()->getTitle());
        $this->assign('panel_title', $this->pageTitle);
    }
    
    
    /**
     * 成功提示
     * @param string|array $message 消息或成功数据
     * @param string|array $jumpUrl 跳转地址或成功数据
     * @param array        $data 成功数据
     * @return Response
     */
    protected function success($message = '', $jumpUrl = '', array $data = [])
    {
        if (is_array($jumpUrl)) {
            $data    = $jumpUrl;
            $jumpUrl = '';
        }
        
        if (is_array($message)) {
            $data    = $message;
            $message = '';
        }
        
        if ($this->isAjax() || $data) {
            return AdminHandle::restResponseSuccess($message, $data, $jumpUrl);
        }
        
        return parent::success($message, $jumpUrl);
    }
    
    
    /**
     * 错误提示
     * @param string|Throwable $message 错误消息或异常对象
     * @param string|Url|int   $jumpUrl 跳转地址或错误代码
     * @param int              $code 错误码
     * @return Response
     */
    protected function error($message, $jumpUrl = '', int $code = 0)
    {
        if ($this->isAjax()) {
            return AdminHandle::restResponseError($message, $jumpUrl, $code);
        }
        
        if ($message instanceof Throwable) {
            $message = $message->getMessage();
        }
        
        return parent::error($message, $jumpUrl);
    }
    
    
    /**
     * 更新缓存
     * @throws DataNotFoundException
     * @throws DbException
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
     * @throws DataNotFoundException
     * @throws DbException
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
            $apps = ['admin' => '后台', $this->app->config->get('app.default_app') => '前台'];
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
<div class="busy-admin-pagination clearfix">
    <span class="page-info">共{$total}条记录&nbsp;&nbsp;{$currentPage}/{$lastPage}页</span>
    <ul class="pagination">%s %s %s</ul>
</div>
HTML;
        $simpleTheme  = <<<HTML
<div class="busy-admin-pagination clearfix">
    <span class="page-info">每页{$listRows}条&nbsp;&nbsp;第{$currentPage}页</span>
    <ul class="pager">%s %s</ul>
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
    
    
    /**
     * 检测权限，供模板使用
     * @param $path
     * @return bool
     * @todo 待开发
     */
    public static function checkAuth($path) : bool
    {
        return true;
    }
    
    
    /**
     * 输出JsTree结构数据
     * @param array $data
     * @return Response
     */
    protected function responseJsTree($data)
    {
        return $this->success('', '', ['data' => $data]);
    }
    
    
    /**
     * 设置页面标题
     * @param string $pageTitle
     */
    protected function setPageTitle(string $pageTitle)
    {
        $this->pageTitle = $pageTitle;
    }
    
    
    /**
     * 设置是否记录操作，以配合保持登录功能
     * @param bool $saveOperateTime
     */
    protected function setSaveOperate(bool $saveOperateTime) : void
    {
        $this->saveOperate = $saveOperateTime;
    }
}