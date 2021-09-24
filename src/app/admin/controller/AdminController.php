<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\controller;

use BusyPHP\App;
use BusyPHP\app\admin\js\AutocompletePlugin;
use BusyPHP\app\admin\js\SelectPickerPlugin;
use BusyPHP\app\admin\js\TablePlugin;
use BusyPHP\app\admin\js\TreePlugin;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\app\admin\setting\AdminSetting;
use BusyPHP\app\admin\setting\PublicSetting;
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
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/24 下午下午4:39 AdminController.php $
 */
abstract class AdminController extends Controller
{
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
     * 管理员信息
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
    
    /**
     * Js Tree 插件
     * @var TreePlugin
     */
    protected $pluginTree;
    
    //+--------------------------------------
    //| 私有
    //+--------------------------------------
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
        
        // 验证登录
        if ($checkLogin) {
            $this->checkLogin();
        }
        
        // 自动处理
        switch ($this->requestPluginName) {
            // SelectPicker插件
            case 'SelectPicker':
                $this->pluginSelectPicker = new SelectPickerPlugin();
                $result                   = $this->pluginSelectPicker->build();
            break;
            
            // Autocomplete插件
            case 'Autocomplete':
                $this->pluginAutocomplete = new AutocompletePlugin();
                $result                   = $this->pluginAutocomplete->build();
            break;
            
            // Table插件
            case 'Table':
                $this->pluginTable = new TablePlugin();
                $result            = $this->pluginTable->build();
            break;
            
            // Tree插件
            case 'Tree':
                $this->pluginTree = new TreePlugin();
                $result           = $this->pluginTree->build();
            break;
            default:
                $result = null;
        }
        
        if ($result) {
            throw new HttpResponseException($this->success($result));
        }
    }
    
    
    /**
     * 验证登录
     */
    protected function checkLogin()
    {
        // 验证登录
        if (!$this->getLoginUserInfo()) {
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
            
            if ($this->isAjax()) {
                $result = $this->error($message, $redirectUrl);
            } else {
                $result = $this->redirect($redirectUrl);
            }
            
            throw new HttpResponseException($result);
        }
        
        // 权限验证
        if (!AdminGroup::checkPermission($this->adminUser)) {
            throw new HttpResponseException($this->error('您无权限操作', $this->request->root(), 0));
        }
    }
    
    
    /**
     * 获取登录用户信息
     * @return AdminUserInfo
     */
    protected function getLoginUserInfo() : ?AdminUserInfo
    {
        try {
            $this->adminUser     = AdminUser::init()->checkLogin($this->saveOperate);
            $this->adminUserId   = $this->adminUser->id;
            $this->adminUsername = $this->adminUser->username;
        } catch (Exception $e) {
            return null;
        }
        
        return $this->adminUser;
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
     * 初始化View注入参数
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function initView() : void
    {
        // 全局URL
        $this->assign('url', [
            'root'       => $this->request->getWebUrl(),
            'app'        => $this->request->getAppUrl(),
            'self'       => url(),
            'controller' => $this->request->controller(),
            'action'     => $this->request->action(),
        ]);
        
        // 计算面包屑
        $menuModel   = SystemMenu::init();
        $hashList    = $menuModel->getHashList();
        $breadcrumb  = [];
        $currentMenu = $hashList[md5($this->request->getPath())] ?? null;
        if ($currentMenu) {
            $idList     = $menuModel->getIdList();
            $parentList = $menuModel->getIdParens();
            $root       = $this->request->root();
            foreach ($parentList[$currentMenu->id] ?? [] as $id) {
                if ($item = ($idList[$id] ?? null)) {
                    $breadcrumb[] = [
                        'name' => $item->name,
                        'url'  => $item->url ? $root . $item->url : '',
                    ];
                }
            }
            krsort($breadcrumb);
            $breadcrumb = array_values($breadcrumb);
            
            // 最终页面
            $query = [];
            foreach ($currentMenu->paramList as $item) {
                $query[$item] = $this->request->get($item);
            }
            
            $breadcrumb[] = [
                'name' => $currentMenu->name,
                'url'  => $root . $currentMenu->url . ($query ? '?' . http_build_query($query) : '')
            ];
        }
        
        // 系统信息
        $this->assign('system', [
            'title'           => AdminSetting::init()->getTitle(),
            'favicon'         => PublicSetting::init()->getFavicon(),
            'logo_icon'       => AdminSetting::init()->getLogoIcon(),
            'logo_horizontal' => AdminSetting::init()->getLogoHorizontal(),
            'user'            => $this->adminUser ?? [],
            'breadcrumb'      => $breadcrumb
        ]);
        
        // 页面名称
        if (!$this->pageTitle && $currentMenu) {
            $this->pageTitle = $currentMenu->name;
        }
        $this->assign('page_title', $this->pageTitle . ' - ' . AdminSetting::init()->getTitle());
        $this->assign('panel_title', $this->pageTitle);
        
        // 样式路径配置
        $skinRoot = $this->request->getAssetsUrl() . 'admin/';
        $this->assign('skin', [
            'root'   => $skinRoot,
            'css'    => $skinRoot . 'css/',
            'js'     => $skinRoot . 'js/',
            'images' => $skinRoot . 'images/',
            'lib'    => $skinRoot . 'lib/'
        ]);
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
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function clearCache()
    {
        $path = $this->app->getBasePath();
        foreach (scandir($path) as $value) {
            if (!is_dir($path . $value) || $value === '.' || $value === '..') {
                continue;
            }
            
            File::deleteDir(App::runtimePath($value . DIRECTORY_SEPARATOR . 'temp'));
            File::deleteDir(App::runtimePath($value . DIRECTORY_SEPARATOR . 'cache'));
        }
        
        // 清理系统缓存
        File::deleteDir(App::runtimeCachePath());
        // 清理临时配置
        File::deleteDir(App::runtimeConfigPath());
        // 清理基本缓存
        Cache::clear();
        
        // 生成配置
        $this->updateCache();
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