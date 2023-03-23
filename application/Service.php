<?php
declare(strict_types = 1);

namespace BusyPHP\app;

use BusyPHP\App;
use BusyPHP\app\admin\controller\AdminHandle;
use BusyPHP\app\admin\controller\common\ErrorController;
use BusyPHP\app\admin\controller\common\FileController;
use BusyPHP\app\admin\controller\common\IndexController;
use BusyPHP\app\admin\controller\common\PassportController;
use BusyPHP\app\admin\controller\common\UeditorController;
use BusyPHP\app\admin\controller\common\UserController;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\setting\CaptchaSetting;
use BusyPHP\app\admin\taglib\Ba;
use BusyPHP\Captcha;
use BusyPHP\Handle;
use BusyPHP\helper\FileHelper;
use BusyPHP\Request;
use BusyPHP\Service as BusyService;
use Closure;
use think\event\HttpRun;
use think\Route;
use think\Service as ThinkService;

/**
 * Application服务类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/30 7:30 PM Service.php $
 * @property App $app
 */
class Service extends ThinkService
{
    /** @var string admin名称 */
    public const ADMIN_NAME = 'admin';
    
    
    public function register() : void
    {
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'helper.php';
    }
    
    
    public function boot() : void
    {
        // 注入验证码token
        Captcha::maker(function(Captcha $captcha) {
            $captcha->token(CaptchaSetting::instance()->getToken());
        });
        
        // 注入验证码响应参数
        Captcha::httpMaker(function(Captcha $captcha, string $app) {
            $setting = CaptchaSetting::instance()->setClient($app);
            
            $captcha->token($setting->getToken());
            $captcha->curve($setting->isCurve());
            $captcha->noise($setting->isNoise());
            $captcha->bgImage($setting->isBgImage());
            $captcha->length($setting->getLength());
            $captcha->expire($setting->getExpireMinute() * 60);
            $captcha->fontSize($setting->getFontSize());
            $captcha->token($setting->getToken());
            
            // 背景颜色
            if ($bgColor = $setting->getBgColor()) {
                $captcha->bgColor($bgColor);
            }
            
            // 验证码类型
            $zh = false;
            switch ($setting->getType()) {
                // 纯英文
                case 1:
                    $captcha->chars('abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY');
                break;
                // 纯数字
                case 2:
                    $captcha->chars('0123456789');
                break;
                // 中文
                case 3:
                    $zh = true;
                    $captcha->zh(true);
                break;
            }
            
            // 验证码字符
            if ($code = $setting->getCode()) {
                if ($zh) {
                    $captcha->zhChars($code);
                } else {
                    $captcha->chars($code);
                }
            }
            
            // 验证码字体
            if (is_file($fontFile = $setting->getFontFile(true))) {
                $captcha->fontFile($fontFile);
            } elseif ($font = $setting->getFont()) {
                if (str_starts_with($font, 'zh_')) {
                    $captcha->fontFile($this->app->getFrameworkPath(sprintf("captcha/zhttfs/%s.ttf", substr($font, 3))));
                } else {
                    $captcha->fontFile($this->app->getFrameworkPath(sprintf("captcha/ttfs/%s.ttf", $font)));
                }
            }
        });
        
        // 监听HttpRun
        $this->app->event->listen(HttpRun::class, function() {
            $this->app->middleware->add(function(Request $request, Closure $next) {
                // 针对后台配置
                if ($this->app->http->getName() === self::ADMIN_NAME) {
                    // 绑定错误处理程序
                    $this->app->bind(Handle::class, AdminHandle::class);
                    
                    $config = $this->app->config->get();
                    
                    // 注入后台标签库
                    $taglibPreLoad                     = $config['view']['taglib_pre_load'] ?? '';
                    $config['view']['taglib_pre_load'] = Ba::class . ($taglibPreLoad ? ',' . $taglibPreLoad : '');
                    
                    // 模版侦测
                    $templateDetect = $config['view']['template_detect'] ?? [];
                    
                    // 解析到 application/admin/view 目录
                    $templateDetect['@admin'] = function(string $template, array $config) {
                        return __DIR__ . '/admin/view/' . ltrim(substr($template, 7), '/') . '.html';
                    };
                    
                    $config['view']['template_detect']   = $templateDetect;
                    $config['view']['default_filter']    = '';
                    $config['route']['empty_controller'] = ErrorController::class;
                    $this->app->config->set($config);
                }
                
                return $next($request);
            });
        });
        
        // 注册路由
        $this->registerRoutes(function(Route $route) {
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
            $systemMenu = SystemMenu::class();
            $systemMenu::registerAnnotation(__DIR__ . '/../application/admin/controller/develop');
            $systemMenu::registerAnnotation(__DIR__ . '/../application/admin/controller/system');
            $systemMenu::registerAnnotation(__DIR__ . '/../application/admin/controller/common');
            $systemMenu::registerAnnotation($this->app->getBasePath() . self::ADMIN_NAME . '/controller');
            if ($this->app->http->getName() === self::ADMIN_NAME) {
                // 注册注解控制器
                $systemMenu::loadAnnotationRoutes();
                
                // common分组注册
                $route->group(function() use ($route) {
                    $actionPattern = '<' . BusyService::ROUTE_VAR_ACTION . '>';
                    $map           = [
                        'File'     => $this->app->getAlias(FileController::class),
                        'Index'    => $this->app->getAlias(IndexController::class),
                        'Passport' => $this->app->getAlias(PassportController::class),
                        'Ueditor'  => $this->app->getAlias(UeditorController::class),
                        'User'     => $this->app->getAlias(UserController::class)
                    ];
                    foreach ($map as $controller => $class) {
                        $route->rule("common.$controller/$actionPattern", "$class@$actionPattern")->append([
                            BusyService::ROUTE_VAR_CONTROL => $controller
                        ]);
                    }
                    
                    // 注册首页
                    $route->group(function() use ($route, $map) {
                        $index = $map['Index'] . '@index';
                        $route->rule('/', $index);
                        $route->rule('index', $index);
                    })->append([
                        BusyService::ROUTE_VAR_CONTROL => 'Index',
                        BusyService::ROUTE_VAR_ACTION  => 'index',
                    ]);
                    
                    // 注册登录地址
                    $route->rule('login', "{$map['Passport']}@login")->append([
                        BusyService::ROUTE_VAR_CONTROL => 'Passport',
                        BusyService::ROUTE_VAR_ACTION  => 'login'
                    ])->name('admin_login');
                    
                    // 注册退出地址
                    $route->rule('out', "{$map['Passport']}@out")->append([
                        BusyService::ROUTE_VAR_CONTROL => 'Passport',
                        BusyService::ROUTE_VAR_ACTION  => 'out'
                    ])->name('admin_out');
                })->append([
                    BusyService::ROUTE_VAR_TYPE  => BusyService::ROUTE_TYPE_PLUGIN,
                    BusyService::ROUTE_VAR_GROUP => 'common'
                ]);
            }
        });
    }
}