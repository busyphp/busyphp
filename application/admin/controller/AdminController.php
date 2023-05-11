<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\controller;

use BusyPHP\App;
use BusyPHP\app\admin\component\common\Task;
use BusyPHP\app\admin\event\panel\AdminPanelClearCacheEvent;
use BusyPHP\app\admin\event\panel\AdminPanelUpdateCacheEvent;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\app\admin\model\system\config\SystemConfig;
use BusyPHP\app\admin\model\system\logs\SystemLogs;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\model\system\task\SystemTaskInterface;
use BusyPHP\Controller;
use BusyPHP\helper\CacheHelper;
use BusyPHP\helper\FileHelper;
use FilesystemIterator;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;
use ScssPhp\ScssPhp\OutputStyle;
use SplFileInfo;
use think\exception\HttpResponseException;
use think\facade\Event;
use think\facade\Route;
use think\Response;
use think\route\Url;
use Throwable;

/**
 * 后台基础类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/24 下午下午4:39 AdminController.php $
 * @property AdminHandle $handle
 */
abstract class AdminController extends Controller
{
    //+--------------------------------------
    //| 记录
    //+--------------------------------------
    /** 新增操作 */
    public const LOG_INSERT = SystemLogs::TYPE_INSERT;
    
    /** 更新操作 */
    public const LOG_UPDATE = SystemLogs::TYPE_UPDATE;
    
    /** 删除操作 */
    public const LOG_DELETE = SystemLogs::TYPE_DELETE;
    
    /** 默认操作 */
    public const LOG_DEFAULT = SystemLogs::TYPE_DEFAULT;
    
    //+--------------------------------------
    //| 变量
    //+--------------------------------------
    /**
     * 管理员信息
     * @var AdminUserField|null
     */
    protected ?AdminUserField $adminUser = null;
    
    /**
     * 管理员ID
     * @var int
     */
    protected int $adminUserId = 0;
    
    /**
     * 管理员账号名称
     * @var string
     */
    protected string $adminUsername = '';
    
    //+--------------------------------------
    //| 私有
    //+--------------------------------------
    /**
     * 页面名称
     * @var string
     */
    private string $pageTitle = '';
    
    /**
     * 是否记录操作时长
     * @var bool
     */
    private bool $saveOperate = true;
    
    
    public function __construct(App $app)
    {
        // 注入任务相关处理
        Task::maker(Task::MAKER_LOG, function(int $type, string $name, string $result) {
            $this->log()->record($type, $name, $result);
        });
        Task::maker(Task::MAKER_DOWNLOAD_URL, function(string $id, string $filename, string $mimetype) {
            return Route::buildUrl('system_task/download', ['id' => $id, 'name' => $filename, 'mimetype' => $mimetype]);
        });
        
        parent::__construct($app);
    }
    
    
    /**
     * 初始化
     * @param bool $checkLogin 是否验证登录，默认验证
     */
    protected function initialize(bool $checkLogin = true)
    {
        // 排除登录校验
        if (!SystemMenu::isExcludeLogin(static::class, $this->request->action()) && $checkLogin) {
            $this->checkLogin();
        }
    }
    
    
    /**
     * 验证登录
     */
    protected function checkLogin()
    {
        // 验证登录
        if (!$this->isLogin()) {
            AdminUser::init()->outLogin();
            
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
                $result = $this->error($message, $redirectUrl, AdminHandle::CODE_NEED_LOGIN);
            } else {
                $result = $this->redirect($redirectUrl);
            }
            
            throw new HttpResponseException($result);
        }
        
        // 权限验证
        if (!AdminGroup::class()::checkPermission($this->adminUser)) {
            throw new HttpResponseException($this->error('无权限操作', $this->request->getAppUrl(), 0));
        }
    }
    
    
    /**
     * 获取登录用户信息
     * @return AdminUserField|null
     */
    protected function isLogin() : ?AdminUserField
    {
        try {
            $this->adminUser     = AdminUser::init()->checkLogin($this->saveOperate);
            $this->adminUserId   = $this->adminUser->id;
            $this->adminUsername = $this->adminUser->username;
            
            return $this->adminUser;
        } catch (Throwable) {
            return null;
        }
    }
    
    
    /**
     * 记录操作记录
     * @param string|int $type 日志分类
     * @param mixed      $value 日志业务参数
     * @return SystemLogs
     */
    protected function log($type = '', string $value = '') : SystemLogs
    {
        return SystemLogs::init()->setUser($this->adminUserId, $this->adminUsername)->setClass($type, $value);
    }
    
    
    /**
     * 创建异步任务操作
     * @param class-string<SystemTaskInterface> $class 任务处理类
     * @param mixed                             $data 任务处理数据
     * @param string                            $title 任务标题
     * @param int                               $later 延迟执行秒数
     * @param int                               $loop 循环间隔秒数
     * @return Task
     */
    protected function task(string $class, mixed $data = null, string $title = '', int $later = 0, int $loop = 0) : Task
    {
        return Task::init($class, $data, $title, $later, $loop);
    }
    
    
    protected function viewFilter(string $content) : string
    {
        // 解析scss
        $compiler = new Compiler($this->app->config->get('app.admin.scssphp.cache_options'));
        $compiler->setOutputStyle($this->app->isDebug() ? OutputStyle::EXPANDED : OutputStyle::COMPRESSED);
        $content = preg_replace_callback('#<style\stype=["\']text/scss[\'"]>(.*?)</style>#is', function($match) use ($compiler) {
            $scss = trim($match[1]);
            $key  = md5($scss);
            $css  = CacheHelper::get(self::class, $key);
            if (!$css) {
                try {
                    $css = $compiler->compileString($match[1])->getCss();
                    CacheHelper::set(self::class, $key, $css, 0);
                } catch (Throwable|SassException $e) {
                    $css = '/**' . PHP_EOL;
                    $css .= $e->getMessage();
                    $css .= PHP_EOL . '*/';
                }
            }
            
            return '<style>' . $css . '</style>';
        }, $content);
        
        return parent::viewFilter($content);
    }
    
    
    /**
     * 初始化View注入参数
     */
    protected function initView() : void
    {
        $this->assign(AdminHandle::templateBaseData($this->pageTitle, $this->adminUser));
    }
    
    
    /**
     * 成功提示
     * @param string|array $message 消息或成功数据
     * @param string|array $jumpUrl 跳转地址或成功数据
     * @param array        $data 成功数据
     * @return Response
     */
    protected function success($message = '', $jumpUrl = '', array $data = []) : Response
    {
        if (is_array($jumpUrl)) {
            $data    = $jumpUrl;
            $jumpUrl = '';
        }
        
        if (is_array($message)) {
            $data    = $message;
            $message = '';
        }
        
        if (($this->isAjax() || $data) && !AdminHandle::isSinglePage()) {
            return $this->handle->jsonSuccess($message, $data, $jumpUrl);
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
    protected function error($message, $jumpUrl = '', int $code = 0) : Response
    {
        if ($this->isAjax()) {
            return $this->handle->jsonError($message, $jumpUrl, $code);
        }
        
        return parent::error($message, $jumpUrl);
    }
    
    
    /**
     * @inheritDoc
     */
    protected function dispatchJump($message, bool $status = true, $jumpUrl = '') : Response
    {
        // 覆盖模板
        $this->app->config->set(['error_tmpl' => __DIR__ . DIRECTORY_SEPARATOR . '../view/message.html'], 'app');
        $this->app->config->set(['success_tmpl' => __DIR__ . DIRECTORY_SEPARATOR . '../view/message.html'], 'app');
        $this->pageTitle = $message;
        
        return parent::dispatchJump($message, $status, $jumpUrl);
    }
    
    
    /**
     * 更新缓存
     * @throws Throwable
     */
    protected function updateCache()
    {
        // 更新菜单缓存
        SystemMenu::init()->updateCache();
        
        // 更新用户组缓存
        AdminGroup::init()->updateCache();
        
        // 生成系统配置文件
        SystemConfig::init()->updateCache();
        
        // 触发更新缓存事件
        Event::trigger(new AdminPanelUpdateCacheEvent());
    }
    
    
    /**
     * 清空缓存
     * @throws Throwable
     */
    protected function clearCache()
    {
        /** @var SplFileInfo $item */
        foreach (new FilesystemIterator($this->app->getBasePath()) as $item) {
            if (!$item->isDir()) {
                continue;
            }
            
            FileHelper::deleteDir($this->app->getRuntimeRootPath(sprintf('app/%s/temp', $item->getFilename())));
        }
        
        // 清理系统缓存
        CacheHelper::clean();
        FileHelper::deleteDir($this->app->getRuntimeCachePath());
        
        // 清理临时配置
        FileHelper::deleteDir($this->app->getRuntimeConfigPath());
        
        // 触发清理缓存事件
        Event::trigger(new AdminPanelClearCacheEvent());
        
        // 生成配置
        $this->updateCache();
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