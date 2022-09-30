<?php

namespace BusyPHP\app;

use BusyPHP\app\admin\controller\AdminHandle;
use BusyPHP\app\admin\controller\common\IndexController;
use BusyPHP\app\admin\controller\common\PassportController;
use BusyPHP\app\admin\controller\develop\ComponentController;
use BusyPHP\app\admin\controller\develop\ConfigController;
use BusyPHP\app\admin\controller\develop\ElementController;
use BusyPHP\app\admin\controller\develop\FileClassController;
use BusyPHP\app\admin\controller\develop\MenuController;
use BusyPHP\app\admin\controller\develop\PluginController;
use BusyPHP\app\admin\controller\system\FileController;
use BusyPHP\app\admin\controller\system\GroupController;
use BusyPHP\app\admin\controller\system\LogsController;
use BusyPHP\app\admin\controller\system\ManagerController;
use BusyPHP\app\admin\controller\system\UserController;
use BusyPHP\app\admin\taglib\Ba;
use BusyPHP\app\general\controller\CaptchaController;
use BusyPHP\app\general\controller\QRCodeController;
use BusyPHP\app\general\controller\ThumbController;
use BusyPHP\Handle;
use BusyPHP\helper\FileHelper;
use BusyPHP\Request;
use Closure;
use think\event\HttpRun;
use think\Route;

/**
 * Application服务类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/30 7:30 PM Service.php $
 */
class Service extends \BusyPHP\Service
{
    /** @var string admin名称 */
    public const ADMIN_NAME = 'admin';
    
    
    public function boot() : void
    {
        // 监听HttpRun
        $this->app->event->listen(HttpRun::class, function() {
            $this->app->middleware->add(function(Request $request, Closure $next) {
                // 针对后台配置
                if ($this->app->http->getName() === self::ADMIN_NAME) {
                    // 绑定错误处理程序
                    $this->app->bind(Handle::class, AdminHandle::class);
                    
                    // 注入后台标签库
                    $config                            = $this->app->config->get();
                    $view                              = $config['view'] ?? [];
                    $taglibPreLoad                     = $view['taglib_pre_load'] ?? '';
                    $config['view']['taglib_pre_load'] = Ba::class . ($taglibPreLoad ? ',' . $taglibPreLoad : '');
                    $config['view']['template_detect'] = [$this, 'adminTemplateDetect'];
                    $this->app->config->set($config);
                }
                
                return $next($request);
            });
        });
        
        // 注册路由
        $this->registerRoutes(function(Route $route) {
            // 验证码路由
            $route->rule('general/captcha', CaptchaController::class . '@index');
            
            // 动态缩图路由
            $route->rule('thumbs/<path>', ThumbController::class . '@index')->pattern(['path' => '.+']);
            $route->rule('thumbs', ThumbController::class . '.@index');
            
            // 动态二维码路由
            $route->rule('qrcodes/<src>', QRCodeController::class . '@index')->pattern(['src' => '.+']);
            $route->rule('qrcodes', QRCodeController::class . '@index');
            
            // 注册后台资源路由
            $route->rule('assets/admin/<path>', function(Request $request) {
                $parse = parse_url(ltrim(substr($request->pathinfo(), 12), '/'));
                $path  = $parse['path'] ?? '';
                
                return FileHelper::responseAssets(__DIR__ . '/../assets/admin/' . ltrim($path, '/'));
            })->pattern(['path' => '.*']);
            
            // 注册通用静态资源路由
            $route->rule('assets/system/<path>', function(Request $request) {
                $parse = parse_url(ltrim(substr($request->pathinfo(), 13), '/'));
                $path  = $parse['path'] ?? '';
                
                return FileHelper::responseAssets(__DIR__ . '/../assets/' . ltrim($path, '/'));
            })->pattern(['path' => '.*']);
            
            // 后台路由
            if ($this->app->http->getName() === self::ADMIN_NAME) {
                $routeConfig = [
                    'system_menu'       => ['group' => 'develop', 'class' => MenuController::class,],
                    'system_config'     => ['group' => 'develop', 'class' => ConfigController::class],
                    'system_file_class' => ['group' => 'develop', 'class' => FileClassController::class],
                    'system_plugin'     => ['group' => 'develop', 'class' => PluginController::class],
                    'manual_component'  => ['group' => 'develop', 'class' => ComponentController::class],
                    'manual_element'    => ['group' => 'develop', 'class' => ElementController::class],
                    'system_file'       => ['group' => 'system', 'class' => FileController::class],
                    'system_user'       => ['group' => 'system', 'class' => UserController::class],
                    'system_group'      => ['group' => 'system', 'class' => GroupController::class],
                    'system_logs'       => ['group' => 'system', 'class' => LogsController::class],
                    'system_manager'    => ['group' => 'system', 'class' => ManagerController::class],
                ];
                
                $actionPattern  = '<' . self::ROUTE_VAR_ACTION . '>';
                $controlPattern = '<' . self::ROUTE_VAR_CONTROL . '>';
                foreach ($routeConfig as $key => $item) {
                    $roleItem = $route->rule("$key/$actionPattern", "{$item['class']}@$actionPattern");
                    $roleItem->append([
                        self::ROUTE_VAR_DIR     => $item['group'],
                        self::ROUTE_VAR_TYPE    => self::ROUTE_TYPE_PLUGIN,
                        self::ROUTE_VAR_CONTROL => $key
                    ]);
                    if (isset($item['actions'])) {
                        $roleItem->pattern([
                            self::ROUTE_VAR_ACTION => $item['actions']
                        ]);
                    }
                }
                
                // 通用注册
                $route->group(function() use ($route, $actionPattern, $controlPattern) {
                    // 全局
                    $route->rule("Common.$controlPattern/$actionPattern", "BusyPHP\app\admin\controller\common\\{$controlPattern}Controller@$actionPattern");
                    
                    // 注册首页
                    $route->group(function() use ($route) {
                        $index = IndexController::class . '@index';
                        $route->rule('/', $index);
                        $route->rule('index', $index);
                    })->append([
                        self::ROUTE_VAR_CONTROL => 'Index',
                        self::ROUTE_VAR_ACTION  => 'index',
                    ]);
                    
                    // 注册登录地址
                    $passport = PassportController::class;
                    $route->rule('login', "$passport@login")->append([
                        self::ROUTE_VAR_CONTROL => 'Passport',
                        self::ROUTE_VAR_ACTION  => 'login'
                    ])->name('admin_login');
                    
                    // 注册退出地址
                    $route->rule('out', "$passport@out")->append([
                        self::ROUTE_VAR_CONTROL => 'Passport',
                        self::ROUTE_VAR_ACTION  => 'out'
                    ])->name('admin_out');
                })->append([
                    self::ROUTE_VAR_TYPE  => self::ROUTE_TYPE_PLUGIN,
                    self::ROUTE_VAR_GROUP => 'Common'
                ]);
            }
        });
    }
    
    
    /**
     * 侦测admin模板
     * @param string $template
     * @param array  $config
     * @return string
     */
    public function adminTemplateDetect(string $template, array $config):string
    {
        // @admin:
        // 解析到 application/admin/view 目录
        if (0 === strpos($template, '@admin:')) {
            return __DIR__ . '/admin/view/' . ltrim(substr($template, 7), '/') . '.html';
        }
        
        return '';
    }
}