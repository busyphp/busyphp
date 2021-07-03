<?php

namespace BusyPHP\app\admin\model\system\file\classes;

use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\helper\util\Filter;

/**
 * 附件分类模型信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午4:34 SystemFileClassInfo.php $
 */
class SystemFileClassInfo extends SystemFileClassField
{
    /**
     * 允许后缀是否继承系统设置
     * @var bool
     */
    public $suffixIsInherit;
    
    /**
     * 允许上传大小是否继承系统设置
     * @var bool
     */
    public $sizeIsInherit;
    
    /**
     * 类型
     * @var string
     */
    public $typeName;
    
    /**
     * 是否附件
     * @var bool
     */
    public $isFile;
    
    /**
     * 是否图片
     * @var bool
     */
    public $isImage;
    
    /**
     * 是否视频
     * @var bool
     */
    public $isVideo;
    
    
    public function onParseAfter()
    {
        $this->isSystem        = $this->isSystem > 0;
        $this->homeShow        = $this->homeShow > 0;
        $this->homeUpload      = $this->homeUpload > 0;
        $this->homeLogin       = $this->homeLogin > 0;
        $this->adminShow       = $this->adminShow > 0;
        $this->size            = Filter::min(intval($this->size), -1);
        $this->suffixIsInherit = $this->suffix <= -1;
        $this->sizeIsInherit   = $this->size <= -1;
        $this->typeName        = SystemFile::getTypes($this->type);
        $this->isFile          = $this->type == SystemFile::FILE_TYPE_FILE;
        $this->isImage         = $this->type == SystemFile::FILE_TYPE_IMAGE;
        $this->isVideo         = $this->type == SystemFile::FILE_TYPE_VIDEO;
        $this->isThumb         = $this->isImage && $this->isThumb > 0;
        $this->watermark       = $this->isImage && $this->watermark > 0;
        $this->deleteSource    = $this->isImage && $this->deleteSource > 0;
    }
}