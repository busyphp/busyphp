<?php
declare (strict_types = 1);

namespace BusyPHP;

use BusyPHP\cache\File;
use BusyPHP\command\Install;
use BusyPHP\command\Version;
use BusyPHP\middleware\ConfigInit;
use BusyPHP\middleware\RouteInit;
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
        // app
        $config             = $this->app->config->get();
        $app                = $config['app'];
        $app['app_express'] = true;
        
        
        // 文件方式缓存配置
        $cache                   = $config['cache'];
        $file                    = $cache['stores']['file'] ?? [];
        $file['type']            = isset($file['type']) && $file['type'] ? $file['type'] : File::class;
        $file['path']            = isset($file['path']) && $file['path'] ? $file['path'] : App::runtimeCachePath();
        $cache['stores']['file'] = $file;
        
        
        // trace
        $trace         = $config['trace'];
        $trace['file'] = isset($trace['file']) && $trace['file'] ? $trace['file'] : App::getBusyPath('tpl') . 'trace.html';
        
        
        // 整合
        $config['app']   = $app;
        $config['cache'] = $cache;
        $config['trace'] = $trace;
        $this->app->config->set($config);
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
}
