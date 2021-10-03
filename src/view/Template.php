<?php
declare (strict_types = 1);

namespace BusyPHP\view;

use BusyPHP\App;
use Exception;

/**
 * 模板引擎
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/5 下午5:20 下午 Template.php $
 */
class Template extends \think\Template
{
    /**
     * 保留内容信息
     * @var array
     */
    private $literal = [];
    
    /**
     * 模板包含信息
     * @var array
     */
    private $includeFile = [];
    
    
    /**
     * 渲染模板文件
     * @access public
     * @param string $template 模板文件
     * @param array  $vars 模板变量
     * @return void
     */
    public function fetch(string $template, array $vars = []) : void
    {
        if ($vars) {
            $this->data = array_merge($this->data, $vars);
        }
        
        if (!empty($this->config['cache_id']) && $this->config['display_cache'] && $this->cache) {
            // 读取渲染缓存
            if ($this->cache->has($this->config['cache_id'])) {
                echo $this->cache->get($this->config['cache_id']);
                
                return;
            }
        }
        
        $template = $this->parseTemplateFile($template);
        
        if ($template) {
            $cacheFile = $this->config['cache_path'] . $this->config['cache_prefix'] . md5($this->config['layout_on'] . $this->config['layout_name'] . $template) . '.' . ltrim($this->config['cache_suffix'], '.');
            
            if (!$this->checkCache($cacheFile)) {
                // 缓存无效 重新模板编译
                $content = file_get_contents($template);
                $this->compiler($content, $cacheFile);
            }
            
            // 页面缓存
            ob_start();
            ob_implicit_flush(0);
            
            // 读取编译存储
            $this->storage->read($cacheFile, $this->data);
            
            // 获取并清空缓存
            $content = ob_get_clean();
            
            if (!empty($this->config['cache_id']) && $this->config['display_cache'] && $this->cache) {
                // 缓存页面输出
                $this->cache->set($this->config['cache_id'], $content, $this->config['cache_time']);
            }
            
            echo $content;
        }
    }
    
    
    /**
     * 渲染模板内容
     * @access public
     * @param string $content 模板内容
     * @param array  $vars 模板变量
     * @return void
     */
    public function display(string $content, array $vars = []) : void
    {
        if ($vars) {
            $this->data = array_merge($this->data, $vars);
        }
        
        $cacheFile = $this->config['cache_path'] . $this->config['cache_prefix'] . md5($content) . '.' . ltrim($this->config['cache_suffix'], '.');
        
        if (!$this->checkCache($cacheFile)) {
            // 缓存无效 模板编译
            $this->compiler($content, $cacheFile);
        }
        
        // 读取编译存储
        $this->storage->read($cacheFile, $this->data);
    }
    
    
    /**
     * 检查编译缓存是否有效
     * 如果无效则需要重新编译
     * @access private
     * @param string $cacheFile 缓存文件名
     * @return bool
     */
    private function checkCache(string $cacheFile) : bool
    {
        if (!$this->config['tpl_cache'] || !is_file($cacheFile) || !$handle = @fopen($cacheFile, "r")) {
            return false;
        }
        
        // 读取第一行
        $line = fgets($handle);
        
        if (false === $line) {
            return false;
        }
        
        preg_match('/\/\*(.+?)\*\//', $line, $matches);
        
        if (!isset($matches[1])) {
            return false;
        }
        
        $includeFile = unserialize($matches[1]);
        
        if (!is_array($includeFile)) {
            return false;
        }
        
        // 检查模板文件是否有更新
        foreach ($includeFile as $path => $time) {
            if (is_file($path) && filemtime($path) > $time) {
                // 模板文件如果有更新则缓存需要更新
                return false;
            }
        }
        
        // 检查编译存储是否有效
        return $this->storage->check($cacheFile, $this->config['cache_time']);
    }
    
    
    /**
     * 编译模板文件内容
     * @access private
     * @param string $content 模板内容
     * @param string $cacheFile 缓存文件名
     * @return void
     */
    private function compiler(string &$content, string $cacheFile) : void
    {
        // 判断是否启用布局
        if ($this->config['layout_on']) {
            if (false !== strpos($content, '{__NOLAYOUT__}')) {
                // 可以单独定义不使用布局
                $content = str_replace('{__NOLAYOUT__}', '', $content);
            } else {
                // 读取布局模板
                $layoutFile = $this->parseTemplateFile($this->config['layout_name']);
                
                if ($layoutFile) {
                    // 替换布局的主体内容
                    $content = str_replace($this->config['layout_item'], $content, file_get_contents($layoutFile));
                }
            }
        } else {
            $content = str_replace('{__NOLAYOUT__}', '', $content);
        }
        
        // 模板解析
        $this->parse($content);
        
        if ($this->config['strip_space']) {
            /* 去除html空格与换行 */
            $find    = ['~>\s+<~', '~>(\s+\n|\r)~'];
            $replace = ['><', '>'];
            $content = preg_replace($find, $replace, $content);
        }
        
        // 优化生成的php代码
        $content = preg_replace('/\?>\s*<\?php\s(?!echo\b|\bend)/s', '', $content);
        
        // 模板过滤输出
        $replace = $this->config['tpl_replace_string'];
        $content = str_replace(array_keys($replace), array_values($replace), $content);
        
        // 添加安全代码及模板引用记录
        $content = '<?php /*' . serialize($this->includeFile) . '*/ ?>' . "\n" . $content;
        // 编译存储
        $this->storage->write($cacheFile, $content);
        
        $this->includeFile = [];
    }
    
    
    /**
     * 模板解析入口
     * 支持普通标签和TagLib解析 支持自定义标签库
     * @access public
     * @param string $content 要解析的模板内容
     * @return void
     */
    public function parse(string &$content) : void
    {
        // 内容为空不解析
        if (empty($content)) {
            return;
        }
        
        // 替换literal标签内容
        $this->parseLiteral($content);
        
        // 解析继承
        $this->parseExtend($content);
        
        // 解析布局
        $this->parseLayout($content);
        
        // 检查include语法
        $this->parseInclude($content);
        
        // 替换包含文件中literal标签内容
        $this->parseLiteral($content);
        
        // 检查PHP语法
        $this->parsePhp($content);
        
        // 获取需要引入的标签库列表
        // 标签库只需要定义一次，允许引入多个一次
        // 一般放在文件的最前面
        // 格式：<taglib name="html,mytag..." />
        // 当TAGLIB_LOAD配置为true时才会进行检测
        if ($this->config['taglib_load']) {
            $tagLibs = $this->getIncludeTagLib($content);
            
            if (!empty($tagLibs)) {
                // 对导入的TagLib进行解析
                foreach ($tagLibs as $tagLibName) {
                    $this->parseTagLib($tagLibName, $content);
                }
            }
        }
        
        // 预先加载的标签库 无需在每个模板中使用taglib标签加载 但必须使用标签库XML前缀
        if ($this->config['taglib_pre_load']) {
            $tagLibs = explode(',', $this->config['taglib_pre_load']);
            
            foreach ($tagLibs as $tag) {
                $this->parseTagLib($tag, $content);
            }
        }
        
        // 内置标签库 无需使用taglib标签导入就可以使用 并且不需使用标签库XML前缀
        $tagLibs = explode(',', $this->config['taglib_build_in']);
        
        foreach ($tagLibs as $tag) {
            $this->parseTagLib($tag, $content, true);
        }
        
        // 解析普通模板标签 {$tagName}
        $this->parseTag($content);
        
        // 还原被替换的Literal标签
        $this->parseLiteral($content, true);
    }
    
    
    /**
     * 检查PHP语法
     * @access private
     * @param string $content 要解析的模板内容
     * @return void
     * @throws Exception
     */
    private function parsePhp(string &$content) : void
    {
        // 短标签的情况要将<?标签用echo方式输出 否则无法正常输出xml标识
        $content = preg_replace('/(<\?(?!php|=|$))/i', '<?php echo \'\\1\'; ?>' . "\n", $content);
        
        // PHP语法检查
        if ($this->config['tpl_deny_php'] && false !== strpos($content, '<?php')) {
            throw new Exception('not allow php tag');
        }
    }
    
    
    /**
     * 解析模板中的布局标签
     * @access private
     * @param string $content 要解析的模板内容
     * @return void
     */
    private function parseLayout(string &$content) : void
    {
        // 读取模板中的布局标签
        if (preg_match($this->getRegex('layout'), $content, $matches)) {
            // 替换Layout标签
            $content = str_replace($matches[0], '', $content);
            // 解析Layout标签
            $array = $this->parseAttr($matches[0]);
            
            if (!$this->config['layout_on'] || $this->config['layout_name'] != $array['name']) {
                // 读取布局模板
                $layoutFile = $this->parseTemplateFile($array['name']);
                
                if ($layoutFile) {
                    $replace = isset($array['replace']) ? $array['replace'] : $this->config['layout_item'];
                    // 替换布局的主体内容
                    $content = str_replace($replace, $content, file_get_contents($layoutFile));
                }
            }
        } else {
            $content = str_replace('{__NOLAYOUT__}', '', $content);
        }
    }
    
    
    /**
     * 解析模板中的include标签
     * @access private
     * @param string $content 要解析的模板内容
     * @return void
     */
    private function parseInclude(string &$content) : void
    {
        $regex = $this->getRegex('include');
        $func  = function($template) use (&$func, &$regex, &$content) {
            if (preg_match_all($regex, $template, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $array = $this->parseAttr($match[0]);
                    $file  = $array['file'];
                    unset($array['file']);
                    
                    // 分析模板文件名并读取内容
                    $parseStr = $this->parseTemplateName($file);
                    
                    foreach ($array as $k => $v) {
                        // 以$开头字符串转换成模板变量
                        if (0 === strpos($v, '$')) {
                            $v = $this->get(substr($v, 1));
                        }
                        
                        $parseStr = str_replace('[' . $k . ']', $v, $parseStr);
                    }
                    
                    $content = str_replace($match[0], $parseStr, $content);
                    // 再次对包含文件进行模板分析
                    $func($parseStr);
                }
                unset($matches);
            }
        };
        
        // 替换模板中的include标签
        $func($content);
    }
    
    
    /**
     * 解析模板中的extend标签
     * @access private
     * @param string $content 要解析的模板内容
     * @return void
     */
    private function parseExtend(string &$content) : void
    {
        $regex  = $this->getRegex('extend');
        $array  = $blocks = $baseBlocks = [];
        $extend = '';
        
        $func = function($template) use (&$func, &$regex, &$array, &$extend, &$blocks, &$baseBlocks) {
            if (preg_match($regex, $template, $matches)) {
                if (!isset($array[$matches['name']])) {
                    $array[$matches['name']] = 1;
                    // 读取继承模板
                    $extend = $this->parseTemplateName($matches['name']);
                    
                    // 递归检查继承
                    $func($extend);
                    
                    // 取得block标签内容
                    $blocks = array_merge($blocks, $this->parseBlock($template));
                    
                    return;
                }
            } else {
                // 取得顶层模板block标签内容
                $baseBlocks = $this->parseBlock($template, true);
                
                if (empty($extend)) {
                    // 无extend标签但有block标签的情况
                    $extend = $template;
                }
            }
        };
        
        $func($content);
        
        if (!empty($extend)) {
            if ($baseBlocks) {
                $children = [];
                foreach ($baseBlocks as $name => $val) {
                    $replace = $val['content'];
                    
                    if (!empty($children[$name])) {
                        // 如果包含有子block标签
                        foreach ($children[$name] as $key) {
                            $replace = str_replace($baseBlocks[$key]['begin'] . $baseBlocks[$key]['content'] . $baseBlocks[$key]['end'], $blocks[$key]['content'], $replace);
                        }
                    }
                    
                    if (isset($blocks[$name])) {
                        // 带有{__block__}表示与所继承模板的相应标签合并，而不是覆盖
                        $replace = str_replace(['{__BLOCK__}', '{__block__}'], $replace, $blocks[$name]['content']);
                        
                        if (!empty($val['parent'])) {
                            // 如果不是最顶层的block标签
                            $parent = $val['parent'];
                            
                            if (isset($blocks[$parent])) {
                                $blocks[$parent]['content'] = str_replace($blocks[$name]['begin'] . $blocks[$name]['content'] . $blocks[$name]['end'], $replace, $blocks[$parent]['content']);
                            }
                            
                            $blocks[$name]['content'] = $replace;
                            $children[$parent][]      = $name;
                            
                            continue;
                        }
                    } elseif (!empty($val['parent'])) {
                        // 如果子标签没有被继承则用原值
                        $children[$val['parent']][] = $name;
                        $blocks[$name]              = $val;
                    }
                    
                    if (!$val['parent']) {
                        // 替换模板中的顶级block标签
                        $extend = str_replace($val['begin'] . $val['content'] . $val['end'], $replace, $extend);
                    }
                }
            }
            
            $content = $extend;
            unset($blocks, $baseBlocks);
        }
    }
    
    
    /**
     * 替换页面中的literal标签
     * @access private
     * @param string  $content 模板内容
     * @param boolean $restore 是否为还原
     * @return void
     */
    private function parseLiteral(string &$content, bool $restore = false) : void
    {
        $regex = $this->getRegex($restore ? 'restoreliteral' : 'literal');
        
        if (preg_match_all($regex, $content, $matches, PREG_SET_ORDER)) {
            if (!$restore) {
                $count = count($this->literal);
                
                // 替换literal标签
                foreach ($matches as $match) {
                    $this->literal[] = substr($match[0], strlen($match[1]), -strlen($match[2]));
                    $content         = str_replace($match[0], "<!--###literal{$count}###-->", $content);
                    $count++;
                }
            } else {
                // 还原literal标签
                foreach ($matches as $match) {
                    $content = str_replace($match[0], $this->literal[$match[1]], $content);
                }
                
                // 清空literal记录
                $this->literal = [];
            }
            
            unset($matches);
        }
    }
    
    
    /**
     * 获取模板中的block标签
     * @access private
     * @param string  $content 模板内容
     * @param boolean $sort 是否排序
     * @return array
     */
    private function parseBlock(string &$content, bool $sort = false) : array
    {
        $regex  = $this->getRegex('block');
        $result = [];
        
        if (preg_match_all($regex, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            $right = $keys = [];
            
            foreach ($matches as $match) {
                if (empty($match['name'][0])) {
                    if (count($right) > 0) {
                        $tag    = array_pop($right);
                        $start  = $tag['offset'] + strlen($tag['tag']);
                        $length = $match[0][1] - $start;
                        
                        $result[$tag['name']] = [
                            'begin'   => $tag['tag'],
                            'content' => substr($content, $start, $length),
                            'end'     => $match[0][0],
                            'parent'  => count($right) ? end($right)['name'] : '',
                        ];
                        
                        $keys[$tag['name']] = $match[0][1];
                    }
                } else {
                    // 标签头压入栈
                    $right[] = [
                        'name'   => $match[2][0],
                        'offset' => $match[0][1],
                        'tag'    => $match[0][0],
                    ];
                }
            }
            
            unset($right, $matches);
            
            if ($sort) {
                // 按block标签结束符在模板中的位置排序
                array_multisort($keys, $result);
            }
        }
        
        return $result;
    }
    
    
    /**
     * 搜索模板页面中包含的TagLib库
     * 并返回列表
     * @access private
     * @param string $content 模板内容
     * @return array|null
     */
    private function getIncludeTagLib(string &$content)
    {
        // 搜索是否有TagLib标签
        if (preg_match($this->getRegex('taglib'), $content, $matches)) {
            // 替换TagLib标签
            $content = str_replace($matches[0], '', $content);
            
            return explode(',', $matches['name']);
        }
        
        return null;
    }
    
    
    /**
     * 模板标签解析
     * 格式： {TagName:args [|content] }
     * @access private
     * @param string $content 要解析的模板内容
     * @return void
     */
    private function parseTag(string &$content) : void
    {
        $regex = $this->getRegex('tag');
        
        if (preg_match_all($regex, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $str  = stripslashes($match[1]);
                $flag = substr($str, 0, 1);
                
                switch ($flag) {
                    case '$':
                        // 解析模板变量 格式 {$varName}
                        // 是否带有?号
                        if (false !== $pos = strpos($str, '?')) {
                            $array = preg_split('/([!=]={1,2}|(?<!-)[><]={0,1})/', substr($str, 0, $pos), 2, PREG_SPLIT_DELIM_CAPTURE);
                            $name  = $array[0];
                            
                            $this->parseVar($name);
                            //$this->parseVarFunction($name);
                            
                            $str = trim(substr($str, $pos + 1));
                            $this->parseVar($str);
                            $first = substr($str, 0, 1);
                            
                            if (strpos($name, ')')) {
                                // $name为对象或是自动识别，或者含有函数
                                if (isset($array[1])) {
                                    $this->parseVar($array[2]);
                                    $name .= $array[1] . $array[2];
                                }
                                
                                switch ($first) {
                                    case '?':
                                        $this->parseVarFunction($name);
                                        $str = '<?php echo (' . $name . ') ? ' . $name . ' : ' . substr($str, 1) . '; ?>';
                                    break;
                                    case '=':
                                        $str = '<?php if(' . $name . ') echo ' . substr($str, 1) . '; ?>';
                                    break;
                                    default:
                                        $str = '<?php echo ' . $name . '?' . $str . '; ?>';
                                }
                            } else {
                                if (isset($array[1])) {
                                    $express = true;
                                    $this->parseVar($array[2]);
                                    $express = $name . $array[1] . $array[2];
                                } else {
                                    $express = false;
                                }
                                
                                if (in_array($first, ['?', '=', ':'])) {
                                    $str = trim(substr($str, 1));
                                    if ('$' == substr($str, 0, 1)) {
                                        $str = $this->parseVarFunction($str);
                                    }
                                }
                                
                                // $name为数组
                                switch ($first) {
                                    case '?':
                                        // {$varname??'xxx'} $varname有定义则输出$varname,否则输出xxx
                                        $str = '<?php echo ' . ($express ?: 'isset(' . $name . ')') . ' ? ' . $this->parseVarFunction($name) . ' : ' . $str . '; ?>';
                                    break;
                                    case '=':
                                        // {$varname?='xxx'} $varname为真时才输出xxx
                                        $str = '<?php if(' . ($express ?: '!empty(' . $name . ')') . ') echo ' . $str . '; ?>';
                                    break;
                                    case ':':
                                        // {$varname?:'xxx'} $varname为真时输出$varname,否则输出xxx
                                        $str = '<?php echo ' . ($express ?: '!empty(' . $name . ')') . ' ? ' . $this->parseVarFunction($name) . ' : ' . $str . '; ?>';
                                    break;
                                    default:
                                        if (strpos($str, ':')) {
                                            // {$varname ? 'a' : 'b'} $varname为真时输出a,否则输出b
                                            $array = explode(':', $str, 2);
                                            
                                            $array[0] = '$' == substr(trim($array[0]), 0, 1) ? $this->parseVarFunction($array[0]) : $array[0];
                                            $array[1] = '$' == substr(trim($array[1]), 0, 1) ? $this->parseVarFunction($array[1]) : $array[1];
                                            
                                            $str = implode(' : ', $array);
                                        }
                                        $str = '<?php echo ' . ($express ?: '!empty(' . $name . ')') . ' ? ' . $str . '; ?>';
                                }
                            }
                        } else {
                            $this->parseVar($str);
                            $this->parseVarFunction($str);
                            $str = '<?php echo ' . $str . '; ?>';
                        }
                    break;
                    case ':':
                        // 输出某个函数的结果
                        $str = substr($str, 1);
                        $this->parseVar($str);
                        $str = '<?php echo ' . $str . '; ?>';
                    break;
                    case '~':
                        // 执行某个函数
                        $str = substr($str, 1);
                        $this->parseVar($str);
                        $str = '<?php ' . $str . '; ?>';
                    break;
                    case '-':
                    case '+':
                        // 输出计算
                        $this->parseVar($str);
                        $str = '<?php echo ' . $str . '; ?>';
                    break;
                    case '/':
                        // 注释标签
                        $flag2 = substr($str, 1, 1);
                        if ('/' == $flag2 || ('*' == $flag2 && substr(rtrim($str), -2) == '*/')) {
                            $str = '';
                        }
                    break;
                    default:
                        // 未识别的标签直接返回
                        $str = $this->config['tpl_begin'] . $str . $this->config['tpl_end'];
                    break;
                }
                
                $content = str_replace($match[0], $str, $content);
            }
            
            unset($matches);
        }
    }
    
    
    /**
     * 分析加载的模板文件并读取内容 支持多个模板文件读取
     * @access private
     * @param string $templateName 模板文件名
     * @return string
     */
    private function parseTemplateName(string $templateName) : string
    {
        $array    = explode(',', $templateName);
        $parseStr = '';
        
        foreach ($array as $templateName) {
            if (empty($templateName)) {
                continue;
            }
            
            if (0 === strpos($templateName, '$')) {
                //支持加载变量文件名
                $templateName = $this->get(substr($templateName, 1));
            }
            
            $template = $this->parseTemplateFile($templateName);
            
            if ($template) {
                // 获取模板文件内容
                $parseStr .= file_get_contents($template);
            }
        }
        
        return $parseStr;
    }
    
    
    /**
     * 解析模板文件名
     * @access private
     * @param string $template 文件名
     * @return string
     */
    private function parseTemplateFile(string $template) : string
    {
        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // @app:
            // 解析到 src/app 目录
            if (0 === strpos($template, '@app:')) {
                $template = App::getInstance()->getFrameworkPath('app/' . ltrim(substr($template, 5), '/') . '.html');
            }
            
            // @admin:
            // 解析到 src/admin/view 目录
            elseif (0 === strpos($template, '@admin:')) {
                $template = App::getInstance()
                    ->getFrameworkPath('app/admin/view/' . ltrim(substr($template, 7), '/') . '.html');
            }
            
            //
            // 默认
            else {
                if (0 !== strpos($template, '/')) {
                    $template = str_replace(['/', ':'], $this->config['view_depr'], $template);
                } else {
                    $template = str_replace(['/', ':'], $this->config['view_depr'], substr($template, 1));
                }
                
                $template = $this->config['view_path'] . $template . '.' . ltrim($this->config['view_suffix'], '.');
            }
        }
        
        if (is_file($template)) {
            // 记录模板文件的更新时间
            $this->includeFile[$template] = filemtime($template);
            
            return $template;
        }
        
        throw new Exception('template not exists:' . $template);
    }
    
    
    /**
     * 按标签生成正则
     * @access private
     * @param string $tagName 标签名
     * @return string
     */
    private function getRegex(string $tagName) : string
    {
        $regex = '';
        if ('tag' == $tagName) {
            $begin = $this->config['tpl_begin'];
            $end   = $this->config['tpl_end'];
            
            if (strlen(ltrim($begin, '\\')) == 1 && strlen(ltrim($end, '\\')) == 1) {
                $regex = $begin . '((?:[\$]{1,2}[a-wA-w_]|[\:\~][\$a-wA-w_]|[+]{2}[\$][a-wA-w_]|[-]{2}[\$][a-wA-w_]|\/[\*\/])(?>[^' . $end . ']*))' . $end;
            } else {
                $regex = $begin . '((?:[\$]{1,2}[a-wA-w_]|[\:\~][\$a-wA-w_]|[+]{2}[\$][a-wA-w_]|[-]{2}[\$][a-wA-w_]|\/[\*\/])(?>(?:(?!' . $end . ').)*))' . $end;
            }
        } else {
            $begin  = $this->config['taglib_begin'];
            $end    = $this->config['taglib_end'];
            $single = strlen(ltrim($begin, '\\')) == 1 && strlen(ltrim($end, '\\')) == 1 ? true : false;
            
            switch ($tagName) {
                case 'block':
                    if ($single) {
                        $regex = $begin . '(?:' . $tagName . '\b\s+(?>(?:(?!name=).)*)\bname=([\'\"])(?P<name>[\$\w\-\/\.]+)\\1(?>[^' . $end . ']*)|\/' . $tagName . ')' . $end;
                    } else {
                        $regex = $begin . '(?:' . $tagName . '\b\s+(?>(?:(?!name=).)*)\bname=([\'\"])(?P<name>[\$\w\-\/\.]+)\\1(?>(?:(?!' . $end . ').)*)|\/' . $tagName . ')' . $end;
                    }
                break;
                case 'literal':
                    if ($single) {
                        $regex = '(' . $begin . $tagName . '\b(?>[^' . $end . ']*)' . $end . ')';
                        $regex .= '(?:(?>[^' . $begin . ']*)(?>(?!' . $begin . '(?>' . $tagName . '\b[^' . $end . ']*|\/' . $tagName . ')' . $end . ')' . $begin . '[^' . $begin . ']*)*)';
                        $regex .= '(' . $begin . '\/' . $tagName . $end . ')';
                    } else {
                        $regex = '(' . $begin . $tagName . '\b(?>(?:(?!' . $end . ').)*)' . $end . ')';
                        $regex .= '(?:(?>(?:(?!' . $begin . ').)*)(?>(?!' . $begin . '(?>' . $tagName . '\b(?>(?:(?!' . $end . ').)*)|\/' . $tagName . ')' . $end . ')' . $begin . '(?>(?:(?!' . $begin . ').)*))*)';
                        $regex .= '(' . $begin . '\/' . $tagName . $end . ')';
                    }
                break;
                case 'restoreliteral':
                    $regex = '<!--###literal(\d+)###-->';
                break;
                case 'include':
                    $name = 'file';
                case 'taglib':
                case 'layout':
                case 'extend':
                    if (empty($name)) {
                        $name = 'name';
                    }
                    if ($single) {
                        $regex = $begin . $tagName . '\b\s+(?>(?:(?!' . $name . '=).)*)\b' . $name . '=([\'\"])(?P<name>[\$\w\-\/\.\:@,\\\\]+)\\1(?>[^' . $end . ']*)' . $end;
                    } else {
                        $regex = $begin . $tagName . '\b\s+(?>(?:(?!' . $name . '=).)*)\b' . $name . '=([\'\"])(?P<name>[\$\w\-\/\.\:@,\\\\]+)\\1(?>(?:(?!' . $end . ').)*)' . $end;
                    }
                break;
            }
        }
        
        return '/' . $regex . '/is';
    }
}
