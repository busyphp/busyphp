<?php

namespace BusyPHP\helper;

use Closure;
use JsonSerializable;
use think\Collection;
use think\contract\Arrayable;
use think\contract\Jsonable;

/**
 * 数据转换辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午上午10:18 TransHelper.php $
 */
class TransHelper
{
    /**
     * 将手机号的中间值变*
     * @param string $phone
     * @return string
     */
    public static function safePhone(string $phone) : string
    {
        $first = (string) substr($phone, 0, 3);
        $last  = (string) substr($phone, -4);
        
        return "{$first}****{$last}";
    }
    
    
    /**
     * 格式化时间
     * @param int    $time 时间戳
     * @param string $format 格式
     * @return false|string
     */
    public static function date($time = 0, $format = 'Y-m-d H:i:s')
    {
        return date($format, $time);
    }
    
    
    /**
     * 格式化 GM DATE
     * @param int    $time 时间戳
     * @param string $format 格式
     * @param string $suffix 后缀
     * @return false|string
     */
    public static function gmDate($time = 0, $format = 'D, d M Y H:i:s', $suffix = ' GMT')
    {
        return gmdate($format, $time) . $suffix;
    }
    
    
    /**
     * 将UTF-8内容转换成GBK字符集
     * @param string $content 字符串
     * @return string|false
     */
    public static function UTF8ToGB2312($content = '')
    {
        return iconv("UTF-8", "GB2312", $content);
    }
    
    
    /**
     * 将GBK内容转换成UTF-8字符集
     * @param string $content 字符串
     * @return string|false
     */
    public static function GB2312ToUTF8($content = '')
    {
        return iconv("GB2312", "UTF-8", $content);
    }
    
    
    /**
     * 将数据转换成布尔类型的0或者1
     * @param mixed $content 要转换的内容
     * @return int
     */
    public static function toBoolInt($content = null) : int
    {
        return empty($content) ? 0 : 1;
    }
    
    
    /**
     * 强制将数据转换成BOOL类型
     * @param mixed $content 要转换的内容
     * @return bool
     */
    public static function toBool($content = null) : bool
    {
        if (is_string($content)) {
            $content = strtolower(trim($content));
            
            if ($content === 'true') {
                return true;
            } elseif ($content === 'false') {
                return false;
            }
        }
        
        return empty($content) ? false : true;
    }
    
    
    /**
     * 将数组转换成option标签
     * @param array        $list 要转换的数组
     * @param string|array $selected 选中项值
     * @param string       $nameKey 选项文本键名称
     * @param string       $valueKey 选项值键名称
     * @param array        $attrs 自定属性键值对
     * @return string
     */
    public static function toOptionHtml(array $list, $selected = null, ?string $nameKey = '', ?string $valueKey = '', ?array $attrs = []) : string
    {
        $selected = !is_array($selected) ? [$selected] : $selected;
        $nameKey  = $nameKey ?: '';
        $valueKey = $valueKey ?: '';
        $attrs    = $attrs ?: [];
        
        $options = '';
        foreach ($list as $index => $item) {
            $value = (string) (empty($valueKey) ? $index : ($item[$valueKey] ?? ''));
            $text  = (string) (empty($nameKey) ? $item : ($item[$nameKey] ?? ''));
            
            $current = '';
            if (in_array($value, $selected)) {
                $current = ' selected';
            }
            
            // 自定义属性
            $attrString = '';
            if (is_array($attrs) && $attrs) {
                foreach ($attrs as $attrName => $attrValue) {
                    if ($attrValue instanceof Closure) {
                        $attrValue = $attrValue($item);
                    } else {
                        $attrValue = $item[$attrValue] ?? null;
                    }
                    
                    if (is_bool($attrValue)) {
                        $attrValue = $attrValue ? 'true' : 'false';
                    } elseif (is_array($attrValue)) {
                        $attrValue = json_encode($attrValue, JSON_UNESCAPED_UNICODE);
                    } elseif ($attrValue instanceof Jsonable || $attrValue instanceof JsonSerializable || $attrValue instanceof Collection) {
                        $attrValue = json_encode($attrValue, JSON_UNESCAPED_UNICODE);
                    } elseif ($attrValue instanceof Arrayable) {
                        $attrValue = json_encode($attrValue->toArray(), JSON_UNESCAPED_UNICODE);
                    } elseif (is_scalar($attrValue)) {
                        $attrValue = (string) $attrValue;
                    } else {
                        continue;
                    }
                    
                    $attrString .= " {$attrName}='{$attrValue}'";
                }
            }
            $options .= '<option value="' . $value . '"' . $attrString . $current . '>' . $text . '</option>';
        }
        
        return $options;
    }
    
    
    /**
     * 自动转换字符集，支持数组转换
     * @param string|array $content 要转换的字符串
     * @param string       $from 当前字符串的编码， 默认gbk
     * @param string       $to 目标字符串的编码，默认utf-8
     * @return array|string
     */
    public static function charset($content, $from = 'gbk', $to = 'utf-8')
    {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to   = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        
        // 如果编码相同或者非字符串标量则不转换
        if (strtoupper($from) === strtoupper($to) || empty($content) || (is_scalar($content) && !is_string($content))) {
            return $content;
        }
        
        
        if (is_string($content)) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($content, $to, $from);
            } elseif (function_exists('iconv')) {
                return iconv($from, $to, $content);
            } else {
                return $content;
            }
        } elseif (is_array($content)) {
            foreach ($content as $key => $val) {
                $_key           = self::charset($key, $from, $to);
                $content[$_key] = self::charset($val, $from, $to);
                if ($key != $_key) {
                    unset($content[$key]);
                }
            }
            
            return $content;
        } else {
            return $content;
        }
    }
    
    
    /**
     * 将UBB代码转换成HTML
     * @param $content
     * @return string
     */
    function ubbToHtml($content)
    {
        $content = trim($content);
        //$Text=htmlspecialchars($Text);
        $content = preg_replace("/\\t/is", "  ", $content);
        $content = preg_replace("/\[h1\](.+?)\[\/h1\]/is", "<h1>\\1</h1>", $content);
        $content = preg_replace("/\[h2\](.+?)\[\/h2\]/is", "<h2>\\1</h2>", $content);
        $content = preg_replace("/\[h3\](.+?)\[\/h3\]/is", "<h3>\\1</h3>", $content);
        $content = preg_replace("/\[h4\](.+?)\[\/h4\]/is", "<h4>\\1</h4>", $content);
        $content = preg_replace("/\[h5\](.+?)\[\/h5\]/is", "<h5>\\1</h5>", $content);
        $content = preg_replace("/\[h6\](.+?)\[\/h6\]/is", "<h6>\\1</h6>", $content);
        $content = preg_replace("/\[separator\]/is", "", $content);
        $content = preg_replace("/\[center\](.+?)\[\/center\]/is", "<center>\\1</center>", $content);
        $content = preg_replace("/\[url=http:\/\/([^\[]*)\](.+?)\[\/url\]/is", "<a href=\"http://\\1\" target=_blank>\\2</a>", $content);
        $content = preg_replace("/\[url=([^\[]*)\](.+?)\[\/url\]/is", "<a href=\"http://\\1\" target=_blank>\\2</a>", $content);
        $content = preg_replace("/\[url\]http:\/\/([^\[]*)\[\/url\]/is", "<a href=\"http://\\1\" target=_blank>\\1</a>", $content);
        $content = preg_replace("/\[url\]([^\[]*)\[\/url\]/is", "<a href=\"\\1\" target=_blank>\\1</a>", $content);
        $content = preg_replace("/\[img\](.+?)\[\/img\]/is", "<img src=\\1>", $content);
        $content = preg_replace("/\[color=(.+?)\](.+?)\[\/color\]/is", "<font color=\\1>\\2</font>", $content);
        $content = preg_replace("/\[size=(.+?)\](.+?)\[\/size\]/is", "<font size=\\1>\\2</font>", $content);
        $content = preg_replace("/\[sup\](.+?)\[\/sup\]/is", "<sup>\\1</sup>", $content);
        $content = preg_replace("/\[sub\](.+?)\[\/sub\]/is", "<sub>\\1</sub>", $content);
        $content = preg_replace("/\[pre\](.+?)\[\/pre\]/is", "<pre>\\1</pre>", $content);
        $content = preg_replace("/\[email\](.+?)\[\/email\]/is", "<a href='mailto:\\1'>\\1</a>", $content);
        $content = preg_replace("/\[colorTxt\](.+?)\[\/colorTxt\]/eis", "color_txt('\\1')", $content);
        $content = preg_replace("/\[emot\](.+?)\[\/emot\]/eis", "emot('\\1')", $content);
        $content = preg_replace("/\[i\](.+?)\[\/i\]/is", "<i>\\1</i>", $content);
        $content = preg_replace("/\[u\](.+?)\[\/u\]/is", "<u>\\1</u>", $content);
        $content = preg_replace("/\[b\](.+?)\[\/b\]/is", "<b>\\1</b>", $content);
        $content = preg_replace("/\[quote\](.+?)\[\/quote\]/is", " <div class='quote'><h5>引用:</h5><blockquote>\\1</blockquote></div>", $content);
        $content = preg_replace("/\[code\](.+?)\[\/code\]/eis", "highlight_code('\\1')", $content);
        $content = preg_replace("/\[php\](.+?)\[\/php\]/eis", "highlight_code('\\1')", $content);
        $content = preg_replace("/\[sig\](.+?)\[\/sig\]/is", "<div class='sign'>\\1</div>", $content);
        $content = preg_replace("/\\n/is", "<br/>", $content);
        
        return $content;
    }
    
    
    /**
     * 将任意值转换成Hash
     * @param string
     * @return string 大写
     */
    public static function createHash()
    {
        $args = func_get_args();
        $str  = '';
        foreach ($args as $arg) {
            $arg = str_replace(["\r", "\n", "\t", " "], '', $arg);
            $arg = strtoupper(trim($arg));
            $str .= $arg . ';';
        }
        
        return strtoupper(md5($str));
    }
    
    
    /**
     * 将字符串转换成base64格式便于在URL中携带
     * @param string $string
     * @return string
     */
    public static function base64encodeUrl(string $string) : string
    {
        $string = base64_encode(trim($string));
        $string = str_replace('+', '_', $string);
        $string = str_replace('/', '-', $string);
        $string = str_replace('=', '', $string);
        
        return $string;
    }
    
    
    /**
     * 将URL中的base64编码转换解码
     * @param string $string
     * @return string
     */
    public static function base64decodeUrl(string $string) : string
    {
        $string = trim($string);
        $string = str_replace('_', '+', $string);
        $string = str_replace('-', '/', $string);
        
        return base64_decode($string);
    }
    
    
    /**
     * 将字节转换成带单位的值
     * @param int  $bytes 要转换的字节 单位 bytes
     * @param bool $returnArray 是否返回数组
     * @return string|array 带单位的值
     */
    public static function formatBytes($bytes, $returnArray = false)
    {
        $array = ["B", "KB", "MB", "GB", "TB", "PB"];
        $pos   = 0;
        while ($bytes >= 1024) {
            $bytes /= 1024;
            $pos++;
        }
        
        
        $number = round($bytes, 2);
        if ($returnArray) {
            return [
                'number' => $number,
                'unit'   => $array[$pos]
            ];
        } else {
            return $number . ' ' . $array[$pos];
        }
    }
    
    
    /**
     * 将金额格式化成 0.00 格式
     * @param float $money 要格式化的金额
     * @param int   $length 保留小数长度，默认2个
     * @return int
     */
    public static function formatMoney($money = 0.00, $length = 2)
    {
        return number_format($money, $length, '.', '');
    }
    
    
    /**
     * XML编码
     * @param mixed  $data 数据
     * @param string $encoding 数据编码
     * @param string $root 根节点名
     * @return string
     */
    public static function xmlEncode($data, $encoding = 'utf-8', $root = 'think')
    {
        $xml = '<?xml version="1.0" encoding="' . $encoding . '"?>';
        $xml .= '<' . $root . '>';
        $xml .= static::toXml($data);
        $xml .= '</' . $root . '>';
        
        return $xml;
    }
    
    
    /**
     * 数据XML编码
     * @param array $data 数据
     * @return string
     */
    public static function toXml(array $data) : string
    {
        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml .= "<$key>";
            $xml .= (is_array($val) || is_object($val)) ? self::toXml($val) : $val;
            [$key,] = explode(' ', $key);
            $xml .= "</$key>";
        }
        
        return $xml;
    }
}