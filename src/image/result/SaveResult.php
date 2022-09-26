<?php
declare(strict_types = 1);

namespace BusyPHP\image\result;

/**
 * 处理并图片保存返回结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 10:18 AM ProcessResult.php $
 */
class SaveResult
{
    /** @var int */
    private $width;
    
    /** @var int */
    private $height;
    
    /** @var string */
    private $format;
    
    /** @var int */
    private $size;
    
    /** @var int */
    private $quality;
    
    
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
     * @return $this
     */
    public function setWidth(int $width) : self
    {
        $this->width = $width;
        
        return $this;
    }
    
    
    /**
     * 获取图片高度
     * @return int
     */
    public function getHeight() : int
    {
        return $this->height;
    }
    
    
    /**
     * 设置图片高度
     * @param int $height
     * @return $this
     */
    public function setHeight(int $height) : self
    {
        $this->height = $height;
        
        return $this;
    }
    
    
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
     * @return $this
     */
    public function setFormat(string $format) : self
    {
        $this->format = strtolower($format);
        
        return $this;
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
     * @return $this
     */
    public function setSize(int $size) : self
    {
        $this->size = $size;
        
        return $this;
    }
    
    
    /**
     * 获取图片质量
     * @return int
     */
    public function getQuality() : int
    {
        return $this->quality ?: 0;
    }
    
    
    /**
     * 设置图片质量
     * @param int $quality
     * @return $this
     */
    public function setQuality(int $quality) : self
    {
        $this->quality = $quality;
        
        return $this;
    }
}