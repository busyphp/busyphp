<?php

namespace BusyPHP\helper;

use BusyPHP\App;
use think\facade\Log;
use think\log\Channel;

/**
 * 日志辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/30 下午下午5:42 LogHelper.php $
 */
class LogHelper
{
    /**
     * 自定义日志通道
     * @param string $name 通道名称
     * @return Channel
     */
    public static function use(string $name) : Channel
    {
        $app    = App::init();
        $config = $app->config->get('log', []);
        $type   = "bp:use_{$name}";
        if (empty($config['channels'][$type])) {
            $config['channels'][$type] = [
                'type'           => 'File',
                'path'           => $app->getRuntimeRootPath('log' . DIRECTORY_SEPARATOR . $name),
                'single'         => false,
                'apart_level'    => [],
                'max_files'      => 0,
                'json'           => false,
                'processor'      => null,
                'close'          => false,
                'format'         => '[%s][%s] %s',
                'realtime_write' => false,
            ];
            $app->config->set($config, 'log');
        }
        
        return Log::channel($type);
    }
    
    
    /**
     * 自定义插件日志通道
     * @param string $name
     * @return Channel
     */
    public static function plugin(string $name) : Channel
    {
        return static::use("plugin_{$name}");
    }
}