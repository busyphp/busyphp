<?php
declare (strict_types = 1);

namespace BusyPHP;

use BusyPHP\app\admin\taglib\Admin;
use BusyPHP\app\general\controller\InstallController;
use BusyPHP\app\general\controller\QRCodeController;
use BusyPHP\app\general\controller\ThumbController;
use BusyPHP\app\general\controller\VerifyController;
use BusyPHP\cache\File;
use BusyPHP\command\InstallCommand;
use BusyPHP\command\VersionCommand;
use BusyPHP\model\Query;
use BusyPHP\view\taglib\Cx;
use BusyPHP\view\View;
use think\app\Url as ThinkUrl;
use think\cache\driver\Redis;
use think\event\HttpRun;
use think\middleware\SessionInit;
use think\Paginator;
use think\Route;
use think\Service as ThinkService;

/**
 * 应用服务类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午11:41 上午 Service.php $
 */
class Service extends ThinkService
{
    public function register()
    {
        $this->configInit();
    }
    
    
    public function boot()
    {
        // 绑定URL生成类
        $this->app->bind(ThinkUrl::class, Url::class);
        
        
        // 配置BaseModel
        Model::setDb($this->app->db);
        Model::setEvent($this->app->event);
        Model::setInvoker([$this->app, 'invoke']);
        Model::maker(function(Model $model) {
            $config = $this->app->config;
            
            $isAutoWriteTimestamp = $model->getAutoWriteTimestamp();
            
            if (is_null($isAutoWriteTimestamp)) {
                // 自动写入时间戳
                $model->isAutoWriteTimestamp($config->get('database.auto_timestamp', 'timestamp'));
            }
            
            $dateFormat = $model->getDateFormat();
            
            if (is_null($dateFormat)) {
                // 设置时间戳格式
                $model->setDateFormat($config->get('database.datetime_format', 'Y-m-d H:i:s'));
            }
        });
        
        
        // 监听HttpRun
        $this->app->event->listen(HttpRun::class, function() {
            $this->app->middleware->add(function(Request $request, \Closure $next) {
                $this->configHttpRun();
                
                return $next($request);
            });
        });
        
        
        // 注册路由
        $this->registerRoutes(function(Route $route) {
            $this->configRoutes($route);
        });
        
        
        // 添加路由中间件
        $this->app->middleware->import([
            function(Request $request, \Closure $next) {
                $this->configRoutePlugin($request);
                
                return $next($request);
            },
            SessionInit::class
        ], 'route');
        
        
        // 绑定命令行
        $this->commands([
            'busy_install' => InstallCommand::class,
            'busy_version' => VersionCommand::class,
        ]);
        
        
        // 分页页面获取注册
        Paginator::currentPageResolver(function($varPage = '') {
            $varPage = $varPage ?: $this->app->config->get('route.var_page');
            $varPage = $varPage ?: 'page';
            $page    = $this->app->request->param($varPage);
            
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }
            
            return 1;
        });
    }
    
    
    /**
     * 设置默认配置
     */
    private function configInit()
    {
        $config      = $this->app->config->get();
        $app         = $this->value($config, 'app', []);
        $view        = $this->value($config, 'view', []);
        $database    = $this->value($config, 'database', []);
        $connections = $this->value($database, 'connections', []);
        $mysql       = $this->value($connections, 'mysql', []);
        $cache       = $this->value($config, 'cache', []);
        $route       = $this->value($config, 'route', []);
        $trace       = $this->value($config, 'trace', []);
        $session     = $this->value($config, 'session', []);
        
        
        // 应用
        $tplPath               = App::getBusyPath('tpl');
        $app['exception_tmpl'] = $this->value($app, 'exception_tmpl', $tplPath . 'exception.html');
        $app['success_tmpl']   = $this->value($app, 'success_tpl', $tplPath . 'message.html');
        $app['error_tmpl']     = $this->value($app, 'error_tpl', $tplPath . 'message.html');
        $app['app_express']    = true;
        
        
        // 错误级别配置
        $errorLevelExclude = $this->value($app, 'error_level_exclude', []);
        if (is_string($errorLevelExclude) && $errorLevelExclude === 'none') {
            $errorLevelExclude = [];
        } else {
            $errorLevelExclude = [E_NOTICE, E_WARNING, E_DEPRECATED];
        }
        $app['error_level_exclude'] = $errorLevelExclude;
        
        
        // 模板配置
        $view['type']            = $this->value($view, 'type', View::class);
        $view['taglib_build_in'] = $this->value($view, 'taglib_build_in', Cx::class);
        $view['taglib_begin']    = $this->value($view, 'taglib_begin', '<');
        $view['taglib_end']      = $this->value($view, 'taglib_end', '>');
        $view['default_filter']  = $this->value($view, 'default_filter', '');
        
        
        // 数据库配置
        $mysql['query']                   = $this->value($mysql, 'query', Query::class);
        $mysql['prefix']                  = $this->value($mysql, 'prefix', 'busy_');
        $mysql['schema_cache_path']       = $this->value($mysql, 'schema_cache_path', App::runtimeCachePath('schema'));
        $database['connections']['mysql'] = $mysql;
        
        
        // 文件缓存
        $cache['stores']          = $this->value($cache, 'stores', []);
        $file                     = $this->value($cache['stores'], 'file', []);
        $file['type']             = $this->value($file, 'type', File::class);
        $file['path']             = $this->value($file, 'path', App::runtimeCachePath());
        $cache['stores']['file']  = $file;
        $redis                    = $this->value($cache['stores'], 'redis', []);
        $redis['type']            = $this->value($redis, 'type', Redis::class);
        $cache['stores']['redis'] = $redis;
        
        
        // 路由配置
        $route['group']            = $this->value($route, 'group', false);
        $route['var_redirect_url'] = $this->value($route, 'var_redirect_url', 'redirect_url');
        $route['var_page']         = $this->value($route, 'var_page', 'page');
        
        
        // trace
        $trace['file'] = $this->value($trace, 'file', App::getBusyPath('tpl') . 'trace.html');
        
        // session
        $session['path'] = $this->value($session, 'path', App::runtimeCachePath('session'));
        
        
        // 组合参数进行设置
        $config['app']      = $app;
        $config['view']     = $view;
        $config['database'] = $database;
        $config['cache']    = $cache;
        $config['route']    = $route;
        $config['trace']    = $trace;
        $config['session']  = $session;
        
        $this->app->config->set($config);
    }
    
    
    /**
     * Http运行配置
     */
    private function configHttpRun()
    {
        $config = $this->app->config->get();
        $view   = $this->value($config, 'view', []);
        $route  = $this->value($config, 'route', []);
        
        // 模板配置
        $view['view_path'] = $this->value($view, 'view_path', $this->app->getAppPath() . 'view' . DIRECTORY_SEPARATOR);
        
        
        // 针对后台配置
        if ($this->app->http->getName() === 'admin') {
            $taglibPreLoad               = $this->value($view, 'taglib_pre_load', '');
            $view['taglib_pre_load']     = Admin::class . ($taglibPreLoad ? ',' . $taglibPreLoad : '');
            $route['group']              = true;
            $route['default_group']      = 'Common';
            $route['default_controller'] = 'Index';
        }
        
        
        // 组合参数进行设置
        $config['view']  = $view;
        $config['route'] = $route;
        
        $this->app->config->set($config);
    }
    
    
    /**
     * 注册路由
     * @param Route $route
     */
    private function configRoutes(Route $route)
    {
        // 验证码路由
        $route->rule('general/verify', VerifyController::class . '@index');
        
        // 动态缩图路由
        $route->rule('thumbs/<src>', ThumbController::class . '@index')->pattern(['src' => '.+']);
        $route->rule('thumbs', ThumbController::class . '.@index');
        
        // 动态二维码路由
        $route->rule('qrcodes/<src>', QRCodeController::class . '@index')->pattern(['src' => '.+']);
        $route->rule('qrcodes', QRCodeController::class . '@index');
        
        // 数据库安装路由
        $route->group(function() use ($route) {
            $route->rule('general/install/<action>', InstallController::class . '@<action>');
            $route->rule('general/install', InstallController::class . '@index')->append(['action' => 'index']);
        })->append([
            'type'    => 'plugin',
            'control' => 'Install'
        ]);
        
        // 后台路由
        if ($this->app->http->getName() === 'admin') {
            $route->group(function() use ($route) {
                $route->rule('Develop.<control>/<action>', 'develop\<control>Controller@<action>')->append([
                    'group' => 'Develop'
                ]);
                
                $route->rule('System.Update/<action>', 'system\UpdateController@<action>')->append([
                    'group'   => 'System',
                    'control' => 'Update',
                ])->pattern([
                    'action' => '[cache|index]+'
                ]);
                
                $route->rule('System.Index/<action>', 'system\IndexController@<action>')->append([
                    'group'   => 'System',
                    'control' => 'Index',
                ])->pattern([
                    'action' => '[index|admin|logs|view_logs|clear_logs]+'
                ]);
                
                $route->rule('System.User/<action>', 'system\UserController@<action>')->append([
                    'group'   => 'System',
                    'control' => 'User',
                ])->pattern([
                    'action' => '[index|add|edit|delete|personal_info|personal_password|password]+'
                ]);
                
                $route->rule('System.Group/<action>', 'system\GroupController@<action>')->append([
                    'group'   => 'System',
                    'control' => 'Group',
                ])->pattern([
                    'action' => '[index|add|edit|delete]+'
                ]);
                
                $route->rule('System.File/<action>', 'system\FileController@<action>')->append([
                    'group'   => 'System',
                    'control' => 'File',
                ])->pattern([
                    'action' => '[setting|index|delete|upload|library]+'
                ]);
                
                $route->rule('Common.<control>/<action>', 'common\<control>Controller@<action>')->append([
                    'group' => 'Common',
                ])->pattern([
                    'control' => '[Passport|Ueditor|Js|Action|Index]+'
                ]);
                
                $route->group(function() use ($route) {
                    $index = 'common\IndexController@index';
                    $route->rule('/', $index);
                    $route->rule('index', $index);
                })->append([
                    'action'  => 'index',
                    'control' => 'Index',
                    'group'   => 'Common'
                ]);
                
                
                // 注册登录地址
                $route->rule('login', 'common\PassportController@login')->append([
                    'group'   => 'Common',
                    'control' => 'Passport',
                    'action'  => 'login'
                ])->name('admin_login');
                
                // 注册退出地址
                $route->rule('out', 'common\PassportController@out')->append([
                    'group'   => 'Common',
                    'control' => 'Passport',
                    'action'  => 'out'
                ])->name('admin_out');
            })->prefix('BusyPHP\app\admin\controller\\')->append(['type' => 'plugin']);
        }
    }
    
    
    /**
     * 配置路由扩展
     * @param Request $request
     */
    private function configRoutePlugin(Request $request)
    {
        // 通过插件方式引入
        if ($request->route('type') === 'plugin') {
            $group = $request->route('group');
            $request->setGroup($group ?? '');
            $request->setController(($group ? $group . '.' : '') . $request->route('control'));
            $request->setAction($request->route('action'));
        }
        
        
        // 解析站点入口URL
        $root = $request->baseFile();
        if ($root && 0 !== strpos($request->url(), $root)) {
            $root = str_replace('\\', '/', dirname($root));
        }
        
        $root = rtrim($root, '/') . '/';
        $root = strpos($root, '.') ? ltrim(dirname($root), DIRECTORY_SEPARATOR) : $root;
        if ('' != $root) {
            $root = '/' . ltrim($root, '/');
        }
        $webUrl = rtrim($root, '/') . '/';
        $request->setWebUrl($webUrl);
        
        
        // 解析应用入口Url
        $appUrl = $request->root();
        if (false === strpos($appUrl, '.')) {
            $appUrl = $webUrl . trim($appUrl, '/');
        }
        $appUrl = rtrim($appUrl, '/') . '/';
        $request->setAppUrl($appUrl);
        
        
        // 定义常量
        if (!defined('GROUP_NAME')) {
            /**
             * 分组名称
             */
            define('GROUP_NAME', $request->group());
        }
        
        if (!defined('MODULE_NAME')) {
            /**
             * 控制器名称
             */
            define('MODULE_NAME', $request->controller(false, true));
        }
        
        if (!defined('ACTION_NAME')) {
            /**
             * 执行方法名称
             */
            define('ACTION_NAME', $request->action());
        }
        
        if (!defined('URL_ROOT')) {
            /**
             * 网站根目录地址
             */
            define('URL_ROOT', $request->getWebUrl());
        }
        
        if (!defined('URL_APP')) {
            /**
             * 当前项目地址
             */
            define('URL_APP', $request->getAppUrl());
        }
        
        if (!defined('URL_ASSETS')) {
            /**
             * 静态资源URL
             */
            define('URL_ASSETS', $request->getWebAssetsUrl());
        }
        
        if (!defined('URL_SELF')) {
            /**
             * 当前URL，包含QueryString
             */
            define('URL_SELF', $request->url());
        }
        
        if (!defined('URL_DOMAIN')) {
            /**
             * 当前域名
             */
            define('URL_DOMAIN', $request->domain());
        }
    }
    
    
    /**
     * 获取配置值
     * @param $array
     * @param $key
     * @param $default
     * @return mixed
     */
    private function value($array, $key, $default)
    {
        return isset($array[$key]) && !empty($array[$key]) ? $array[$key] : $default;
    }
}
