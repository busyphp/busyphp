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
class AdminHandle extends Handle
{
    /** @var int 需要登录 */
    const CODE_NEED_LOGIN = 3020001;
    
    /** @var int 无法获取控制台日志 */
    const CODE_NEED_EMPTY_CONSOLE_LOG = 4010001;
    
    
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
            return self::restResponseError($e);
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
            'user'              => $adminUser ?? null,
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
                    'formVerify'    => [
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
     * Rest响应
     * @param int    $code 错误码，1为成功
     * @param string $message 消息
     * @param array  $result 数据
     * @param mixed  $url 跳转的URL
     * @return Response
     */
    public static function restResponse(int $code = 1, string $message = '', array $result = [], $url = '') : Response
    {
        $app = App::getInstance();
        $url = (string) $url;
        
        if ($code === 1) {
            if ($result && !ArrayHelper::isAssoc($result)) {
                return self::restResponse(0, '返回数据结构必须是键值对形式');
            }
        }
        
        $data = [
            'code'    => $code,
            'message' => $message ?: ($code === 1 ? 'Succeeded' : 'Failed'),
            'result'  => $result ?: new stdClass(),
            'url'     => $url,
        ];
        if ($app->isDebug()) {
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
    public static function restResponseSuccess($message = '', $result = [], $url = '') : Response
    {
        if (is_array($message)) {
            $url     = is_array($result) ? '' : $result;
            $result  = $message;
            $message = '';
        } elseif (!is_array($result) && $result) {
            $url = $result;
        }
        
        return self::restResponse(1, $message, $result, $url);
    }
    
    
    /**
     * Rest响应失败
     * @param string|Throwable $message 失败消息或异常类对象
     * @param string|Url|int   $url 跳转地址或错误代码
     * @param int              $code 错误代码
     * @return Response
     */
    public static function restResponseError($message = '', $url = '', int $code = 0) : Response
    {
        if ($message instanceof Throwable) {
            if ($message->getCode() !== 1) {
                $code = $message->getCode();
            }
            $message = $message->getMessage();
        }
        
        if (is_numeric($url)) {
            $code = $url;
        }
        
        return self::restResponse($code === 1 ? 0 : $code, $message, [], $url);
    }
}