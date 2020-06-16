<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace BusyPHP\initializer;

use BusyPHP\Service;
use think\App;

/**
 * 注册系统服务
 */
class RegisterService extends \think\initializer\RegisterService
{
    public function init(App $app)
    {
        parent::init($app);
        
        // 注册基本服务类
        $app->register(Service::class);
        
        // 注册扩展服务类
        $file = $app->getRootPath() . 'vendor/busy_services.php';
        if (is_file($file)) {
            $services = include $file;
            foreach ($services as $service) {
                if (class_exists($service)) {
                    $app->register($service);
                }
            }
        }
    }
}
