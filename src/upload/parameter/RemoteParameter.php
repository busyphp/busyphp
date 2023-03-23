<?php
declare(strict_types = 1);

namespace BusyPHP\upload\parameter;

use BusyPHP\upload\concern\BasenameMimetypeConcern;
use BusyPHP\Upload;
use BusyPHP\upload\driver\RemoteUpload;
use BusyPHP\upload\interfaces\BindDriverParameterInterface;
use Closure;
use think\filesystem\Driver as FilesystemDriver;

/**
 * 远程文件下载到磁盘参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/20 5:49 PM RemoteParameter.php $
 */
class RemoteParameter implements BindDriverParameterInterface
{
    use BasenameMimetypeConcern;
    
    /** @var string */
    protected $url;
    
    /** @var Closure|callable */
    protected $progress = null;
    
    /** @var string|FilesystemDriver */
    protected $tmpDisk = '';
    
    /** @var string */
    protected $tmpDir = '';
    
    /** @var array */
    protected $options = [];
    
    /** @var array */
    protected $headOptions = [];
    
    /** @var array */
    protected $getOptions = [];
    
    
    /**
     * 构造函数
     * @param string $url 文件网址
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }
    
    
    /**
     * 获取下载地址
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }
    
    
    /**
     * 设置下载进度回调
     * @param callable|Closure $progress
     */
    public function setProgress($progress) : self
    {
        $this->progress = $progress;
        
        return $this;
    }
    
    
    /**
     * 获取下载进度回调
     * @return callable|Closure
     */
    public function getProgress()
    {
        return $this->progress;
    }
    
    
    /**
     * 设置临时存储磁盘系统
     * @param string|FilesystemDriver $tmpDisk
     * @return self
     */
    public function setTmpDisk($tmpDisk) : self
    {
        $this->tmpDisk = $tmpDisk;
        
        return $this;
    }
    
    
    /**
     * 获取临时存储磁盘系统
     * @return string|FilesystemDriver
     */
    public function getTmpDisk()
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
    
    
    /**
     * 获取HTTP全局配置
     * @return array
     */
    public function getOptions() : array
    {
        return $this->options;
    }
    
    
    /**
     * 设置HTTP全局配置
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options) : self
    {
        $this->options = $options;
        
        return $this;
    }
    
    
    /**
     * 获取HEAD请求配置
     * @return array
     */
    public function getHeadOptions() : array
    {
        return $this->headOptions;
    }
    
    
    /**
     * 设置HEAD请求配置
     * @param array $headOptions
     * @return $this
     */
    public function setHeadOptions(array $headOptions) : self
    {
        $this->headOptions = $headOptions;
        
        return $this;
    }
    
    
    /**
     * 获取GET请求配置
     * @return array
     */
    public function getGetOptions() : array
    {
        return $this->getOptions;
    }
    
    
    /**
     * 设置GET请求配置
     * @param array $getOptions
     * @return $this
     */
    public function setGetOptions(array $getOptions) : self
    {
        $this->getOptions = $getOptions;
        
        return $this;
    }
    
    
    /**
     * 获取上传驱动类
     * @return class-string<Upload>
     */
    public function getDriver() : string
    {
        return RemoteUpload::class;
    }
}