<?php
declare (strict_types = 1);

namespace BusyPHP;

use BusyPHP\command\make\Field;
use BusyPHP\command\Publish;
use BusyPHP\command\Version;
use BusyPHP\facade\Captcha;
use BusyPHP\facade\QrCode;
use BusyPHP\image\driver\Local;
use Closure;
use think\event\HttpRun;
use think\middleware\SessionInit;
use think\Paginator;
use think\Request;
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
    public const ROUTE_VAR_DIR = '__busy_dir__';
    
    /** @var string 路由定义类型参数 */
    public const ROUTE_VAR_TYPE = '__busy_type__';
    
    /** @var string 路由定义分组参数 */
    public const ROUTE_VAR_GROUP = '__busy_group__';
    
    /** @var string 路由定义控制器参数 */
    public const ROUTE_VAR_CONTROL = '__busy_control__';
    
    /** @var string 路由定义方法参数 */
    public const ROUTE_VAR_ACTION = '__busy_action__';
    
    /** @var string 路由定义类型-插件 */
    public const ROUTE_TYPE_PLUGIN = 'plugin';
    
    
    public function boot()
    {
        // 配置BaseModel
        Model::setDb($this->app->db);
        Model::setInvoker([$this->app, 'invoke']);
        Model::maker(function(Model $model) {
            $config = $this->app->config;
            
            // 自动写入时间戳
            $isAutoWriteTimestamp = $model->getAutoWriteTimestamp();
            if (is_null($isAutoWriteTimestamp)) {
                $model->isAutoWriteTimestamp($config->get('database.auto_timestamp', 'timestamp'));
            }
            
            // 设置时间戳格式
            $dateFormat = $model->getDateFormat();
            if (is_null($dateFormat)) {
                $model->setDateFormat($config->get('database.datetime_format', 'Y-m-d H:i:s'));
            }
            
            // 设置字段
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
            Publish::class,
            Version::class,
            \BusyPHP\command\make\Model::class,
            Field::class
        ]);
        
        // 多应用支持
        $this->app->event->listen(HttpRun::class, function() {
            $this->app->middleware->add(MultipleApp::class);
        }, true);
        
        // 添加路由中间件
        $this->app->middleware->route(SessionInit::class);
        $this->app->middleware->route(function(Request $request, Closure $next) {
            // 通过插件方式引入
            if ($request->route(self::ROUTE_VAR_TYPE) === self::ROUTE_TYPE_PLUGIN) {
                $group = $request->route(self::ROUTE_VAR_GROUP);
                $request->setController(($group ? $group . '.' : '') . $request->route(self::ROUTE_VAR_CONTROL));
                $request->setAction($request->route(self::ROUTE_VAR_ACTION));
            }
            
            return $next($request);
        });
        
        // 注册路由
        $this->registerRoutes(function(Route $route) {
            // 验证码路由
            $route->rule('general/captcha', function() {
                return Captcha::http()->response();
            });
            
            // 二维码路由
            $route->rule('general/qrcode.<format>', function() {
                return QrCode::http()->response();
            });
            
            // 动态图片路由
            $route->rule('general/image/<path>', function() {
                return Local::http()->response();
            })->pattern(['path' => '.+']);
        });
    }
}
