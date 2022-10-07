<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file\classes;

use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\model\Entity;

/**
 * 附件分类模型信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午4:34 SystemFileClassInfo.php $
 * @method static Entity typeName() 类型名称
 * @method static Entity isFile() 是否附件
 * @method static Entity isImage() 是否图片
 * @method static Entity isVideo() 是否视频
 * @method static Entity isAudio() 是否音频
 */
class SystemFileClassInfo extends SystemFileClassField
{
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
    
    /**
     * 是否音频
     * @var bool
     */
    public $isAudio;
    
    
    protected function onParseAfter()
    {
        $this->typeName = SystemFile::getTypes($this->type);
        $this->isFile   = $this->type == SystemFile::FILE_TYPE_FILE;
        $this->isImage  = $this->type == SystemFile::FILE_TYPE_IMAGE;
        $this->isVideo  = $this->type == SystemFile::FILE_TYPE_VIDEO;
        $this->isAudio  = $this->type == SystemFile::FILE_TYPE_AUDIO;
    }
}