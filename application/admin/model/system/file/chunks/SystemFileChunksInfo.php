<?php

namespace BusyPHP\app\admin\model\system\file\chunks;

use BusyPHP\helper\TransHelper;

/**
 * SystemFileChunksInfo
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/8 9:18 PM SystemFileChunksInfo.php $
 */
class SystemFileChunksInfo extends SystemFileChunksField
{
    /**
     * 格式化的创建时间
     * @var string
     */
    public $formatCreateTime;
    
    /**
     * 目录名称
     * @var string
     */
    public $dirname;
    
    /**
     * 文件名称
     * @var string
     */
    public $basename;
    
    /**
     * 文件路径
     * @var string
     */
    public $path;
    
    
    protected function onParseAfter() : void
    {
        $this->formatCreateTime = TransHelper::date($this->createTime);
        $this->dirname          = SystemFileChunks::getClass()::buildDir($this->fragmentId);
        $this->basename         = SystemFileChunks::getClass()::buildName($this->number);
        $this->path             = $this->dirname . '/' . $this->basename;
    }
}