<?php
declare (strict_types = 1);

namespace BusyPHP;

use BusyPHP\cache\File;
use BusyPHP\command\Install;
use BusyPHP\command\Version;
use BusyPHP\middleware\ConfigInit;
use BusyPHP\middleware\RouteInit;
use BusyPHP\model\Query;
use BusyPHP\view\taglib\Cx;
use BusyPHP\view\View;
use think\cache\driver\Redis;
use think\middleware\SessionInit;
use think\Paginator;

/**
 * 应用服务类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午11:41 上午 Service.php $
 */
class Service extends \think\Service
{
    public function register()
    {
        $this->setConfig();
    }
    
    
    public function boot()
    {
        // 绑定URL生成类
        $this->app->bind(\think\app\Url::class, Url::class);
        
        
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
        $this->app->event->listen('HttpRun', function() {
            $this->app->middleware->add(ConfigInit::class);
        });
        
        
        // 添加路由中间件
        $this->app->middleware->import([
            RouteInit::class,
            SessionInit::class
        ], 'route');
        
        
        // 绑定命令行
        $this->commands([
            'busy_install' => Install::class,
            'busy_version' => Version::class,
        ]);
        
        
        // 分页页面获取注册
        Paginator::currentPageResolver(function($varPage = '') {
            $varPage = $varPage ?: config('route.var_page');
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
    private function setConfig()
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
        $route['var_redirect_url'] = $this->value($route, 'var_redirect_url', 'redirect_url');
        $route['var_page']         = $this->value($route, 'var_page', 'page');
        
        
        // trace
        $trace['file'] = $this->value($trace, 'file', App::getBusyPath('tpl') . 'trace.html');
        
        
        // 组合参数进行设置
        $config['app']      = $app;
        $config['view']     = $view;
        $config['database'] = $database;
        $config['cache']    = $cache;
        $config['route']    = $route;
        $config['trace']    = $trace;
        
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
}
