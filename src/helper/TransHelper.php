<?php

namespace BusyPHP\helper;

/**
 * 数据转换辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午上午10:18 TransHelper.php $
 */
class TransHelper
{
    /**
     * 转换时间范围条件，用于按照时间搜索的时候使用
     * @param array  $data 包含时间范围字段的数据
     * @param string $startField 开始时间字段，默认start_time
     * @param string $endField 结束时间字段，默认end_time
     * @param string $defStart 默认开始时间，默认为今天的0点开始，精确到秒
     * @param string $defEnd 默认结束时间，默认为今天的23:59结束，精确到秒
     * @return array
     */
    public static function parseTimeRangeCondition($data, $startField = '', $endField = '', $defStart = '', $defEnd = '')
    {
        $startField = $startField ?: 'start_time';
        $endField   = $endField ?: 'end_time';
        $defStart   = $defStart ?: self::date(strtotime(date('Y-m-d')));
        $defEnd     = $defEnd ?: self::date(strtotime(date('Y-m-d 23:59:59')));
        if (!isset($data[$startField])) {
            $data[$startField] = $defStart;
        }
        if (!isset($data[$endField])) {
            $data[$endField] = $defEnd;
        }
        
        $condition = [];
        if ($data[$startField] && $data[$endField]) {
            $condition = [
                ['egt', strtotime($data[$startField])],
                ['elt', strtotime($data[$endField])],
                'AND'
            ];
        } elseif ($data[$startField] && !$data[$endField]) {
            $condition = ['egt', strtotime($data[$startField])];
        } elseif ($data[$endField] && !$data[$startField]) {
            $condition = ['elt', strtotime($data[$endField])];
        }
        
        return $condition;
    }
    
    
    /**
     * 将手机号的中间值变*
     * @param $phone
     * @return string
     */
    public static function safePhone($phone)
    {
        $first = substr($phone, 0, 3);
        $last  = substr($phone, -4);
        
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
     * 将UTF-8内容转换成GBK字符集
     * @param string $content 字符串
     * @return string
     */
    public static function UTF8ToGB2312($content = '')
    {
        return iconv("UTF-8", "GB2312", $content);
    }
    
    
    /**
     * 将GBK内容转换成UTF-8字符集
     * @param string $content 字符串
     * @return string
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
    public static function boolToNumber($content = null)
    {
        return empty($content) ? 0 : 1;
    }
    
    
    /**
     * 强制将数据转换成BOOL类型
     * @param null $content
     * @return bool
     */
    public static function dataToBool($content = null)
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
     * @param array        $array 要转换的数组
     * @param string       $value option value 属性值的 数组键名，设置__index 代表取键名为参数
     * @param string       $name option text 显示值的 数组键名，设置__index 代表取item为参数
     * @param string|array $selectedValue 选中项的值 value，多个用半角逗号分割
     * @param array        $attr 属性配置，格式 array(name => key)
     * @return string
     */
    public static function arrayToOption($array, $value = '', $name = '', $selectedValue = null, $attr = [])
    {
        $defaultKey    = '__index';
        $value         = $value ?: $defaultKey;
        $name          = $name ?: $defaultKey;
        $value         = (string) $value;
        $name          = (string) $name;
        $string        = '';
        $selectedValue = !is_null($selectedValue) ? is_array($selectedValue) ? $selectedValue : explode(',', $selectedValue) : null;
        foreach ($array as $index => $item) {
            $current     = '';
            $optionValue = $value == $defaultKey ? $index : $item[$value];
            $optionName  = $name == $defaultKey ? $item : $item[$name];
            if (!is_null($selectedValue) && in_array($optionValue, $selectedValue)) {
                $current = ' selected';
            }
            
            $attrString = '';
            if ($attr) {
                foreach ($attr as $attrName => $attrValue) {
                    $attrValue  = $attrValue ?: $defaultKey;
                    $attrValue  = $attrValue == $defaultKey ? $index : $item[$attrValue];
                    $attrString .= " data-{$attrName}=\"{$attrValue}\"";
                }
            }
            $string .= '<option value="' . $optionValue . '"' . $attrString . $current . '>' . $optionName . '</option>';
        }
        
        return $string;
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
     * @param $string
     * @return string
     */
    public static function base64encodeUrl($string)
    {
        $string = base64_encode(trim($string));
        $string = str_replace('+', '_', $string);
        $string = str_replace('/', '-', $string);
        $string = str_replace('=', '', $string);
        
        return $string;
    }
    
    
    /**
     * 将URL中的base64编码转换解码
     * @param $string
     * @return string
     */
    public static function base64decodeUrl($string)
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
        $xml .= self::dataToXml($data);
        $xml .= '</' . $root . '>';
        
        return $xml;
    }
    
    
    /**
     * 数据XML编码
     * @param mixed $data 数据
     * @return string
     */
    public static function dataToXml($data)
    {
        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml .= "<$key>";
            $xml .= (is_array($val) || is_object($val)) ? self::dataToXml($val) : $val;
            [$key,] = explode(' ', $key);
            $xml .= "</$key>";
        }
        
        return $xml;
    }
}