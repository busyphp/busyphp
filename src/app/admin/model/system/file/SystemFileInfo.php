<?php

namespace BusyPHP\app\admin\model\system\file;


use BusyPHP\helper\file\File;
use BusyPHP\helper\util\Transform;

/**
 * 附件模型信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午3:26 SystemFileInfo.php $
 */
class SystemFileInfo extends SystemFileField
{
    /**
     * 附件类型名称
     * @var string
     */
    public $classifyName;
    
    /**
     * 格式化的创建时间
     * @var string
     */
    public $formatCreateTime;
    
    /**
     * 附件大小单位
     * @var string
     */
    public $sizeUnit;
    
    /**
     * 附件大小
     * @var int
     */
    public $sizeNum;
    
    /**
     * 格式化的附件大小
     * @var string
     */
    public $formatSize;
    
    /**
     * 附件名称
     * @var string
     */
    public $filename;
    
    
    public function onParseAfter()
    {
        $this->classifyName     = SystemFile::getTypes($this->classify);
        $this->formatCreateTime = Transform::date($this->createTime);
        $this->isAdmin          = $this->isAdmin > 0;
        
        $sizes            = Transform::formatBytes($this->size, true);
        $this->sizeUnit   = $sizes['unit'];
        $this->sizeNum    = $sizes['number'];
        $this->formatSize = "{$this->sizeNum} {$this->sizeUnit}";
        $this->filename   = File::pathInfo($this->url, PATHINFO_BASENAME);
    }
}