<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller;

use BusyPHP\App;
use BusyPHP\app\admin\component\js\Driver;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\setting\AdminSetting;
use BusyPHP\app\admin\setting\PublicSetting;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\traits\ContainerDefine;
use BusyPHP\traits\ContainerInstance;
use Closure;
use stdClass;
use think\Container;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
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
    
    /** @var int 需要登录 */
    const CODE_NEED_LOGIN = 3020001;
    
    /** @var int 无法获取控制台日志 */
    const CODE_NEED_EMPTY_CONSOLE_LOG = 4010001;
    
    
    public static function defineContainer() : string
    {
        return static::class;
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
        $theme        = AdminUser::init()->getTheme($adminUser);
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
                'requires'   => $requires,
                'configs'    => [
                    'app'           => [
                        'errorImgUrl'     => $publicSetting->getImgErrorPlaceholder(false) . "?v={$data['skin']['version']}",
                        'url'             => $data['url']['app'],
                        'navSingleHold'   => $theme['nav_single_hold'],
                        'navMode'         => $theme['nav_mode'],
                        'pageTitleSuffix' => $pageTitleSuffix,
                        'operateTipStyle' => $app->config->get('app.admin.operate_tip_style') ?: 'toast',
                        'login'           => !!$adminUser,
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
                    'validate'    => [
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
            return $this->jsonError($e);
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
     * @param int    $code 错误码，1为成功
     * @param string $message 消息
     * @param array  $result 数据
     * @param mixed  $url 跳转的URL
     * @return Response
     */
    public function json(int $code = 1, string $message = '', array $result = [], $url = '') : Response
    {
        $app = $this->app;
        $url = (string) $url;
        
        if ($code === 1) {
            if ($result && !ArrayHelper::isAssoc($result)) {
                return $this->json(0, '返回数据结构必须是键值对形式');
            }
        }
        
        $data = [
            'code'    => $code,
            'message' => $message ?: ($code === 1 ? 'Succeeded' : 'Failed'),
            'result'  => $result ?: new stdClass(),
            'url'     => $url,
        ];
        
        if ($app->isDebug()) {
            $runtime = number_format(microtime(true) - $app->getBeginTime(), 10, '.', '');
            $reqs    = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
            $mem     = number_format((memory_get_usage() - $app->getBeginMem()) / 1024, 2);
            $uri     = $app->request->protocol() . ' ' . $app->request->method() . ' : ' . $app->request->url(true);
            $files   = get_included_files();
            
            $data['debugs'] = [
                'request' => sprintf("%s %s", date('Y-m-d H:i:s', $app->request->time() ?: time()), $uri),
                'runtime' => sprintf("%ss [ 吞吐率：%sreq/s ] 内存消耗：%skb 文件加载：%s", number_format((float) $runtime, 6), $reqs, $mem, count($files)),
                'query'   => sprintf("%s queries", $app->db->getQueryTimes()),
                'cache'   => sprintf("%s reads,%s writes", $app->cache->getReadTimes(), $app->cache->getWriteTimes()),
                'cookies' => $app->request->cookie(),
                'session' => $app->exists('session') ? $app->session->all() : [],
                'files'   => $files,
            ];
            $data['traces'] = trace();
        }
        
        return Response::create($data, 'json');
    }
    
    
    /**
     * Rest响应成功
     * @param string|array     $message 成功消息或成功的数据
     * @param array|string|Url $result 成功数据或跳转的URL
     * @param string|Url       $url 跳转的地址
     * @return Response
     */
    public function jsonSuccess(string|array $message = '', array|string|Url $result = [], string|Url $url = '') : Response
    {
        if (is_array($message)) {
            $url     = is_array($result) ? '' : $result;
            $result  = $message;
            $message = '';
        } elseif (!is_array($result) && $result) {
            $url = $result;
        }
        
        return $this->json(1, $message, $result, $url);
    }
    
    
    /**
     * Rest响应失败
     * @param string|Throwable     $message 失败消息或异常类对象
     * @param string|Url|int|array $url 跳转地址或错误代码或错误数据
     * @param int                  $code 错误代码
     * @return Response
     */
    public function jsonError(mixed $message = '', string|Url|int|array $url = '', int $code = 0) : Response
    {
        $errorCode = 0;
        $result    = [];
        if ($message instanceof Throwable) {
            $result    = $this->convertExceptionToArray($message);
            $message   = $result['message'];
            $errorCode = $result['code'];
            unset($result['message'], $result['code']);
        }
        
        if (is_int($url)) {
            $code = (int) $url;
            $url  = '';
        } elseif (is_array($url)) {
            if (ArrayHelper::isAssoc($url)) {
                $result += $url;
            }
            $url = '';
        }
        
        if (!$code) {
            $code = $errorCode;
        }
        
        return $this->json($code === 1 ? 0 : $code, $message, $result, $url);
    }
}