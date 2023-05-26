<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\driver\part;

use BusyPHP\uploader\concern\BasenameMimetypeConcern;
use BusyPHP\uploader\interfaces\DataInterface;

/**
 * 分块预备上传参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/21 2:53 PM PartPrepareData.php $
 */
class PartPrepareData implements DataInterface
{
    use BasenameMimetypeConcern;
    
    /**
     * @var string
     */
    protected string $originalName;
    
    /**
     * @var int
     */
    protected int $filesize;
    
    /**
     * @var string
     */
    protected string $md5;
    
    /**
     * @var string
     */
    protected string $tmpDisk = '';
    
    /**
     * @var string
     */
    protected string $tmpDir = '';
    
    
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
     * @return static
     */
    public function setMd5(string $md5) : static
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
     * @return static
     */
    public function setTmpDisk(string $tmpDisk) : static
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
     * @return static
     */
    public function setTmpDir(string $tmpDir) : static
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