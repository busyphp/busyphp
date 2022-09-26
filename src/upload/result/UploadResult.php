<?php
declare(strict_types = 1);

namespace BusyPHP\upload\result;

/**
 * 上传返回结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/20 2:04 PM UploadResult.php $
 */
class UploadResult
{
    /** @var string */
    private $basename = '';
    
    /** @var string */
    private $mimetype = '';
    
    /** @var int */
    private $filesize = 0;
    
    /** @var string */
    private $path = '';
    
    /** @var string */
    private $md5 = '';
    
    /** @var int */
    private $width = 0;
    
    /** @var int */
    private $height = 0;
    
    
    /**
     * 获取文件名(含扩展名)
     * @return string
     */
    public function getBasename() : string
    {
        return $this->basename;
    }
    
    
    /**
     * 设置文件名(含扩展名)
     * @param string $basename
     * @return $this
     */
    public function setBasename(string $basename) : self
    {
        $this->basename = $basename;
        
        return $this;
    }
    
    
    /**
     * 获取文件mimetype
     * @return string
     */
    public function getMimetype() : string
    {
        return $this->mimetype;
    }
    
    
    /**
     * 设置文件mimetype
     * @param string $mimetype
     * @return $this
     */
    public function setMimetype(string $mimetype) : self
    {
        $this->mimetype = $mimetype;
        
        return $this;
    }
    
    
    /**
     * 获取文件大小
     * @return int
     */
    public function getFilesize() : int
    {
        return $this->filesize;
    }
    
    
    /**
     * 设置文件大小
     * @param int $filesize
     * @return $this
     */
    public function setFilesize(int $filesize) : self
    {
        $this->filesize = $filesize;
        
        return $this;
    }
    
    
    /**
     * 获取文件路径
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }
    
    
    /**
     * 设置文件路径
     * @param string $path
     * @return $this
     */
    public function setPath(string $path) : self
    {
        $this->path = $path;
        
        return $this;
    }
    
    
    /**
     * 获取文件MD5
     * @return string
     */
    public function getMd5() : string
    {
        return $this->md5;
    }
    
    
    /**
     * 设置文件MD5
     * @param string $md5
     * @return $this
     */
    public function setMd5(string $md5) : self
    {
        $this->md5 = $md5;
        
        return $this;
    }
    
    
    /**
     * 获取文件宽度
     * @return int
     */
    public function getWidth() : int
    {
        return $this->width;
    }
    
    
    /**
     * 设置文件宽度
     * @param int $width
     * @return $this
     */
    public function setWidth(int $width) : self
    {
        $this->width = $width;
        
        return $this;
    }
    
    
    /**
     * 获取文件高度
     * @return int
     */
    public function getHeight() : int
    {
        return $this->height;
    }
    
    
    /**
     * 设置文件高度
     * @param int $height
     * @return $this
     */
    public function setHeight(int $height) : self
    {
        $this->height = $height;
        
        return $this;
    }
}