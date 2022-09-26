<?php
declare(strict_types = 1);

namespace BusyPHP\image\result;

/**
 * 图片信息返回结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 1:04 PM InfoResult.php $
 */
class InfoResult
{
    /** @var string */
    private $format;
    
    /** @var int */
    private $width;
    
    /** @var int */
    private $height;
    
    /** @var int */
    private $size;
    
    /** @var string */
    private $md5;
    
    /** @var int */
    private $frameCount;
    
    
    /**
     * 获取图片格式
     * @return string
     */
    public function getFormat() : string
    {
        return $this->format ?: '';
    }
    
    
    /**
     * 设置图片格式
     * @param string $format
     */
    public function setFormat(string $format) : void
    {
        $this->format = strtolower($format);
    }
    
    
    /**
     * 获取图片宽度
     * @return int
     */
    public function getWidth() : int
    {
        return $this->width ?: 0;
    }
    
    
    /**
     * 设置图片宽度
     * @param int $width
     */
    public function setWidth(int $width) : void
    {
        $this->width = $width;
    }
    
    
    /**
     * 获取图片高度
     * @return int
     */
    public function getHeight() : int
    {
        return $this->height ?: 0;
    }
    
    
    /**
     * 设置图片高度
     * @param int $height
     */
    public function setHeight(int $height) : void
    {
        $this->height = $height;
    }
    
    
    /**
     * 获取图片大小
     * @return int
     */
    public function getSize() : int
    {
        return $this->size ?: 0;
    }
    
    
    /**
     * 设置图片大小
     * @param int $size
     */
    public function setSize(int $size) : void
    {
        $this->size = $size;
    }
    
    
    /**
     * 获取图片哈希
     * @return string
     */
    public function getHash() : string
    {
        return $this->md5 ?: '';
    }
    
    
    /**
     * 设置图片哈希
     * @param string $md5
     */
    public function setMd5(string $md5) : void
    {
        $this->md5 = $md5;
    }
    
    
    /**
     * 获取动态图帧数
     * @return int
     */
    public function getFrameCount() : int
    {
        return $this->frameCount ?: 0;
    }
    
    
    /**
     * 设置动态度帧数
     * @param int $frameCount
     */
    public function setFrameCount(int $frameCount) : void
    {
        $this->frameCount = $frameCount;
    }
}