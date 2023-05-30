<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller;

use BusyPHP\App;
use BusyPHP\app\admin\component\js\Driver;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\model\system\token\SystemToken;
use BusyPHP\app\admin\model\system\token\SystemTokenField;
use BusyPHP\app\admin\setting\AdminSetting;
use BusyPHP\app\admin\setting\PublicSetting;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\traits\ContainerDefine;
use BusyPHP\traits\ContainerInstance;
use Closure;
use stdClass;
use think\Container;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Session;
use think\Request;
use think\Response;
use think\response\View;
use think\route\Url;
use Throwable;

/**
 * 后台异常处理类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/18 下午下午1:40 AdminHandle.php $
 */
class AdminHandle extends Handle implements ContainerInterface
{
    use ContainerDefine;
    use ContainerInstance;
    
    // +----------------------------------------------------
    // + cookie session
    // +----------------------------------------------------
    const COOKIE_AUTH_KEY      = 'admin_auth_key';
    
    const COOKIE_USER_ID       = 'admin_user_id';
    
    const COOKIE_USER_THEME    = 'admin_user_theme';
    
    const SESSION_OPERATE_TIME = 'admin_operate_time';
    
    // +----------------------------------------------------
    // + 错误吗
    // +----------------------------------------------------
    /** @var int 需要登录 */
    const CODE_NEED_LOGIN = 3020001;
    
    /** @var int 无法获取控制台日志 */
    const CODE_NEED_EMPTY_CONSOLE_LOG = 4010001;
    
    
    public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 是否单页请求
     * @return bool
     */
    public static function isSinglePage() : bool
    {
        return Driver::getRequestName() === 'SinglePage';
    }
    
    
    /**
     * 模板基础数据
     * @param string              $pageTitle
     * @param AdminUserField|null $adminUser
     * @return array
     */
    public static function templateBaseData(string $pageTitle = '', ?AdminUserField $adminUser = null) : array
    {
        $app     = App::getInstance();
        $request = $app->request;
        $data    = [];
        
        // 全局URL
        $data['url'] = [
            'root'       => $request->getWebUrl(),
            'app'        => $request->getAppUrl(),
            'self'       => url(),
            'controller' => $request->controller(),
            'action'     => $request->action(),
            'path'       => $request->getRoutePath(),
            'route_path' => $request->getRoutePath(true)
        ];
        
        
        // 计算面包屑
        $menuModel  = SystemMenu::init();
        $hasMap     = $menuModel->getHashMap();
        $breadcrumb = [];
        if ($currentMenu = ($hasMap[md5($data['url']['route_path'])] ?? null)) {
            $parentMap = $menuModel->getHashParentMap();
            $root      = $request->getAppUrl();
            foreach (array_reverse($parentMap[$currentMenu->hash] ?? []) as $hash) {
                if ($item = ($hasMap[$hash] ?? null)) {
                    $breadcrumb[] = [
                        'name' => $item->name,
                        'url'  => $item->url ? $root . ltrim($item->url, '/') : '',
                    ];
                }
            }
            $breadcrumb = array_values($breadcrumb);
            
            // 最终页面
            $query = [];
            foreach ($currentMenu->paramList as $item) {
                $query[$item] = $request->get($item);
            }
            
            $breadcrumb[] = [
                'name' => $currentMenu->name,
                'url'  => $root . ltrim($currentMenu->url, '/') . ($query ? '?' . http_build_query($query) : '')
            ];
        }
        
        
        // 页面名称
        if (!$pageTitle && $currentMenu) {
            $pageTitle = $currentMenu->name;
        }
        
        
        // 样式路径配置
        $theme        = static::getTheme($adminUser);
        $assetsUrl    = $request->getAssetsUrl();
        $skinRoot     = $assetsUrl . 'admin/';
        $version      = $app->config->get('app.admin.version', '');
        $version      = $version ? ".$version" : '';
        $data['skin'] = [
            'root'    => $skinRoot,
            'css'     => $skinRoot . 'css/',
            'js'      => $skinRoot . 'js/',
            'images'  => $skinRoot . 'images/',
            'themes'  => $skinRoot . 'themes/',
            'theme'   => $skinRoot . "themes/{$theme['skin']}.css",
            'lib'     => $assetsUrl . 'system/js/',
            'version' => ($app->isDebug() && $app->config->get('app.admin.debug', false)) ? time() : ($app->getFrameworkVersion() . $version),
        ];
        
        // 系统信息
        $adminSetting    = AdminSetting::instance();
        $publicSetting   = PublicSetting::instance();
        $pageTitleSuffix = ' - ' . $adminSetting->getTitle();
        $requires        = $app->config->get('app.admin.requires', '');
        if ($requires instanceof Closure) {
            $requires = Container::getInstance()->invokeFunction($requires);
        }
        $printCss = $app->config->get('app.admin.print_css', '');
        if ($printCss instanceof Closure) {
            $printCss = Container::getInstance()->invokeFunction($printCss);
        }
        $printStyle = $app->config->get('app.admin.print_style', '');
        if ($printStyle instanceof Closure) {
            $printStyle = Container::getInstance()->invokeFunction($printStyle);
        }
        
        $elementUi      = $app->config->get('app.admin.element_ui', false);
        $vue            = $elementUi || $app->config->get('app.admin.vue', false);
        $data['system'] = [
            'title'             => $adminSetting->getTitle(),
            'page_title'        => $pageTitle,
            'page_title_suffix' => $pageTitleSuffix,
            'favicon'           => $publicSetting->getFavicon(),
            'logo_icon'         => $adminSetting->getLogoIcon(),
            'logo_horizontal'   => $adminSetting->getLogoHorizontal(),
            'user'              => $adminUser,
            'breadcrumb'        => $breadcrumb,
            'frame_config'      => json_encode([
                'root'       => $data['url']['app'],
                'moduleRoot' => $data['skin']['lib'],
                'skinRoot'   => $data['skin']['root'],
                'jsRoot'     => $data['skin']['js'],
                'cssRoot'    => $data['skin']['css'],
                'imagesRoot' => $data['skin']['images'],
                'version'    => $data['skin']['version'],
                'debug'      => $app->config->get('app.admin.debug', false),
                'vue'        => $vue,
                'elementUi'  => $elementUi,
                'requires'   => $requires,
                'configs'    => [
                    'app'           => [
                        'imagePlaceholder'  => $publicSetting->getImagePlaceholder(false) . "?v={$data['skin']['version']}",
                        'avatarPlaceholder' => $publicSetting->getAvatarPlaceholder(false) . "?v={$data['skin']['version']}",
                        'url'               => $data['url']['app'],
                        'navSingleHold'     => $theme['nav_single_hold'],
                        'navMode'           => $theme['nav_mode'],
                        'pageTitleSuffix'   => $pageTitleSuffix,
                        'operateTipStyle'   => $app->config->get('app.admin.operate_tip_style') ?: 'toast',
                        'login'             => !!$adminUser,
                    ],
                    'upload'        => [
                        'configUrl' => (string) url('common.file/config?noext')->suffix(false),
                    ],
                    'editor'        => [
                        'ueConfigUrl' => (string) url('common.ueditor/runtime?js=1&noext')->suffix(false),
                    ],
                    'modal'         => [
                        'cancel_right' => $app->config->get('app.admin.modal_cancel_right', false),
                    ],
                    'topBar'        => [
                        'url' => $data['url']['app'],
                    ],
                    'tree'          => [
                        'url' => $data['url']['app']
                    ],
                    'linkagePicker' => [
                        'url' => $data['url']['app']
                    ],
                    'table'         => [
                        'url' => $data['url']['app']
                    ],
                    'selectPicker'  => [
                        'url' => $data['url']['app']
                    ],
                    'autocomplete'  => [
                        'url' => $data['url']['app']
                    ],
                    'validate'      => [
                        'remote' => $data['url']['app']
                    ],
                    'watermark'     => $adminSetting->getWatermark(),
                    'print'         => [
                        'css'   => $printCss,
                        'style' => $printStyle
                    ],
                    'consoleLog'    => [
                        'url' => $data['url']['app']
                    ]
                ]
            ], JSON_UNESCAPED_UNICODE)
        ];
        
        return $data;
    }
    
    
    /**
     * 处理数据渲染
     * @param Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e) : Response
    {
        if ($e instanceof HttpResponseException || $e instanceof HttpException) {
            return parent::render($request, $e);
        }
        
        if ($request->isAjax() && !self::isSinglePage()) {
            return $this->error($e);
        }
        
        if ($request->isJson()) {
            return parent::render($request, $e);
        }
        
        // 异常页面
        try {
            /** @var View $view */
            $view = View::create(__DIR__ . DIRECTORY_SEPARATOR . '../view/exception.html', 'view');
            $view->assign(self::templateBaseData('系统发生错误'));
            $view->assign($this->convertExceptionToArray($e));
            
            return $view;
        } catch (Throwable $e) {
            return parent::render($request, $e);
        }
    }
    
    
    /**
     * Rest响应
     * @param int              $code 错误码，1为成功
     * @param string|Throwable $message 消息
     * @param array|object     $result 数据
     * @param string|Url       $url 跳转的URL
     * @return Response
     */
    public function response(int $code = 1, string|Throwable $message = '', array|object $result = [], string|Url $url = '') : Response
    {
        if ($code === 1 && is_array($result) && $result && !ArrayHelper::isAssoc($result)) {
            return $this->response(0, '返回数据结构必须是键值对形式');
        }
        
        $exception = [];
        if ($message instanceof Throwable) {
            $exception = $this->convertExceptionToArray($message);
            $message   = $exception['message'];
            if (!$code && $exception['code'] !== 1) {
                $code = $exception['code'];
            }
        }
        
        $data = [
            'code'    => $code,
            'message' => $message ?: ($code === 1 ? 'Succeeded' : 'Failed'),
            'result'  => $result ?: new stdClass(),
            'url'     => (string) $url,
        ];
        
        if ($this->app->isDebug()) {
            $runtime       = number_format(microtime(true) - $this->app->getBeginTime(), 10, '.', '');
            $data['debug'] = [
                'runtime' => sprintf("%ss", number_format((float) $runtime, 6)),
                'mbps'    => sprintf("%sreq/s", $runtime > 0 ? number_format(1 / $runtime, 2) : '∞'),
                'memory'  => sprintf("%skb", number_format((memory_get_usage() - $this->app->getBeginMem()) / 1024, 2)),
                'query'   => sprintf("%s queries", $this->app->db->getQueryTimes()),
                'cache'   => sprintf("%s reads,%s writes", $this->app->cache->getReadTimes(), $this->app->cache->getWriteTimes()),
                'cookies' => $this->app->request->cookie(),
                'session' => $this->app->exists('session') ? $this->app->session->all() : [],
                'files'   => get_included_files(),
            ];
            $data['trace'] = trace();
            
            if ($exception) {
                $data['exception'] = $exception;
            }
        }
        
        return Response::create($data, 'json');
    }
    
    
    /**
     * Rest响应成功
     * @param mixed      $message 成功消息或成功的数据
     * @param mixed      $result 成功数据或跳转的URL
     * @param string|Url $url 跳转的地址
     * @return Response
     */
    public function success(mixed $message = '', mixed $result = [], string|Url $url = '') : Response
    {
        // $message 为 Url 对象，则只保留 $message
        if ($message instanceof Url) {
            $url     = $message;
            $message = '';
            $result  = [];
        }
        
        // $message 为 array 或 object，此时 $result 只能是 Url 对象 或 string
        if (is_array($message) || is_object($message)) {
            $url = '';
            if ($result instanceof Url || is_string($result)) {
                $url = $result;
            }
            $result  = $message;
            $message = '';
        }
        
        return $this->response(1, $message, $result, $url);
    }
    
    
    /**
     * Rest响应失败
     * @param string|Throwable $message 失败消息或异常类对象
     * @param string|Url|int   $url 跳转地址或错误代码或错误数据
     * @param int              $code 错误代码
     * @return Response
     */
    public function error(string|Throwable $message = '', string|Url|int $url = '', int $code = 0) : Response
    {
        if (is_int($url)) {
            $code = $url;
            $url  = '';
        }
        
        return $this->response($code === 1 ? 0 : $code, $message, [], $url);
    }
    
    
    /**
     * 保存主题到cookie
     * @param $id
     * @param $theme
     */
    public function saveTheme($id, $theme)
    {
        Cookie::set(static::COOKIE_USER_THEME . $id, is_array($theme) ? json_encode($theme, JSON_UNESCAPED_UNICODE) : $theme, 86400 * 365);
    }
    
    
    /**
     * 获取主题
     * @param AdminUserField|null $userInfo
     * @return array{skin: string, nav_mode: bool, nav_single_hold: bool}
     */
    public static function getTheme(?AdminUserField $userInfo = null) : array
    {
        if ($userInfo) {
            $theme = $userInfo->theme;
        } else {
            $userId = Cookie::get(static::COOKIE_USER_ID);
            $theme  = Cookie::get(static::COOKIE_USER_THEME . $userId);
            $theme  = json_decode((string) $theme, true) ?: [];
        }
        
        $theme['skin']            = trim($theme['skin'] ?? '');
        $theme['skin']            = $theme['skin'] ?: Config::get('app.admin.theme_skin', 'default');
        $theme['nav_mode']        = isset($theme['nav_mode']) ? (intval($theme['nav_mode']) > 0) : Config::get('app.admin.theme_nav_mode', false);
        $theme['nav_single_hold'] = isset($theme['nav_single_hold']) ? (intval($theme['nav_single_hold']) > 0) : Config::get('app.admin.theme_nav_single_hold', false);
        
        return $theme;
    }
    
    
    /**
     * 保存登录参数
     * @param SystemTokenField $token
     * @param AdminUserField   $user
     * @param bool             $saveLogin
     */
    public function saveLogin(SystemTokenField $token, AdminUserField $user, bool $saveLogin = false)
    {
        Cookie::set(
            static::COOKIE_AUTH_KEY,
            AdminUser::instance()->createAuthKey(
                $token,
                $user,
                $saveLogin
            ),
            $saveLogin ? AdminSetting::instance()->getSaveLogin() : null
        );
        Cookie::set(static::COOKIE_USER_ID, (string) $user->id, 86400 * 365);
        
        $this->saveTheme($user->id, $user->theme);
        $this->updateOperateTime();
    }
    
    
    /**
     * 检查登录
     * @param bool $saveOperate 是否记录操作时间
     * @return AdminUserField
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function checkLogin(bool $saveOperate) : AdminUserField
    {
        // 验证登录时常
        $setting = AdminSetting::instance();
        $often   = $setting->getOften();
        if ($often > 0) {
            $operateTime = (int) Session::get(static::SESSION_OPERATE_TIME, 0);
            if (time() - ($often * 60) > $operateTime) {
                throw new VerifyException('登录超时', 'timeout');
            }
        }
        
        // 获取Cookie
        $userId  = Cookie::get(static::COOKIE_USER_ID, '0');
        $authKey = Cookie::get(static::COOKIE_AUTH_KEY, '');
        if (!$userId || !$authKey) {
            throw new VerifyException('请登录', 'cookie');
        }
        
        $user = AdminUser::instance()->checkLogin($authKey, SystemToken::class()::DEFAULT_TYPE);
        
        // 记录最后操作时间
        if ($saveOperate) {
            $this->updateOperateTime();
        }
        
        return $user;
    }
    
    
    /**
     * 退出登录
     */
    public function outLogin()
    {
        Cookie::delete(static::COOKIE_AUTH_KEY);
    }
    
    
    /**
     * 更新最后一次操作时间
     */
    protected function updateOperateTime()
    {
        if (AdminSetting::instance()->getOften()) {
            Session::set(static::SESSION_OPERATE_TIME, time());
        }
    }
}