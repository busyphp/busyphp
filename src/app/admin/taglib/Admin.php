<?php

namespace BusyPHP\app\admin\taglib;

use BusyPHP\exception\AppException;
use BusyPHP\app\admin\model\system\file\SystemFile;
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
    
    /**
     * 标签配置
     * @var array
     */
    protected $tags = [
        'js'      => [
            'attr'  => 'type',
            'close' => 0
        ],
        'date'    => [
            'attr' => 'format'
        ],
        'file'    => [
            'attr' => 'name,type,mark,readonly,image-target,desc-target,select-name,select-icon,upload-name,upload-icon,readonly,init'
        ],
        'pics'    => [
            'attr' => 'name,type,mark,select-name,select-icon,upload-name,upload-icon'
        ],
        'ueditor' => [
            'attr' => 'config,name,id,class,file,image,video,mark,width,height'
        ]
    ];
    
    
    /**
     * 附件上传
     * 语法 <admin:file name="表单名称" type="附件类型" mark="附件标识" target="上传成功后返回给的的HTML节点" desc-target="上传说明的HTML节点" select-icon="" select-name="" upload-name="" upload-icon="" init="">附件地址</admin:file>
     * @param array  $tag
     * @param string $content
     * @return string
     * @throws AppException
     */
    public function tagFile(array $tag, string $content) : string
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
        $id = parse_name($id, 1) . 'File';
        
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
        
        $temp = '<div class="input-group"><input type="text" id="' . $id . '" name="' . $tag['name'] . '" class="form-control" data-init="' . $tag['init'] . '" data-auto="file" data-mark-type="' . $tag['type'] . '" data-mark-value="' . $tag['mark'] . '" value="' . $content . '" data-desc-target="' . implode(',', $descTarget) . '" data-target="' . implode(',', $target) . '" ' . ($tag['readonly'] ? 'readonly="readonly"' : '') . ' disabled/><div class="input-group-btn">';
        
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
    public function tagPics(array $tag, string $content) : string
    {
        $name       = isset($tag['name']) ? $tag['name'] : '';
        $id         = str_replace('[', '_', $tag['name']);
        $id         = str_replace(']', '_', $id);
        $id         = str_replace('__', '_', $id);
        $id         = parse_name($id, 1) . 'Pics';
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
    public function tagJs(array $tag, string $content) : string
    {
        $type  = isset($tag['type']) ? $tag['type'] : '';
        $value = isset($tag['value']) ? $tag['value'] : '';
        
        switch (trim($type)) {
            // 上传
            case 'uploader':
                $this->isFile = true;
            break;
            // 日期组件
            case 'date':
                $this->bodyExtend .= $this->createJs('__ASSETS__admin/lib/date/WdatePicker.js');
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
    public function tagDate(array $tag, string $content) : string
    {
        $format = $this->getAttrValue($tag, 'format', 'Y-m-d H:i:s');
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
    public function tagUeditor(array $tag, string $content) : string
    {
        $config = $this->getAttrValue($tag, 'config', 'default');
        $name   = $this->getAttrValue($tag, 'name', 'content');
        $class  = $this->getAttrValue($tag, 'class');
        $file   = $this->getAttrValue($tag, 'file', SystemFile::FILE_TYPE_FILE);
        $image  = $this->getAttrValue($tag, 'image', SystemFile::FILE_TYPE_IMAGE);
        $video  = $this->getAttrValue($tag, 'video', SystemFile::FILE_TYPE_VIDEO);
        $mark   = $this->getAttrValue($tag, 'mark');
        $width  = str_replace('px', '', $this->getAttrValue($tag, 'width', '100%'));
        $width  = is_numeric($width) ? $width . 'px' : $width;
        $height = str_replace('px', '', $this->getAttrValue($tag, 'height', '500'));
        $height = is_numeric($height) ? $height . 'px' : $height;
        
        $this->isUEditor = true;
        
        return '<textarea data-ueditor="' . $config . '" role-image-mark="' . $image . '" role-file-mark="' . $file . '" role-video-mark="' . $video . '" role-mark="' . $mark . '" class="form-control ' . $class . '" name="' . $name . '" style="height:' . $height . '; width:' . $width . ';">' . $content . '</textarea>';
    }
    
    
    public function parseTag(string &$content, string $lib = '') : void
    {
        parent::parseTag($content, $lib);
        
        $this->parseComplete($content);
    }
    
    
    /**
     * 模板解析完毕回调处理
     * @param $content
     */
    public function parseComplete(&$content)
    {
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
            $content = preg_replace_callback('/<\/head>/is', function($array) use ($headExtend) {
                return PHP_EOL . $headExtend . PHP_EOL . $array[0];
            }, $content);
        }
        
        // 追加内容
        if ($bodyExtend) {
            $content = preg_replace_callback('/<!--\[admin:plugin\]-->/is', function($array) use ($bodyExtend) {
                return PHP_EOL . $bodyExtend . PHP_EOL . $array[0];
            }, $content);
        }
    }
    
    
    /**
     * 获取属性值
     * @param array  $attr
     * @param string $key
     * @param mixed  $defaultValue
     * @return mixed
     */
    protected function getAttrValue($attr, $key, $defaultValue = '')
    {
        $value = '';
        if (isset($attr[$key])) {
            $value = $attr[$key];
        }
        $value = trim($value);
        
        return $value ? $value : $defaultValue;
    }
    
    
    /**
     * 创建css
     * @param $path
     * @param $parse
     * @return string
     */
    protected function createCss($path, $parse = false)
    {
        if ($parse === true) {
            $path = '<?php echo(url("' . $path . '"));?>';
        }
        
        return '<link rel="stylesheet" href="' . $path . '" type="text/css"/>';
    }
    
    
    /**
     * 创建js
     * @param $path
     * @param $parse
     * @return string
     */
    protected function createJs($path, $parse = false)
    {
        if ($parse === true) {
            $path = '<?php echo(url("' . $path . '"));?>';
        }
        
        return '<script src="' . $path . '" type="text/javascript"></script>';
    }
}