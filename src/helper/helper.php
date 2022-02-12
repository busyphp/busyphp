<?php
// +----------------------------------------------------
// + 公共助手函数库
// +----------------------------------------------------

use BusyPHP\App;
use BusyPHP\exception\VerifyException;
use BusyPHP\file\Captcha;
use BusyPHP\file\captcha\CaptchaUrl;
use BusyPHP\file\qrcode\QRCodeUrl;
use BusyPHP\file\image\ThumbUrl;
use BusyPHP\Request;
use think\Container;

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

if (!function_exists('thumb_url')) {
    /**
     * 生成缩图URL
     * @param string $url 图片地址
     * @param string $size 尺寸配置
     * @param string $type 缩图类型
     * @return ThumbUrl
     */
    function thumb_url($url, $size, $type = BusyPHP\file\Image::THUMB_CORP) : ThumbUrl
    {
        return BusyPHP\facade\ThumbUrl::url($url)->type($type)->size($size);
    }
}


if (!function_exists('qr_code_url')) {
    /**
     * 生成二维码URL
     * @param string $text 二维码内容
     * @param string $logo 自定义LOGO URL 相对于根目录
     * @return QRCodeUrl
     */
    function qr_code_url($text, $logo = '') : QRCodeUrl
    {
        return BusyPHP\facade\QRCodeUrl::text($text)->logo($logo);
    }
}


if (!function_exists('captcha_url')) {
    /**
     * 生成验证码URL
     * @param string $key 验证码标识
     * @param int    $width 宽度
     * @param int    $height 高度
     * @return CaptchaUrl
     */
    function captcha_url($key, $width = 0, $height = 0) : CaptchaUrl
    {
        return \BusyPHP\facade\CaptchaUrl::key($key)->width($width)->height($height);
    }
}

if (!function_exists('captcha_check')) {
    /**
     * 检测验证码
     * @param string $code 验证码内容
     * @param string $key 验证码标识
     * @throws VerifyException
     */
    function captcha_check($code, string $key)
    {
        (new Captcha(App::getInstance()->getDirName()))->check($code, $key);
    }
}

if (!function_exists('captcha_clear')) {
    /**
     * 清空验证码
     * @param string $key 验证码标识
     */
    function captcha_clear(string $key)
    {
        (new Captcha(App::getInstance()->getDirName()))->clear($key);
    }
}

if (!function_exists('app')) {
    /**
     * 快速获取容器中的实例 支持依赖注入
     * @param string $name 类名或标识 默认获取当前应用实例
     * @param array  $args 参数
     * @param bool   $newInstance 是否每次创建新的实例
     * @return mixed|App
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
        return app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . ($path ? ltrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : $path);
    }
}