<?php
declare (strict_types = 1);

namespace BusyPHP\initializer;

use BusyPHP\Service;
use think\App;

/**
 * RegisterService
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/10/4 10:29 PM RegisterService.php $
 */
class RegisterService extends \think\initializer\RegisterService
{
    public function init(App $app)
    {
        parent::init($app);
        
        // 注册基本服务类
        $app->register(Service::class);
        
        // 注册Application服务类
        $app->register(\BusyPHP\app\Service::class);
        
        // 注册扩展服务类
        $file = $app->getRootPath() . 'vendor/busyphp_services.php';
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
