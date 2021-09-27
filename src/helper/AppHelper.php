<?php

namespace BusyPHP\helper;

use BusyPHP\App;
use BusyPHP\contract\structs\items\AppListItem;

/**
 * 应用辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/26 下午上午9:54 AppHelper.php $
 */
class AppHelper
{
    /**
     * 获取应用目录名称
     * @return string
     */
    public static function getDirName() : string
    {
        return pathinfo(App::getInstance()->getAppPath(), PATHINFO_FILENAME);
    }
    
    
    /**
     * 获取应用集合
     * @return AppListItem[]
     */
    public static function getList() : array
    {
        $app      = App::getInstance();
        $basePath = $app->getBasePath();
        $list     = [];
        $maps     = ['admin' => '后台管理', 'home' => '前端网站'];
        foreach (scandir($basePath) as $value) {
            if (!is_dir($path = $basePath . $value . DIRECTORY_SEPARATOR) || $value === '.' || $value === '..') {
                continue;
            }
            
            $name   = '';
            $config = [];
            if (is_file($configFile = $path . 'config' . DIRECTORY_SEPARATOR . 'app.php')) {
                $config = include $configFile;
                $config = is_array($config) ? $config : [];
                $name   = $config['app_name'] ?? '';
            }
            if (!$name) {
                $name = ($maps[$value] ?? '') ?: $value;
            }
            
            $item         = new AppListItem();
            $item->path   = $path;
            $item->dir    = $value;
            $item->name   = $name;
            $item->config = $config;
            
            $list[] = $item;
        }
        
        return $list;
    }
}