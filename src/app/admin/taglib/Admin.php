<?php

namespace BusyPHP\app\admin\taglib;

use BusyPHP\app\admin\controller\AdminController;
use BusyPHP\exception\AppException;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\helper\util\Str;
use think\template\TagLib;

/**
 * BusyPHP后端模板标签库
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/2 下午6:04 下午 Bp.php $
 */
class Admin extends TagLib
{
    protected $isUEditor  = false;
    
    protected $isFile     = false;
    
    protected $isPics     = false;
    
    protected $bodyExtend = '';
    
    protected $headExtend = '';
    
    protected $trimStart  = '<busy-admin-trim>';
    
    protected $trimEnd    = '</busy-admin-trim>';
    
    /**
     * 标签配置
     * @var array
     */
    protected $tags = [
        'js'         => ['attr' => 'type', 'close' => 0],
        'date'       => ['attr' => 'format'],
        'file'       => ['attr' => 'name,type,mark,readonly,image-target,desc-target,select-name,select-icon,upload-name,upload-icon,readonly,init'],
        'pics'       => ['attr' => 'name,type,mark,select-name,select-icon,upload-name,upload-icon'],
        'ueditor'    => ['attr' => 'config,name,id,class,file,image,video,mark,width,height'],
        'search'     => ['attr' => 'url,fields,accurate', 'must' => 'url'],
        'tpl'        => ['attr' => 'type', 'must' => 'type'],
        'btn'        => ['attr' => 'url,type,size,block,style,class,icon,permission,line,circle'],
        'form-group' => ['attr' => 'label,desc,col,id,class,type,must', 'expression' => true],
        'permission' => ['attr' => 'name', 'must' => 'name']
    ];
    
    
    public function parseTag(string &$content, string $lib = '') : void
    {
        $tags     = [];
        $lib      = $lib ? strtolower($lib) . ':' : '';
        $aliasLib = 'busy-';
        
        foreach ($this->tags as $name => $val) {
            $close                           = !isset($val['close']) || $val['close'] ? 1 : 0;
            $tags[$close][$lib . $name]      = $name;
            $tags[$close][$aliasLib . $name] = $name;
            if (isset($val['alias'])) {
                // 别名设置
                $array = (array) $val['alias'];
                foreach (explode(',', $array[0]) as $v) {
                    $tags[$close][$lib . $v]      = $name;
                    $tags[$close][$aliasLib . $v] = $name;
                }
            }
        }
        
        // 闭合标签
        if (!empty($tags[1])) {
            $nodes = [];
            $regex = $this->getRegex(array_keys($tags[1]), 1);
            if (preg_match_all($regex, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                $right = [];
                foreach ($matches as $match) {
                    if ('' == $match[1][0]) {
                        $name = strtolower($match[2][0]);
                        // 如果有没闭合的标签头则取出最后一个
                        if (!empty($right[$name])) {
                            // $match[0][1]为标签结束符在模板中的位置
                            $nodes[$match[0][1]] = [
                                'name'  => $name,
                                'begin' => array_pop($right[$name]), // 标签开始符
                                'end'   => $match[0], // 标签结束符
                            ];
                        }
                    } else {
                        // 标签头压入栈
                        $right[strtolower($match[1][0])][] = $match[0];
                    }
                }
                unset($right, $matches);
                // 按标签在模板中的位置从后向前排序
                krsort($nodes);
            }
            
            $break = '<!--###break###--!>';
            if ($nodes) {
                $beginArray = [];
                // 标签替换 从后向前
                foreach ($nodes as $pos => $node) {
                    // 对应的标签名
                    $name  = $tags[1][$node['name']];
                    $alias = $lib . $name != $node['name'] ? ($lib ? strstr($node['name'], $lib) : $node['name']) : '';
                    
                    // 解析标签属性
                    $attrs  = $this->parseAttr($node['begin'][0], $name, $alias);
                    $method = 'tag' . str_replace('-', '', $name);
                    
                    // 读取标签库中对应的标签内容 replace[0]用来替换标签头，replace[1]用来替换标签尾
                    $replace = explode($break, $this->$method($attrs, $break));
                    
                    if (count($replace) > 1) {
                        while ($beginArray) {
                            $begin = end($beginArray);
                            // 判断当前标签尾的位置是否在栈中最后一个标签头的后面，是则为子标签
                            if ($node['end'][1] > $begin['pos']) {
                                break;
                            } else {
                                // 不为子标签时，取出栈中最后一个标签头
                                $begin = array_pop($beginArray);
                                // 替换标签头部
                                $content = substr_replace($content, $begin['str'], $begin['pos'], $begin['len']);
                            }
                        }
                        // 替换标签尾部
                        $content = substr_replace($content, $replace[1], $node['end'][1], strlen($node['end'][0]));
                        // 把标签头压入栈
                        $beginArray[] = [
                            'pos' => $node['begin'][1],
                            'len' => strlen($node['begin'][0]),
                            'str' => $replace[0]
                        ];
                    }
                }
                
                while ($beginArray) {
                    $begin = array_pop($beginArray);
                    // 替换标签头部
                    $content = substr_replace($content, $begin['str'], $begin['pos'], $begin['len']);
                }
            }
        }
        // 自闭合标签
        if (!empty($tags[0])) {
            $regex   = $this->getRegex(array_keys($tags[0]), 0);
            $content = preg_replace_callback($regex, function($matches) use (&$tags, &$lib) {
                // 对应的标签名
                $name  = $tags[0][strtolower($matches[1])];
                $alias = $lib . $name != $matches[1] ? ($lib ? strstr($matches[1], $lib) : $matches[1]) : '';
                // 解析标签属性
                $attrs  = $this->parseAttr($matches[0], $name, $alias);
                $method = 'tag' . $name;
                
                return $this->$method($attrs, '');
            }, $content);
        }
        
        
        // 解析完成处理
        $bodyExtend = '';
        $headExtend = '';
        
        // 导入上传插件
        if ($this->isFile) {
            $bodyExtend   .= $this->createJs('__ASSETS__admin/lib/webuploader/webuploader.min.js');
            $bodyExtend   .= $this->createJs('Common.Js/uploader', true);
            $this->isFile = false;
        }
        
        // 导入百度编辑器JS插件
        if ($this->isUEditor) {
            $bodyExtend      .= $this->createJs('__ASSETS__admin/lib/ueditor/ueditor.all.min.js');
            $bodyExtend      .= $this->createJs('Common.Ueditor/runtime?js=1', true);
            $this->isUEditor = false;
        }
        
        // 导入多图上传
        if ($this->isPics) {
            $headExtend .= $this->createCss('__ASSETS__admin/lib/pics/css.css');
            $bodyExtend .= $this->createJs('__ASSETS__admin/lib/pics/js.js');
        }
        
        $headExtend .= $this->headExtend;
        $bodyExtend .= $this->bodyExtend;
        
        if ($headExtend) {
            $content = preg_replace_callback('/<!--busy-admin-page-head-->/is', function($array) use ($headExtend) {
                return $array[0] . $headExtend;
            }, $content);
        }
        
        // 追加内容
        if ($bodyExtend) {
            $content = preg_replace_callback('/<!--busy-admin-page-foot-->/is', function($array) use ($bodyExtend) {
                return $array[0] . $bodyExtend;
            }, $content);
        }
        
        // 移除由于标签换行造成的空格
        $trimStart = $this->trimStart;
        $trimEnd   = str_replace('/', '\/', $this->trimEnd);
        $content   = preg_replace_callback("/{$trimStart}(.*?){$trimEnd}/is", function($array) {
            return trim($array[1]);
        }, $content);
    }
    
    
    /**
     * 创建css
     * @param string $path
     * @param bool   $parse
     * @param string $id
     * @return string
     */
    protected function createCss($path, $parse = false, $id = '')
    {
        if ($parse === true) {
            $path = '<?php echo(url("' . $path . '"));?>';
        }
        
        return '<link rel="stylesheet" href="' . $path . '" type="text/css" id="' . $id . '"/>';
    }
    
    
    /**
     * 创建js
     * @param string $path
     * @param bool   $parse
     * @param string $id
     * @return string
     */
    protected function createJs($path, $parse = false, $id = '')
    {
        if ($parse === true) {
            $path = '<?php echo(url("' . $path . '"));?>';
        }
        
        return '<script src="' . $path . '" type="text/javascript" id="' . $id . '"></script>';
    }
    
    
    /**
     * 生成额外属性
     * @param array  $tag
     * @param string $name
     * @return string
     */
    protected function createAttr(array $tag, string $name) : string
    {
        $attr = '';
        foreach ($tag as $item => $value) {
            if (false !== stripos(',' . $this->tags[$name]['attr'] . ',', ',' . $item . ',') || $item == 'expression') {
                continue;
            }
            $attr .= ' ' . $item . '="' . $value . '"';
        }
        
        return $attr;
    }
    
    
    /**
     * 生成检测权限代码 {@see AdminController::checkAuth()}
     * @param string $content
     * @param string $path
     * @return string
     */
    protected function createPermission(string $content, string $path)
    {
        return <<<HTML
<?php if (BusyPHP\app\admin\controller\AdminController::checkAuth('{$path}')): ?>{$content}<?php endif; ?>
HTML;
    }
    
    
    /**
     * 获取属性值
     * @param array  $attr
     * @param string $key
     * @param mixed  $defaultValue
     * @return mixed
     */
    protected function parseValue(array $attr, string $key, $defaultValue = '')
    {
        $value = trim($attr[$key] ?? $defaultValue);
        
        return $value ?: $defaultValue;
    }
    
    
    /**
     * 解析bool类型
     * @param $value
     * @return bool
     */
    protected function parseBool($value)
    {
        if (is_bool($value)) {
            return $value;
        }
        
        $value = trim($value);
        if (!$value) {
            return false;
        }
        if ($value === 'false') {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * 附件上传
     * 语法 <admin:file name="表单名称" type="附件类型" mark="附件标识" target="上传成功后返回给的的HTML节点" desc-target="上传说明的HTML节点" select-icon="" select-name="" upload-name="" upload-icon="" init="">附件地址</admin:file>
     * @param array  $tag
     * @param string $content
     * @return string
     * @throws AppException
     */
    protected function tagFile(array $tag, string $content) : string
    {
        $tag['name']        = isset($tag['name']) ? $tag['name'] : '';
        $tag['type']        = isset($tag['type']) ? $tag['type'] : '';
        $tag['mark']        = isset($tag['mark']) ? $tag['mark'] : '';
        $tag['target']      = isset($tag['target']) ? $tag['target'] : '';
        $tag['desc-target'] = isset($tag['desc-target']) ? $tag['desc-target'] : '';
        $tag['select-icon'] = isset($tag['select-icon']) ? $tag['select-icon'] : '';
        $tag['select-name'] = isset($tag['select-name']) ? $tag['select-name'] : '';
        $tag['upload-name'] = isset($tag['upload-name']) ? $tag['upload-name'] : '';
        $tag['upload-icon'] = isset($tag['upload-icon']) ? $tag['upload-icon'] : '';
        $tag['readonly']    = isset($tag['readonly']) ? !empty($tag['readonly']) : false;
        $tag['init']        = isset($tag['init']) ? intval($tag['init']) : 1;
        if (!$tag['name']) {
            throw new AppException('admin:file标签缺少name值');
        }
        
        $this->isFile = true;
        
        // ID
        $id = str_replace('[', '_', $tag['name']);
        $id = str_replace(']', '_', $id);
        $id = str_replace('__', '_', $id);
        $id = ucfirst(Str::camel($id)) . 'File';
        
        // 显示触发容器对象
        $target = ["#{$id}"];
        if ($tag['target']) {
            $target = array_merge($target, explode(',', $tag['target']));
        }
        
        // 描述触发容器对象
        $descTarget = ["#{$id}Desc"];
        if ($tag['desc-target']) {
            $descTarget = array_merge($descTarget, explode(',', $tag['desc-target']));
        }
        
        // 附件类型
        $tag['type']        = $tag['type'] ? $tag['type'] : SystemFile::FILE_TYPE_IMAGE;
        $tag['upload-name'] = $tag['upload-name'] ? $tag['upload-name'] : '上传';
        $tag['select-name'] = $tag['select-name'] ? $tag['select-name'] : '选择';
        
        // 图标
        $sIcon = '';
        $uIcon = '';
        if ($tag['select-icon']) {
            $sIcon = "<i class='{$tag['select-icon']}'></i> ";
        }
        if ($tag['upload-icon']) {
            $uIcon = "<i class='{$tag['upload-icon']}'></i> ";
        }
        
        $temp = '<div class="input-group"><input type="text" id="' . $id . '" name="' . $tag['name'] . '" class="form-control" data-init="' . $tag['init'] . '" data-auto="file" data-mark-type="' . $tag['type'] . '" data-mark-value="' . $tag['mark'] . '" value="' . $this->trimStart . $content . $this->trimEnd . '" data-desc-target="' . implode(',', $descTarget) . '" data-target="' . implode(',', $target) . '" ' . ($tag['readonly'] ? 'readonly="readonly"' : '') . ' disabled/><div class="input-group-btn">';
        
        // 上传功能
        if ($tag['upload-name'] != 'none') {
            $temp .= '<a class="btn btn-success disabled" id="' . $id . 'UploadBtn" data-module="upload" href="javascript:void(0);" disabled>' . $uIcon . $tag['upload-name'] . '</a>';
        }
        
        
        // 选择功能
        if ($tag['select-name'] != 'none') {
            $temp .= '<a class="btn btn-default disabled" id="' . $id . 'SelectBtn" data-module="select" href="javascript:void(0);" disabled>' . $sIcon . $tag['select-name'] . '</a>';
        }
        
        $temp .= '</div></div>';
        
        return $temp;
    }
    
    
    /**
     * 多图上传器
     * 语法 <admin:pics name="" type="" mark="">图集数组</admin:pics>
     * @param array  $tag
     * @param string $content
     * @return string
     * @throws AppException
     */
    protected function tagPics(array $tag, string $content) : string
    {
        $name       = isset($tag['name']) ? $tag['name'] : '';
        $id         = str_replace('[', '_', $tag['name']);
        $id         = str_replace(']', '_', $id);
        $id         = str_replace('__', '_', $id);
        $id         = ucfirst(Str::camel($id)) . 'Pics';
        $type       = isset($tag['type']) ? trim($tag['type']) : '';
        $type       = !empty($type) ? $type : SystemFile::FILE_TYPE_IMAGE;
        $mark       = isset($tag['mark']) ? $tag['mark'] : '';
        $filename   = isset($tag['filename']) ? trim($tag['filename']) : '';
        $filename   = !empty($filename) ? 'true' : 'false';
        $uploadName = isset($tag['upload-name']) ? $tag['upload-name'] : '';
        $uploadName = $uploadName ? $uploadName : '上传图片';
        $uploadIcon = isset($tag['upload-icon']) ? $tag['upload-icon'] : '';
        $selectName = isset($tag['select-name']) ? $tag['select-name'] : '';
        $selectName = $selectName ? $selectName : '图库选择';
        $selectIcon = isset($tag['select-icon']) ? $tag['select-icon'] : '';
        
        if (!$name) {
            throw new AppException('admin:pics标签缺少name值');
        }
        
        $this->isFile = true;
        $this->isPics = true;
        
        $upload   = '';
        $isUpload = false;
        $isSelect = false;
        $hide     = '';
        if ($uploadName != 'none') {
            $icon     = $uploadIcon ? '<i class="' . $uploadIcon . '"></i> ' : '';
            $upload   .= '<a disabled data-module="upload" class="btn btn-success btn-xs pics-select-btn disabled" href="javascript:void(0)">' . $icon . $uploadName . '</a>';
            $isUpload = true;
        }
        
        if ($selectName != 'none') {
            $icon     = $selectIcon ? '<i class="' . $selectIcon . '"></i> ' : '';
            $upload   .= '<a disabled data-module="select" class="btn btn-default btn-xs pics-select-btn disabled" href="javascript:void(0)">' . $icon . $selectName . '</a>';
            $isSelect = true;
        }
        
        if ($isSelect && !$isUpload) {
            $hide = ' hide';
        }
        
        return '<div id="' . $id . '" data-filename="' . $filename . '" data-auto="pics" data-mark-type="' . $type . '" data-mark-value="' . $mark . '" data-name="' . $name . '"><div data-module="wrap" class="pics-wrap"><div data-module="header" class="pics-header"><div data-module="progress-box" class="hide pics-progress-box pull-right"><div class="progress"><span class="text" data-module="speed">0%</span><span class="percentage" data-module="progress"></span></div></div>' . $upload . '<div class="clearfix"></div></div><div data-module="body" class="pics-body' . $hide . '"><ul data-module="queue" class="pics-queue hide"></ul><div class="clearfix"></div><div class="pics-empty-select size-12" data-module="empty">请稍候...</div></div><div data-module="footer" class="pics-footer' . $hide . '"><div class="pull-right"><button data-module="start" class="btn btn-success btn-xs pics-start-btn disabled" disabled type="button">开始上传</button><button data-module="cancel" class="btn btn-danger btn-xs pics-cancel-btn disabled" disabled type="button">取消上传</button></div><div class="pull-left"><div data-module="tip" class="pics-info"></div></div><div class="clearfix"></div></div><div class="pics-list-box hide" data-module="list-box"><ul data-module="list" class="pics-queue"></ul><div class="clearfix"></div></div></div><div class="hide" data-module="data">' . $content . '</div></div>';
    }
    
    
    /**
     * 表单js语法
     * <admin:js type=""/>
     * @param $tag
     * @param $content
     * @return string
     */
    protected function tagJs(array $tag, string $content) : string
    {
        $type  = isset($tag['type']) ? $tag['type'] : '';
        $value = isset($tag['value']) ? $tag['value'] : '';
        
        switch (trim($type)) {
            // 上传
            case 'uploader':
                $this->isFile = true;
            break;
            // 颜色组件
            case 'color':
                $this->headExtend .= $this->createCss('__ASSETS__admin/lib/color/css.min.css');
                $this->bodyExtend .= $this->createJs('__ASSETS__admin/lib/color/js.min.js');
            break;
            // 地区组件
            case 'area':
                $this->bodyExtend .= $this->createJs('Common.Js/area?callback=' . $value, true);
                $this->bodyExtend .= $this->createJs('__ASSETS__admin/js/areaSelect.js');
            break;
        }
        
        return '';
    }
    
    
    /**
     * date语法
     * <admin:date format="Y-m-d H:i:s">时间变量，不包含左右大括号</date>
     * @param array  $tag
     * @param string $content
     * @return string
     */
    protected function tagDate(array $tag, string $content) : string
    {
        $format = $this->parseValue($tag, 'format', 'Y-m-d H:i:s');
        $var    = $this->autoBuildVar($content);
        
        return '<?php echo(date(\'' . $format . '\', ' . $var . ')); ?>';
    }
    
    
    /**
     * 百度UEditor编辑器语法
     * <admin:ueditor config="small" name="" width="" height="">内容</admin:ueditor>
     * @param $tag
     * @param $content
     * @return string
     */
    protected function tagUeditor(array $tag, string $content) : string
    {
        $config = $this->parseValue($tag, 'config', 'default');
        $name   = $this->parseValue($tag, 'name', 'content');
        $class  = $this->parseValue($tag, 'class');
        $file   = $this->parseValue($tag, 'file', SystemFile::FILE_TYPE_FILE);
        $image  = $this->parseValue($tag, 'image', SystemFile::FILE_TYPE_IMAGE);
        $video  = $this->parseValue($tag, 'video', SystemFile::FILE_TYPE_VIDEO);
        $mark   = $this->parseValue($tag, 'mark');
        $width  = str_replace('px', '', $this->parseValue($tag, 'width', '100%'));
        $width  = is_numeric($width) ? $width . 'px' : $width;
        $height = str_replace('px', '', $this->parseValue($tag, 'height', '500'));
        $height = is_numeric($height) ? $height . 'px' : $height;
        
        $this->isUEditor = true;
        
        return '<textarea data-ueditor="' . $config . '" role-image-mark="' . $image . '" role-file-mark="' . $file . '" role-video-mark="' . $video . '" role-mark="' . $mark . '" class="form-control ' . $class . '" name="' . $name . '" style="height:' . $height . '; width:' . $width . ';">' . $this->trimStart . $content . $this->trimEnd . '</textarea>';
    }
    
    
    /**
     * 解析搜索标签
     * @param array  $tag
     * @param string $content
     * @return string
     */
    protected function tagSearch(array $tag, string $content) : string
    {
        $url      = $tag['url'] ?? '';
        $fields   = $tag['fields'] ?? '';
        $accurate = $tag['accurate'] ?? 'false';
        
        return <<<HTML
<div data-toggle="busy-search-bar" data-url="{$url}" data-accurate="{$accurate}" data-fields="{$fields}" {$this->createAttr($tag, 'search')}>{$content}</div>
HTML;
    }
    
    
    /**
     * 解析模板标签
     * @param array  $tag
     * @param string $content
     * @return string
     */
    protected function tagTpl(array $tag, string $content) : string
    {
        $type = $tag['type'] ?? '';
        switch (strtolower($type)) {
            case 'search-left':
            case 'search-right':
            case 'search-toolbar':
                $attr = 'data-search-id="' . substr($type, 7) . '"';
            break;
            default:
                $attr = '';
        }
        
        return <<<HTML
<script type="text/html" {$attr}>{$content}</script>
HTML;
    }
    
    
    /**
     * 解析按钮标签
     * @param array  $tag
     * @param string $content
     * @return string
     */
    protected function tagBtn(array $tag, string $content) : string
    {
        // URL
        $url = $this->parseValue($tag, 'url', '');
        $url = preg_replace_callback('/{(.*?)}/s', function($match) {
            $condition = $this->parseCondition($match[1]);
            
            return "'. $condition .'";
        }, $url);
        
        // 按钮类型
        $type = strtolower($this->parseValue($tag, 'type', ''));
        if (in_array($type, ['button', 'submit', 'reset', 'search'])) {
            $name = 'button';
            $type = ' type="' . $type . '"';
            
            // 打开方式
            $target = $this->parseValue($tag, 'target', '');
            if ($target === '_blank') {
                $href = " onclick=\"window.open('{:url('{$url}')}')\"";
            } else {
                $href = " onclick=\"self.location.href='{:url('{$url}')}'\"";
            }
        } else {
            $type = '';
            $name = 'a';
            $href = " href=\"{:url('{$url}')}\"";
        }
        
        // 样式
        $style = " btn-{$this->parseValue($tag, 'style', 'default')}";
        
        // 线条边框按钮
        $line = $this->parseBool($tag['line'] ?? false) ? ' btn-line' : '';
        
        // 圆形按钮
        $circle = $this->parseBool($tag['circle'] ?? false) ? ' btn-circle' : '';
        
        // 自定义class
        $class = $this->parseValue($tag, 'class', '');
        $class = $class ? " {$class}" : '';
        
        // 尺寸
        $size = $this->parseValue($tag, 'size', '');
        $size = $size ? " btn-{$size}" : '';
        
        // 块
        $block = $this->parseBool($tag['block'] ?? '');
        $block = $block ? ' btn-block' : '';
        
        // 图标
        $icon    = $this->parseValue($tag, 'icon', '');
        $iconTag = '';
        if ($icon) {
            // 包含空格或包含变量符号则认为是自定义图标
            if (false === strpos($icon, ' ') && false === strpos($icon, '{')) {
                $icon = 'icon icon-' . $icon;
            }
            $iconTag = '<i class="' . $icon . '"></i>';
        }
        
        $html       = <<<HTML
<{$name} class="btn{$line}{$circle}{$style}{$size}{$block}{$class}"{$href}{$type}{$this->createAttr($tag, 'btn')}>{$iconTag} {$content}</{$name}>
HTML;
        $permission = trim($tag['permission'] ?? '');
        $permission = $permission === '' ? 'true' : $permission;
        if ($this->parseBool($permission)) {
            $path = $permission === '1' || $permission === 'true' ? $url : $permission;
            $html = $this->createPermission($html, $path);
        }
        
        return $html;
    }
    
    
    /**
     * 权限检测标签
     * @param array  $tag
     * @param string $content
     * @return string
     */
    protected function tagPermission(array $tag, string $content) : string
    {
        return $this->createPermission($content, trim($tag['name'] ?? ''));
    }
    
    
    /**
     * 表单组
     * @param array  $tag
     * @param string $content
     * @return string
     */
    protected function tagFormGroup(array $tag, string $content) : string
    {
        $type         = strtolower($this->parseValue($tag, 'type', ''));
        $label        = $this->parseValue($tag, 'label', '');
        $col          = $this->parseValue($tag, 'col', '');
        $isHorizontal = !empty($col);
        $must         = $this->parseValue($tag, 'must', '');
        $desc         = $this->parseValue($tag, 'desc', '');
        $id           = $this->parseValue($tag, 'id', '');
        $class        = $this->parseValue($tag, 'class', '');
        $class        = $class ? ' ' . $class : '';
        $col          = explode(',', $col, 2);
        $labelCol     = intval($this->parseValue($col, 0, 2));
        $contentCol   = intval($this->parseValue($col, 1, 4));
        $descCol      = 12 - $labelCol - $contentCol;
        
        $attrId = '';
        if ($id) {
            $attrId = ' id="' . $id . '"';
        }
        
        // 分组标题
        if ($type == 'title') {
            return <<<HTML
<div class="form-group-title{$class}"{$attrId}{$this->createAttr($tag, 'form-group')}>{$content}</div>
HTML;
        }
        
        // 名称
        $labelTag = '';
        if ($label && $type != 'action') {
            $labelId = '';
            if ($id) {
                $labelId = ' id="' . $id . 'Label"';
            }
            
            $mustClass = '';
            if ($must) {
                $mustClass = ' must';
            }
            
            // 水平
            if ($isHorizontal) {
                $labelClass = "control-label col-sm-{$labelCol}{$mustClass}";
            } else {
                $labelClass = $mustClass;
            }
            
            $labelTag = <<<HTML
<label class="{$labelClass}"{$labelId}>{$label}</label>
HTML;
        }
        
        // 描述
        $descTag = '';
        if ($desc) {
            $descId = '';
            if ($id) {
                $descId = ' id="' . $id . 'Desc"';
            }
            
            if ($isHorizontal) {
                $descTag = <<<HTML
<div class="col-sm-{$descCol}"{$descId}><p class="help-block">{$desc}</p></div>
HTML;
            } else {
                $descTag = <<<HTML
<p class="help-block"{$descId}>{$desc}</p>
HTML;
            }
        }
        
        // 内容
        if ($isHorizontal) {
            if ($type == 'action') {
                $contentClass = "col-sm-offset-{$labelCol} col-sm-{$contentCol}";
            } else {
                $contentClass = "col-sm-{$contentCol}";
            }
    
            $contentId = '';
            if ($id) {
                $contentId = ' id="' . $id . 'Content"';
            }
            
            $contentTag = <<<HTML
<div class="{$contentClass}"{$contentId}>{$content}</div>
HTML;
        } else {
            $contentTag = $content;
        }
        
        
        return <<<HTML
<div class="form-group{$class}"{$attrId}{$this->createAttr($tag, 'form-group')}>
    {$labelTag}
    {$contentTag}
    {$descTag}
</div>
HTML;
    }
}