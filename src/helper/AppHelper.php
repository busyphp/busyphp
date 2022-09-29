<?php

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
     * @param string $clientDir
     * @return string
     */
    public static function getName(string $clientDir) : string
    {
        if (!isset($appList)) {
            $appList = ArrayHelper::listByKey(static::getList(), 'dir');
        }
        
        return $clientDir == self::CLI_CLIENT_KEY ? self::CLI_CLIENT_NAME : ($appList[$clientDir]['name'] ?? $clientDir);
    }
}