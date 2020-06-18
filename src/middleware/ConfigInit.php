<?php

namespace BusyPHP\middleware;

use BusyPHP\App;
use BusyPHP\app\admin\taglib\Admin;
use BusyPHP\contract\interfaces\Middleware;
use BusyPHP\Request;
use Closure;
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
    
    
    public function __construct(App $app)
    {
        $this->app = $app;
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
     * 设置应用运行配置
     */
    private function setConfig()
    {
        $config = $this->app->config->get();
        $view   = $this->value($config, 'view', []);
        $route  = $this->value($config, 'route', []);
        
        
        // 模板配置
        $view['view_path'] = $this->value($view, 'view_path', $this->app->getAppPath() . 'view' . DIRECTORY_SEPARATOR);
        
        
        // 针对后台配置
        if ($this->app->http->getName() === 'admin') {
            $view['taglib_pre_load']     = $this->value($view, 'taglib_pre_load', Admin::class);
            $route['default_controller'] = $this->value($route, 'default_controller', 'Common.index');
        }
        
        
        // 组合参数进行设置
        $config['view']  = $view;
        $config['route'] = $route;
        
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