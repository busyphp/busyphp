<?php

namespace BusyPHP\helper\util;

/**
 * 字符串相关处理类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/6 下午9:45 上午 Str.php $
 */
class Str extends \think\helper\Str
{
    /**
     * 替换变量{@xxx}
     * @param string       $content 要解析的字符串
     * @param array|string $name 变量对应的名称或数组
     * @param string       $value 如果$var为名称的话，这里就是变量对应的值
     * @return string 替换后的字符串
     */
    public static function parseAtVar(string $content, $name = [], $value = null) : string
    {
        if (!is_array($name) && $name) {
            $name = array($name => $value);
        }
        
        return preg_replace_callback('/\{@(.*?)\}/', function($array) use ($name) {
            return $name[$array[1]] ?? '';
        }, $content);
    }
    
    
    /**
     * 计算字符串长度，支持中文为一个文字算一个
     * @param string $string 要计算的字符串
     * @return int
     */
    public static function count(string $string = '') : int
    {
        if (!$string) {
            return 0;
        }
        if (function_exists('mb_strlen')) {
            return mb_strlen($string, 'utf-8');
        } else {
            preg_match_all("/./u", $string, $array);
            
            return count($array[0]);
        }
    }
    
    
    /**
     * 字符串截取，支持中文和其他编码
     * @param string $content 字符串
     * @param int    $length 长度
     * @param bool   $suffix 如果超出长度是否返回后缀
     * @param int    $start 起始截取点
     * @param string $charset 字符集，默认位utf-8
     * @return string
     */
    public static function cut($content, $length, $suffix = true, $start = 0, $charset = 'utf-8')
    {
        if (self::count($content) <= $length) {
            return $content;
        }
        
        if (function_exists("mb_substr")) {
            $slice = mb_substr($content, $start, $length, $charset);
        } elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($content, $start, $length, $charset);
            if (false === $slice) {
                $slice = '';
            }
        } else {
            $re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $content, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }
        
        return $suffix ? $slice . '...' : $slice;
    }
    
    
    /**
     * 简介截取
     * @param string $string 要截取的字符串
     * @param int    $length 截取的字符串长度，默认140个
     * @return string
     */
    public static function cutDesc($string = '', $length = 140)
    {
        // 包含HTML则去除HTML
        if (false !== stripos($string, '<') && false !== stripos($string, '</')) {
            $string = strip_tags($string);
        }
        
        $string = str_replace(array('&nbsp;', '&#160;', '&#xA0;', '　'), '', $string);
        $string = str_replace(array('&quot;', '&#34;', '&#x22;'), '"', $string);
        $string = str_replace(array('&apos;', '&#039;'), '\'', $string);
        $string = str_replace(array("\r\n", "\r", "\n", "\t"), '', $string);
        $string = str_replace(array('&amp;', '&#38;', '&#x26;'), '&', $string);
        $string = self::cut($string, $length, false);
        $string = str_replace('&', '&amp;', $string);
        
        return str_replace(array('\'', '"'), array('&apos;', '&quot;'), $string);
    }
    
    
    /**
     * 生成UUID 单机使用
     * @return string
     */
    public static function uuid()
    {
        $charId = md5(uniqid(mt_rand(), true));
        $hyphen = chr(45);
        $uuid   = substr($charId, 0, 8) . $hyphen . substr($charId, 8, 4) . $hyphen . substr($charId, 12, 4) . $hyphen . substr($charId, 16, 4) . $hyphen . substr($charId, 20, 12);
        
        return $uuid;
    }
}