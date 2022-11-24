<?php
declare(strict_types = 1);

namespace BusyPHP\helper;

/**
 * 数据过滤辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午上午10:19 FilterHelper.php $
 */
class FilterHelper
{
    /**
     * 保留最小值
     * @param int|float $number
     * @param int|float $default 默认最小值
     * @return int|float
     * @deprecated 请使用 {@see max()}
     */
    public static function min($number, $default = 0)
    {
        return max($number, $default);
    }
    
    
    /**
     * 保留最大值
     * @param int|float $number
     * @param int|float $default 默认最大值
     * @return int|float
     * @deprecated 请使用 {@see min()}
     */
    public static function max($number, $default = 0)
    {
        return min($number, $default);
    }
    
    
    /**
     * 格式化字符串
     * @param string       $string
     * @param string       $symbol 保留的符号
     * @param string|array $filterSymbol 移除的符号
     * @return string
     */
    public static function formatString($string, $symbol = PHP_EOL, $filterSymbol = null) : string
    {
        if (!is_null($filterSymbol)) {
            $string = str_replace($filterSymbol, '', $string);
        }
        
        $string = explode($symbol, $string);
        $string = FilterHelper::trim($string);
        $string = array_filter($string);
        $string = array_unique($string);
        $string = implode($symbol, $string);
        
        return $string;
    }
    
    
    /**
     * 移除数组中的重复值和空值
     * @param array $array
     * @param bool  $isRest 是否重置键
     * @return array
     */
    public static function trimArray($array, $isRest = false) : array
    {
        $array = is_array($array) ? $array : [];
        $array = array_map(function($str) {
            return trim((string) $str);
        }, $array);
        $array = array_filter($array);
        $array = array_unique($array);
        
        if ($isRest) {
            $array = array_values($array);
        }
        
        return $array;
    }
    
    
    /**
     * 批量对数据去左右空格，支持数组递归
     * @param array|string $data 要操作的字符串或者数组
     * @return array|string
     */
    public static function trim($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::trim($value);
            }
            
            return $data;
        } elseif (is_string($data)) {
            return trim((string) $data);
        } else {
            return $data;
        }
    }
    
    
    /**
     * 过滤字符串，保持单行
     * @param string $string 要过滤的字符串
     * @return string
     */
    public static function nowrap($string) : string
    {
        return self::safeString(preg_replace("/(\015\012)|(\015)|(\012)/", '', $string));
    }
    
    
    /**
     * 字符串过滤，移除HTML，替换单双引号
     * @param string $content
     * @return string
     */
    public static function safeString($content = '') : string
    {
        return str_replace(['"', "'"], ['&quot;', '&#039;'], strip_tags($content));
    }
    
    
    /**
     * 过滤搜索关键词，去除左右空格，转义：%，_，符号
     * @param string $string 关键词
     * @param bool   $replaceSpace 遇到空格是否替换成%号，默认替换
     * @return string
     */
    public static function searchWord($string, $replaceSpace = true) : string
    {
        $string = trim((string) $string);
        if (!$string) {
            return $string;
        }
        $string = self::nowrap($string);
        $string = str_replace(['%', '_'], ['\%', '\_'], $string);
        $array  = ["\t", "\r\n", "\r", "\n"];
        if ($replaceSpace) {
            $array[] = ' ';
        }
        
        return str_replace($array, '%', $string);
    }
    
    
    /**
     * XXS过滤
     * @param string $content 要过滤的内容
     * @return string
     */
    public static function xxs($content)
    {
        // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
        // this prevents some character re-spacing such as <java\0script>
        // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
        $content = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $content);
        
        // straight replacements, the user should never need these since they're normal characters
        // this prevents like <IMG SRC=@avascript:alert('XSS')>
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
            
            // @ @ search for the hex values
            $content = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $content); // with a ;
            // @ @ 0{0,7} matches '0' zero to seven times
            $content = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $content);              // with a ;
        }
        
        // now the only remaining whitespace attacks are \t, \n, and \r
        $raString = 'javascript|vbscript|expression|applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base|onabort|onactivate|onafterprint|onafterupdate|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeeditfocus|onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onblur|onbounce|oncellchange|onchange|onclick|oncontextmenu|oncontrolselect|oncopy|oncut|ondataavailable|ondatasetchanged|ondatasetcomplete|ondblclick|ondeactivate|ondrag|ondragend|ondragenter|ondragleave|ondragover|ondragstart|ondrop|onerror|onerrorupdate|onfilterchange|onfinish|onfocus|onfocusin|onfocusout|onhelp|onkeydown|onkeypress|onkeyup|onlayoutcomplete|onload|onlosecapture|onmousedown|onmouseenter|onmouseleave|onmousemove|onmouseout|onmouseover|onmouseup|onmousewheel|onmove|onmoveend|onmovestart|onpaste|onpropertychange|onreadystatechange|onreset|onresize|onresizeend|onresizestart|onrowenter|onrowexit|onrowsdelete|onrowsinserted|onscroll|onselect|onselectionchange|onselectstart|onstart|onstop|onsubmit|onunload';
        $ra       = explode('|', $raString);
        $found    = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $content;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern     .= '/i';
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
                $content     = preg_replace($pattern, $replacement, $content);     // filter out the hex tags
                if ($val_before == $content) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }
        
        return $content;
    }
    
    
    /**
     * 输出安全的HTML代码
     * @param string $content
     * @param string $tags 允许的HTML标签，默认允许：table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a
     * @return string
     */
    public static function safeHtml($content, $tags = null)
    {
        $content = trim((string) $content);
        //完全过滤注释
        $content = preg_replace('/<!--?.*-->/', '', $content);
        //完全过滤动态代码
        $content = preg_replace('/<\?|\?' . '>/', '', $content);
        //完全过滤js
        $content = preg_replace('/<script?.*\/script>/', '', $content);
        $content = str_replace('[', '&#091;', $content);
        $content = str_replace(']', '&#093;', $content);
        $content = str_replace('|', '&#124;', $content);
        //过滤换行符
        $content = preg_replace('/\r?\n/', '', $content);
        //br
        $content = preg_replace('/<br(\s\/)?' . '>/i', '[br]', $content);
        $content = preg_replace('/(\[br\]\s*){10,}/i', '[br]', $content);
        //过滤危险的属性，如：过滤on事件lang js
        while (preg_match('/(<[^><]+)( lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i', $content, $mat)) {
            $content = str_replace($mat[0], $mat[1], $content);
        }
        while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $content, $mat)) {
            $content = str_replace($mat[0], $mat[1] . $mat[3], $content);
        }
        if (empty($tags)) {
            $tags = 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a';
        }
        //允许的HTML标签
        $content = preg_replace('/<(' . $tags . ')( [^><\[\]]*)>/i', '[\1\2]', $content);
        $content = preg_replace('/<\/(' . $tags . ')>/Ui', '[/\1]', $content);
        //过滤多余html
        $content = preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml)[^><]*>/i', '', $content);
        //过滤合法的html标签
        while (preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i', $content, $mat)) {
            $content = str_replace($mat[0], str_replace('>', ']', str_replace('<', '[', $mat[0])), $content);
        }
        //转换引号
        while (preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i', $content, $mat)) {
            $content = str_replace($mat[0], $mat[1] . '|' . $mat[3] . '|' . $mat[4], $content);
        }
        //过滤错误的单个引号
        while (preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i', $content, $mat)) {
            $content = str_replace($mat[0], str_replace($mat[1], '', $mat[0]), $content);
        }
        //转换其它所有不合法的 < >
        $content = str_replace('<', '&lt;', $content);
        $content = str_replace('>', '&gt;', $content);
        $content = str_replace('"', '&quot;', $content);
        //反转换
        $content = str_replace('[', '<', $content);
        $content = str_replace(']', '>', $content);
        $content = str_replace('|', '"', $content);
        //过滤多余空格
        $content = str_replace('  ', ' ', $content);
        
        return $content;
    }
}