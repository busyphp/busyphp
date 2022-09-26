<?php
declare(strict_types = 1);

namespace BusyPHP\upload\parameter;

use BusyPHP\upload\concern\BasenameMimetypeConcern;

/**
 * 初始化分块上传参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/21 2:53 PM PartCreateParameter.php $
 */
class PartCreateParameter
{
    use BasenameMimetypeConcern;
    
    /** @var string */
    protected $originalName;
    
    /** @var string */
    protected $tmpDisk = '';
    
    /** @var string */
    protected $tmpDir = '';
    
    /** @var int */
    protected $filesize;
    
    /** @var string */
    protected $md5;
    
    
    /**
     * 构造函数
     * @param string $basename 文件原名(含扩展名)
     * @param string $md5 文件MD5值
     * @param string $mimetype 文件mimetype
     * @param int    $filesize 文件大小
     */
    public function __construct(string $basename, string $md5 = '', string $mimetype = '', int $filesize = 0)
    {
        $this->originalName = $basename;
        $this->md5          = $md5;
        $this->filesize     = $filesize;
        $this->setBasename($basename);
        $this->setMimetype($mimetype);
    }
    
    
    /**
     * 获取文件原名(含扩展名)
     * @return string
     */
    public function getOriginalName() : string
    {
        return $this->originalName;
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
    
    
    /**
     * 获取文件大小
     * @return int
     */
    public function getFilesize() : int
    {
        return $this->filesize;
    }
    
    
    /**
     * 设置临时存储磁盘系统
     * @param string $tmpDisk
     * @return self
     */
    public function setTmpDisk(string $tmpDisk) : self
    {
        $this->tmpDisk = $tmpDisk;
        
        return $this;
    }
    
    
    /**
     * 获取临时存储磁盘系统
     * @return string
     */
    public function getTmpDisk() : string
    {
        return $this->tmpDisk;
    }
    
    
    /**
     * 设置临时存储目录名称
     * @param string $tmpDir
     * @return self
     */
    public function setTmpDir(string $tmpDir) : self
    {
        $this->tmpDir = $tmpDir;
        
        return $this;
    }
    
    
    /**
     * 获取临时存储目录名称
     * @return string
     */
    public function getTmpDir() : string
    {
        return $this->tmpDir;
    }
}