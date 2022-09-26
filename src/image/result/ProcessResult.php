<?php
declare(strict_types = 1);

namespace BusyPHP\image\result;

/**
 * 处理图片返回结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/14 10:15 AM ProcessResult.php $
 */
class ProcessResult
{
    /** @var string */
    private $mimetype = 'image/jpeg';
    
    /** @var int */
    private $width = 0;
    
    /** @var int */
    private $height = 0;
    
    /** @var int */
    private $filesize = 0;
    
    /** @var string */
    private $format = 'jpeg';
    
    /** @var string */
    private $data = '';
    
    
    /**
     * 获取Mimetype
     * @return string
     */
    public function getMimetype() : string
    {
        return $this->mimetype;
    }
    
    
    /**
     * 设置Mimetype
     * @param string $mimetype
     * @return $this
     */
    public function setMimetype(string $mimetype) : self
    {
        $this->mimetype = $mimetype;
        
        return $this;
    }
    
    
    /**
     * 获取宽度
     * @return int
     */
    public function getWidth() : int
    {
        return $this->width;
    }
    
    
    /**
     * 设置宽度
     * @param int $width
     * @return $this
     */
    public function setWidth(int $width) : self
    {
        $this->width = $width;
        
        return $this;
    }
    
    
    /**
     * 获取高度
     * @return int
     */
    public function getHeight() : int
    {
        return $this->height;
    }
    
    
    /**
     * 设置高度
     * @param int $height
     * @return $this
     */
    public function setHeight(int $height) : self
    {
        $this->height = $height;
        
        return $this;
    }
    
    
    /**
     * 获取图片大小
     * @return int
     */
    public function getFilesize() : int
    {
        return $this->filesize;
    }
    
    
    /**
     * 设置图片大小
     * @param int $filesize
     * @return $this
     */
    public function setFilesize(int $filesize) : self
    {
        $this->filesize = $filesize;
        
        return $this;
    }
    
    
    /**
     * 获取图片格式
     * @return string
     */
    public function getFormat() : string
    {
        return $this->format;
    }
    
    
    /**
     * 设置图片格式
     * @param string $format
     * @return $this
     */
    public function setFormat(string $format) : self
    {
        $this->format = $format;
        
        return $this;
    }
    
    
    /**
     * 获取图片数据
     * @return string
     */
    public function getData() : string
    {
        return $this->data;
    }
    
    
    /**
     * 设置图片数据
     * @param string $data
     * @return $this
     */
    public function setData(string $data) : self
    {
        $this->data = $data;
        
        return $this;
    }
}