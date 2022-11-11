<?php
declare(strict_types = 1);

namespace BusyPHP\helper;

use BusyPHP\App;

/**
 * App辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/29 2:41 PM AppHelper.php $
 */
class AppHelper
{
    /** @var string CLI模式标识 */
    const CLI_CLIENT_KEY = ':cli';
    
    /** @var string CLI模式名称 */
    const CLI_CLIENT_NAME = '控制台';
    
    
    /**
     * 获取应用集合
     * @return array
     */
    public static function getList() : array
    {
        static $list;
        
        if (!isset($list)) {
            $basePath = App::getInstance()->getBasePath();
            $list     = [];
            $maps     = ['admin' => '系统', 'home' => '前端'];
            foreach (scandir($basePath) as $value) {
                if (!is_dir($path = $basePath . $value) || $value === '.' || $value === '..') {
                    continue;
                }
                
                $name   = '';
                $config = [];
                if (is_file($configFile = $path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php')) {
                    $config = include $configFile;
                    $config = is_array($config) ? $config : [];
                    $name   = $config['app_name'] ?? '';
                }
                
                $list[] = [
                    'path'   => $path,
                    'dir'    => $value,
                    'name'   => $name ?: (($maps[$value] ?? '') ?: $value),
                    'config' => $config
                ];
            }
        }
        
        
        return $list;
    }
    
    
    /**
     * 获取客户端名称
     * @param string $client
     * @return string
     */
    public static function getName(string $client) : string
    {
        if (!isset($appList)) {
            $appList = ArrayHelper::listByKey(static::getList(), 'dir');
        }
        
        return $client == self::CLI_CLIENT_KEY ? self::CLI_CLIENT_NAME : ($appList[$client]['name'] ?? $client);
    }
    
    
    /**
     * 获取客户端标识
     * @return string
     */
    public static function getClient() : string
    {
        $app = App::getInstance();
        
        return $app->runningInConsole() ? self::CLI_CLIENT_KEY : $app->getDirName();
    }
    
    
    /**
     * 剔除controller后缀
     * @param string $value
     * @return string
     */
    public static function trimController(string $value) : string
    {
        if (strtolower((string) substr($value, -10)) === 'controller') {
            $value = substr($value, 0, -10);
        }
        
        return StringHelper::snake($value);
    }
}