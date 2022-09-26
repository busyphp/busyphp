<?php
declare(strict_types = 1);

namespace BusyPHP\upload\parameter;

use InvalidArgumentException;

/**
 * 初始分块驱动参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/22 8:58 AM PartInitParameter.php $
 */
class PartInitParameter
{
    /** @var string */
    private $path;
    
    /** @var string */
    private $basename = '';
    
    /** @var int */
    private $filesize = 0;
    
    /** @var string */
    private $md5 = '';
    
    /** @var string */
    private $mimetype = '';
    
    /** @var string */
    private $tmpDisk = '';
    
    /** @var string */
    private $tmpDir = '';
    
    
    /**
     * 构造函数
     * @param string $path 上传文件的存储路径
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }
    
    
    /**
     * 获取存储路径
     * @return string
     */
    public function getPath() : string
    {
        if (!$this->path) {
            throw new InvalidArgumentException('存储路径不能为空');
        }
        
        return $this->path;
    }
    
    
    /**
     * 获取上传文件的原名称含扩展名，如：图片.jpeg
     * @return string
     */
    public function getBasename() : string
    {
        if ('' !== $this->basename && '' === pathinfo($this->basename, PATHINFO_EXTENSION)) {
            throw new InvalidArgumentException('文件原名未包含扩展名');
        }
        
        return $this->basename;
    }
    
    
    /**
     * 设置上传文件的原名称含扩展名，如：图片.jpeg
     * @param string $basename
     * @return $this
     */
    public function setBasename(string $basename) : self
    {
        $this->basename = $basename;
        
        return $this;
    }
    
    
    /**
     * 获取上传文件的大小
     * @return int
     */
    public function getFilesize() : int
    {
        return $this->filesize;
    }
    
    
    /**
     * 设置上传文件的大小
     * @param int $filesize
     * @return $this
     */
    public function setFilesize(int $filesize) : self
    {
        $this->filesize = $filesize;
        
        return $this;
    }
    
    
    /**
     * 获取上传文件的mimetype
     * @return string
     */
    public function getMimetype() : string
    {
        return $this->mimetype;
    }
    
    
    /**
     * 设置上传文件的mimetype
     * @param string $mimetype
     * @return $this
     */
    public function setMimetype(string $mimetype) : self
    {
        $this->mimetype = $mimetype;
        
        return $this;
    }
    
    
    /**
     * 获取分块暂存文件系统
     * @return string
     */
    public function getTmpDisk() : string
    {
        return $this->tmpDisk;
    }
    
    
    /**
     * 设置分块暂存文件系统
     * @param string $tmpDisk
     * @return $this
     */
    public function setTmpDisk(string $tmpDisk) : self
    {
        $this->tmpDisk = $tmpDisk;
        
        return $this;
    }
    
    
    /**
     * 获取分块暂存文件目录
     * @return string
     */
    public function getTmpDir() : string
    {
        return $this->tmpDir;
    }
    
    
    /**
     * 设置分块暂存文件目录
     * @param string $tmpDir
     * @return $this
     */
    public function setTmpDir(string $tmpDir) : self
    {
        $this->tmpDir = $tmpDir;
        
        return $this;
    }
    
    
    /**
     * 获取文件MD5值
     * @return string
     */
    public function getMd5() : string
    {
        return $this->md5;
    }
    
    
    /**
     * 设置文件MD5值
     * @param string $md5
     * @return $this
     */
    public function setMd5(string $md5) : self
    {
        $this->md5 = $md5;
        
        return $this;
    }
}