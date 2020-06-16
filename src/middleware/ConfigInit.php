<?php

namespace BusyPHP\middleware;

use BusyPHP\App;
use BusyPHP\app\admin\taglib\Admin;
use BusyPHP\cache\File;
use BusyPHP\contract\interfaces\Middleware;
use BusyPHP\model\Query;
use BusyPHP\Request;
use BusyPHP\view\taglib\Cx;
use BusyPHP\view\View;
use Closure;
use think\Http;
use think\Response;

/**
 * 应用配置初始化中间件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/5 下午3:40 下午 ConfigInit.php $
 */
class ConfigInit implements Middleware
{
    /**
     * @var App
     */
    protected $app;
    
    /**
     * @var Http
     */
    protected $http;
    
    
    public function __construct(App $app)
    {
        $this->app  = $app;
        $this->http = $this->app->http;
    }
    
    
    public function handle(Request $request, Closure $next)
    {
        // 导入路由
        include App::getBusyPath('app') . 'route.php';
        
        // 设置默认配置
        $this->setConfig();
        
        return $next($request);
    }
    
    
    /**
     * 设置默认配置
     */
    private function setConfig()
    {
        $config = $this->app->config->get();
        
        // 应用
        $app                   = $this->value($config, 'app', []);
        $tplPath               = App::getBusyPath('tpl');
        $app['exception_tmpl'] = $this->value($app, 'exception_tmpl', $tplPath . 'exception.html');
        $app['success_tmpl']   = $this->value($app, 'success_tpl', $tplPath . 'message.html');
        $app['error_tmpl']     = $this->value($app, 'error_tpl', $tplPath . 'message.html');
        
        
        // 错误级别配置
        $errorLevelExclude = $this->value($app, 'error_level_exclude', []);
        if (is_string($errorLevelExclude) && $errorLevelExclude === 'none') {
            $errorLevelExclude = [];
        } else {
            $errorLevelExclude = [E_NOTICE, E_WARNING, E_DEPRECATED];
        }
        $app['error_level_exclude'] = $errorLevelExclude;
        
        
        // 模板配置
        $view                    = $this->value($config, 'view', []);
        $view['type']            = $this->value($view, 'type', View::class);
        $view['taglib_build_in'] = $this->value($view, 'taglib_build_in', Cx::class);
        $view['taglib_begin']    = $this->value($view, 'taglib_begin', '<');
        $view['taglib_end']      = $this->value($view, 'taglib_end', '>');
        $view['default_filter']  = $this->value($view, 'default_filter', '');
        $view['view_path']       = $this->value($view, 'view_path', app_path('view'));
        
        
        // 数据库配置
        $database                         = $this->value($config, 'database', []);
        $connections                      = $this->value($database, 'connections', []);
        $mysql                            = $this->value($connections, 'mysql', []);
        $mysql['query']                   = $this->value($mysql, 'query', Query::class);
        $mysql['prefix']                  = $this->value($mysql, 'prefix', 'busy_');
        $mysql['schema_cache_path']       = $this->value($mysql, 'schema_cache_path', App::runtimeCachePath('schema'));
        $database['connections']['mysql'] = $mysql;
        
        
        // 文件缓存
        $cache                   = $this->value($config, 'cache', []);
        $cache['stores']         = $this->value($cache, 'stores', []);
        $file                    = $this->value($cache['stores'], 'file', []);
        $file['type']            = $this->value($file, 'type', File::class);
        $file['path']            = $this->value($file, 'path', App::runtimeCachePath());
        $cache['stores']['file'] = $file;
        
        
        // 路由配置
        $route                     = $this->value($config, 'route', []);
        $route['var_redirect_url'] = $this->value($route, 'var_redirect_url', 'redirect_url');
        $route['var_page']         = $this->value($route, 'var_page', 'page');
        
        
        // 针对后台配置
        if ($this->http->getName() === 'admin') {
            // 模板
            $view['taglib_pre_load'] = $this->value($view, 'taglib_pre_load', Admin::class);
            
            
            // 路由
            $route['default_controller'] = $this->value($route, 'default_controller', 'Common.index');
        }
        
        
        // 组合参数进行设置
        $config['app']      = $app;
        $config['view']     = $view;
        $config['database'] = $database;
        $config['cache']    = $cache;
        $config['route']    = $route;
        
        $this->app->config->set($config);
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
    
    
    /**
     * 结束调度
     * @param Response $response
     */
    public function end(Response $response) : void
    {
    }
}