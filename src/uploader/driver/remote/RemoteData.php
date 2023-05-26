<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\driver\remote;

use BusyPHP\uploader\concern\BasenameMimetypeConcern;
use BusyPHP\uploader\interfaces\DataInterface;
use Closure;
use think\filesystem\Driver;

/**
 * 远程文件下载到磁盘参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/20 5:49 PM RemoteData.php $
 */
class RemoteData implements DataInterface
{
    use BasenameMimetypeConcern;
    
    /**
     * @var string
     */
    protected string $url;
    
    /**
     * @var Closure|null
     */
    protected Closure|null $progress = null;
    
    /**
     * @var string|Driver
     */
    protected string|Driver $tmpDisk = '';
    
    /**
     * @var string
     */
    protected string $tmpDir = '';
    
    /**
     * @var array
     */
    protected array $options = [];
    
    /**
     * @var array
     */
    protected array $headOptions = [];
    
    /**
     * @var array
     */
    protected array $getOptions = [];
    
    /**
     * @var array
     */
    protected array $ignoreHosts = [];
    
    
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
     * @return static
     */
    public function setProgress($progress) : static
    {
        $this->progress = $progress;
        
        return $this;
    }
    
    
    /**
     * 获取下载进度回调
     * @return Closure|null
     */
    public function getProgress() : Closure|null
    {
        return $this->progress;
    }
    
    
    /**
     * 设置临时存储磁盘系统
     * @param string|Driver $tmpDisk
     * @return static
     */
    public function setTmpDisk($tmpDisk) : static
    {
        $this->tmpDisk = $tmpDisk;
        
        return $this;
    }
    
    
    /**
     * 获取临时存储磁盘系统
     * @return string|Driver
     */
    public function getTmpDisk() : Driver|string
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
     * @return static
     */
    public function setOptions(array $options) : static
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
     * @return static
     */
    public function setHeadOptions(array $headOptions) : static
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
     * @return static
     */
    public function setGetOptions(array $getOptions) : static
    {
        $this->getOptions = $getOptions;
        
        return $this;
    }
    
    
    /**
     * 设置下载忽略域名
     * @param array $ignoreHosts
     * @return static
     */
    public function setIgnoreHosts(array $ignoreHosts) : static
    {
        $this->ignoreHosts = $ignoreHosts;
        
        return $this;
    }
    
    
    /**
     * 获取下载忽略域名
     * @return array
     */
    public function getIgnoreHosts() : array
    {
        return $this->ignoreHosts;
    }
}