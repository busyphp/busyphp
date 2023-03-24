<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\helper\FileHelper;
use RuntimeException;

/**
 * 前端准备上传参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/24 5:16 PM SystemFilePrepareUploadParameter.php $
 */
class SystemFilePrepareUploadParameter
{
    /** @var string */
    private $md5;
    
    /** @var string */
    private $filename;
    
    /** @var int */
    private $filesize;
    
    /** @var string */
    private $mimetype;
    
    /** @var string */
    private $classType = '';
    
    /** @var string */
    private $classValue = '';
    
    /** @var int */
    private $userId = 0;
    
    /** @var bool */
    private $part = true;
    
    /** @var string */
    private $disk = '';
    
    
    /**
     * 构造函数
     * @param string $md5 文件MD5
     * @param string $filename 文件名(含扩展名)
     * @param int    $filesize 文件大小(字节)
     * @param string $mimetype 文件mimetype
     */
    public function __construct(string $md5, string $filename, int $filesize, string $mimetype)
    {
        if (!$md5) {
            throw new ParamInvalidException('md5');
        }
        if (!$filename) {
            throw new ParamInvalidException('filename');
        }
        if ($filesize <= 0) {
            throw new ParamInvalidException('filesize');
        }
        
        $this->md5      = $md5;
        $this->filename = $filename;
        $this->filesize = $filesize;
        $this->mimetype = $mimetype ?: FileHelper::getMimetypeByPath($filename);
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
     * 获取文件名(含扩展名)
     * @return string
     */
    public function getFilename() : string
    {
        if ('' === pathinfo($this->filename, PATHINFO_EXTENSION)) {
            throw new RuntimeException('文件名未包含扩展名');
        }
        
        return $this->filename;
    }
    
    
    /**
     * 获取文件大小(字节)
     * @return int
     */
    public function getFilesize() : int
    {
        return $this->filesize;
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
     * 获取文件分类
     * @return string
     */
    public function getClassType() : string
    {
        return $this->classType ?: SystemFile::FILE_TYPE_FILE;
    }
    
    
    /**
     * 设置文件分类
     * @param string $classType
     * @return $this
     */
    public function setClassType(string $classType) : self
    {
        $this->classType = $classType;
        
        return $this;
    }
    
    
    /**
     * 获取文件分类业务参数
     * @return string
     */
    public function getClassValue() : string
    {
        return $this->classValue;
    }
    
    
    /**
     * 设置文件分类业务参数
     * @param string $classValue
     * @return $this
     */
    public function setClassValue(string $classValue) : self
    {
        $this->classValue = $classValue;
        
        return $this;
    }
    
    
    /**
     * 获取用户ID
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId;
    }
    
    
    /**
     * 设置用户ID
     * @param int $userId
     * @return $this
     */
    public function setUserId(int $userId) : self
    {
        $this->userId = $userId;
        
        return $this;
    }
    
    
    /**
     * 是否启用分块上传
     * @return bool
     */
    public function isPart() : bool
    {
        return $this->part;
    }
    
    
    /**
     * 设置是否启用分块上传
     * @param bool $part
     * @return $this
     */
    public function setPart(bool $part) : self
    {
        $this->part = $part;
        
        return $this;
    }
    
    
    /**
     * 指定磁盘
     * @param string $disk
     * @return $this
     */
    public function setDisk(string $disk) : self
    {
        $this->disk = $disk;
        
        return $this;
    }
    
    
    /**
     * 获取磁盘
     * @return string
     */
    public function getDisk() : string
    {
        return $this->disk;
    }
}