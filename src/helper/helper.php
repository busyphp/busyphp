<?php
// +----------------------------------------------------
// + 公共助手函数库
// +----------------------------------------------------

use BusyPHP\file\qrcode\QRCodeUrl;
use BusyPHP\file\image\ThumbUrl;

if (!function_exists('is_android')) {
    /**
     * 是否安卓端
     * @param string $ua 自定义UA
     * @return bool
     */
    function is_android($ua = '') : bool
    {
        $ua = $ua ?: $_SERVER['HTTP_USER_AGENT'];
        
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
        $ua = $ua ?: $_SERVER['HTTP_USER_AGENT'];
        
        return stripos($ua, 'iphone') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'ipad');
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
        $ua = $ua ?: $_SERVER['HTTP_USER_AGENT'];
        
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