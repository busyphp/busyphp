<?php
// +----------------------------------------------------
// + 公共助手函数库
// +----------------------------------------------------

use BusyPHP\App;
use think\Container;
use think\Request;

if (!function_exists('is_mobile')) {
    /**
     * 是否移动端
     * @return bool
     */
    function is_mobile() : bool
    {
        return App::getInstance()->request->isMobile();
    }
}

if (!function_exists('is_android')) {
    /**
     * 是否安卓端
     * @param string $ua 自定义UA
     * @return bool
     */
    function is_android($ua = '') : bool
    {
        $ua = $ua ?: App::getInstance()->request->server('HTTP_USER_AGENT');
        
        return stripos($ua, 'android') !== false;
    }
}


if (!function_exists('is_ios')) {
    /**
     * 是否苹果端
     * @param string $ua 自定义UA
     * @return bool
     */
    function is_ios($ua = '') : bool
    {
        $ua = $ua ?: App::getInstance()->request->server('HTTP_USER_AGENT');
        
        return stripos($ua, 'iphone') !== false || stripos($ua, 'ipad');
    }
}


if (!function_exists('is_wechat_client')) {
    /**
     * 判断是否微信端
     * @param string $ua 自定义UA
     * @return bool
     */
    function is_wechat_client($ua = '') : bool
    {
        $ua = $ua ?: App::getInstance()->request->server('HTTP_USER_AGENT');
        
        return strpos($ua, 'MicroMessenger') !== false;
    }
}


if (!function_exists('is_checked')) {
    /**
     * 判断是否checkbox或radio选中项
     * @param bool $condition 条件
     * @return string
     */
    function is_checked($condition) : string
    {
        return $condition ? ' checked' : '';
    }
}


if (!function_exists('is_selected')) {
    /**
     * 判断是否option选中项
     * @param bool $condition 条件
     * @return string
     */
    function is_selected($condition) : string
    {
        return $condition ? ' selected' : '';
    }
}


if (!function_exists('is_disabled')) {
    /**
     * 判断是否禁用
     * @param bool $condition 条件
     * @return string
     */
    function is_disabled($condition) : string
    {
        return $condition ? ' disabled' : '';
    }
}


if (!function_exists('is_readonly')) {
    /**
     * 判断是否只读
     * @param bool $condition 条件
     * @return string
     */
    function is_readonly($condition) : string
    {
        return $condition ? ' readonly' : '';
    }
}

if (!function_exists('app')) {
    /**
     * 快速获取容器中的实例 支持依赖注入
     * @template T
     * @param string|class-string<T> $name 类名或标识 默认获取当前应用实例
     * @param array                  $args 参数
     * @param bool                   $newInstance 是否每次创建新的实例
     * @return T|object|App
     */
    function app(string $name = '', array $args = [], bool $newInstance = false)
    {
        return Container::getInstance()->make($name ?: App::class, $args, $newInstance);
    }
}

if (!function_exists('request')) {
    /**
     * 获取当前Request对象实例
     * @return Request
     */
    function request() : Request
    {
        return app('request');
    }
}

if (!function_exists('public_path')) {
    /**
     * 获取web根目录
     * @param string $path
     * @return string
     */
    function public_path($path = '')
    {
        return app()->getPublicPath() . ($path ? ltrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : $path);
    }
}