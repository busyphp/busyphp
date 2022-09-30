<?php
declare (strict_types = 1);

namespace BusyPHP;

use BusyPHP\app\admin\controller\AdminHandle;
use BusyPHP\app\admin\controller\common\IndexController;
use BusyPHP\app\admin\controller\common\PassportController;
use BusyPHP\app\admin\controller\develop\ComponentController;
use BusyPHP\app\admin\controller\develop\ElementController;
use BusyPHP\app\admin\controller\develop\ConfigController;
use BusyPHP\app\admin\controller\develop\FileClassController;
use BusyPHP\app\admin\controller\develop\MenuController;
use BusyPHP\app\admin\controller\develop\PluginController;
use BusyPHP\app\admin\controller\system\FileController;
use BusyPHP\app\admin\controller\system\LogsController;
use BusyPHP\app\admin\controller\system\ManagerController;
use BusyPHP\app\admin\controller\system\UserController;
use BusyPHP\app\admin\controller\system\GroupController;
use BusyPHP\app\admin\taglib\Ba;
use BusyPHP\app\general\controller\QRCodeController;
use BusyPHP\app\general\controller\ThumbController;
use BusyPHP\app\general\controller\CaptchaController;
use BusyPHP\command\InstallCommand;
use BusyPHP\command\VersionCommand;
use BusyPHP\helper\FileHelper;
use Closure;
use think\event\HttpRun;
use think\middleware\SessionInit;
use think\Paginator;
use think\Route;
use think\Service as ThinkService;

/**
 * 应用服务类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午11:41 上午 Service.php $
 * @property App $app
 */
class Service extends ThinkService
{
    /** @var string 路由定义目录参数 */
    const ROUTE_VAR_DIR = '__busy_dir__';
    
    /** @var string 路由定义类型参数 */
    const ROUTE_VAR_TYPE = '__busy_type__';
    
    /** @var string 路由定义分组参数 */
    const ROUTE_VAR_GROUP = '__busy_group__';
    
    /** @var string 路由定义控制器参数 */
    const ROUTE_VAR_CONTROL = '__busy_control__';
    
    /** @var string 路由定义方法参数 */
    const ROUTE_VAR_ACTION = '__busy_action__';
    
    
    public function register()
    {
    }
    
    
    public function boot()
    {
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
            
            $timeField = $config->get('database.datetime_field');
            if (!empty($timeField)) {
                [$createTime, $updateTime] = explode(',', $timeField);
                $model->setTimeField($createTime, $updateTime);
            }
        });
        
        
        // 监听HttpRun
        $this->app->event->listen(HttpRun::class, function() {
            $this->app->middleware->add(MultipleApp::class);
            $this->app->middleware->add(function(Request $request, Closure $next) {
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
            function(Request $request, Closure $next) {
                // 通过插件方式引入
                if ($request->route(self::ROUTE_VAR_TYPE) === 'plugin') {
                    $group = $request->route(self::ROUTE_VAR_GROUP);
                    $request->setController(($group ? $group . '.' : '') . $request->route(self::ROUTE_VAR_CONTROL));
                    $request->setAction($request->route(self::ROUTE_VAR_ACTION));
                }
                
                return $next($request);
            },
            SessionInit::class
        ], 'route');
        
        
        // 绑定命令行
        $this->commands([
            'bp:install' => InstallCommand::class,
            'bp:version' => VersionCommand::class,
        ]);
        
        
        // 分页页面获取注册
        Paginator::currentPageResolver(function($varPage = '') {
            $page = $this->app->request->param($varPage);
            
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }
            
            return 1;
        });
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
            // 绑定错误处理程序
            $this->app->bind(Handle::class, AdminHandle::class);
            
            $taglibPreLoad           = $this->value($view, 'taglib_pre_load', '');
            $view['taglib_pre_load'] = Ba::class . ($taglibPreLoad ? ',' . $taglibPreLoad : '');
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
        $route->rule('general/captcha', CaptchaController::class . '@index');
        
        // 动态缩图路由
        $route->rule('thumbs/<path>', ThumbController::class . '@index')->pattern(['path' => '.+']);
        $route->rule('thumbs', ThumbController::class . '.@index');
        
        // 动态二维码路由
        $route->rule('qrcodes/<src>', QRCodeController::class . '@index')->pattern(['src' => '.+']);
        $route->rule('qrcodes', QRCodeController::class . '@index');
        
        // 后台路由
        if ($this->app->http->getName() === 'admin') {
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
                    self::ROUTE_VAR_TYPE    => 'plugin',
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
            })->append([self::ROUTE_VAR_TYPE => 'plugin', self::ROUTE_VAR_GROUP => 'Common']);
        }
        
        // 注册后台资源路由
        $route->rule('assets/admin/<path>', function(Request $request) {
            $parse = parse_url(ltrim(substr($request->pathinfo(), 12), '/'));
            $path  = $parse['path'] ?? '';
            
            return FileHelper::responseAssets(__DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . ltrim($path, '/'));
        })->pattern(['path' => '.*']);
        
        // 注册通用静态资源路由
        $route->rule('assets/static/<path>', function(Request $request) {
            $parse = parse_url(ltrim(substr($request->pathinfo(), 13), '/'));
            $path  = $parse['path'] ?? '';
            
            return FileHelper::responseAssets(__DIR__ . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . ltrim($path, '/'));
        })->pattern(['path' => '.*']);
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
