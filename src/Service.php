<?php
declare (strict_types = 1);

namespace BusyPHP;

use BusyPHP\command\InstallCommand;
use BusyPHP\command\VersionCommand;
use Closure;
use think\event\HttpRun;
use think\middleware\SessionInit;
use think\Paginator;
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
    public const ROUTE_VAR_DIR = '__busy_dir__';
    
    /** @var string 路由定义类型参数 */
    public const ROUTE_VAR_TYPE = '__busy_type__';
    
    /** @var string 路由定义分组参数 */
    public const ROUTE_VAR_GROUP = '__busy_group__';
    
    /** @var string 路由定义控制器参数 */
    public const ROUTE_VAR_CONTROL = '__busy_control__';
    
    /** @var string 路由定义方法参数 */
    public const ROUTE_VAR_ACTION = '__busy_action__';
    
    /** @var string 插件路由 */
    public const ROUTE_PLUGIN = 'plugin';
    
    
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
        
        // 分页页面获取注册
        Paginator::currentPageResolver(function($varPage = '') {
            $page = $this->app->request->param($varPage);
            
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }
            
            return 1;
        });
        
        // 绑定命令行
        $this->commands([
            'bp:install' => InstallCommand::class,
            'bp:version' => VersionCommand::class,
        ]);
        
        // 监听HttpRun
        $this->app->event->listen(HttpRun::class, function() {
            $this->app->middleware->add(MultipleApp::class);
        });
        
        // 添加路由中间件
        $this->app->middleware->import([
            function(Request $request, Closure $next) {
                // 通过插件方式引入
                if ($request->route(self::ROUTE_VAR_TYPE) === self::ROUTE_PLUGIN) {
                    $group = $request->route(self::ROUTE_VAR_GROUP);
                    $request->setController(($group ? $group . '.' : '') . $request->route(self::ROUTE_VAR_CONTROL));
                    $request->setAction($request->route(self::ROUTE_VAR_ACTION));
                }
                
                return $next($request);
            },
            SessionInit::class
        ], 'route');
    }
}
