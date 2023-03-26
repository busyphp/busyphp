<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\filesystem;

use BusyPHP\image\Driver as ImageDriver;
use BusyPHP\upload\interfaces\FrontInterface;
use BusyPHP\upload\interfaces\PartInterface;
use Closure;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use RuntimeException;
use think\Cache;
use think\facade\Route;
use think\File;
use think\route\Url;

/**
 * 文件系统驱动类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/17 9:00 PM Driver.php $
 * @mixin Filesystem
 */
abstract class Driver
{
    /** @var Cache */
    protected $cache;
    
    /** @var Filesystem */
    protected $filesystem;
    
    /** @var ImageDriver */
    protected $imageDriver;
    
    /** @var PartInterface */
    protected $partDriver;
    
    /** @var FrontInterface */
    protected $frontDriver;
    
    /** @var FilesystemAdapter */
    protected $adapter;
    
    /**
     * 配置参数
     * @var array
     */
    protected $config = [];
    
    
    public function __construct(Cache $cache, array $config)
    {
        $this->cache      = $cache;
        $this->config     = array_merge($this->config, $config);
        $this->adapter    = $this->createAdapter();
        $this->filesystem = $this->createFilesystem($this->adapter);
    }
    
    
    /**
     * 创建文件驱动适配器
     * @return FilesystemAdapter
     */
    abstract protected function createAdapter() : FilesystemAdapter;
    
    
    /**
     * 创建图片处理驱动
     * @return ImageDriver
     */
    abstract protected function createImageDriver() : ImageDriver;
    
    
    /**
     * 创建分块上传驱动
     * @return PartInterface
     */
    abstract protected function createPart() : PartInterface;
    
    
    /**
     * 创建前端服务驱动
     * @return FrontInterface
     */
    abstract protected function createFront() : FrontInterface;
    
    
    /**
     * 创建文件操作系统
     * @param FilesystemAdapter $adapter
     * @return Filesystem
     */
    protected function createFilesystem(FilesystemAdapter $adapter) : Filesystem
    {
        return new Filesystem($adapter, array_intersect_key($this->config, array_flip([
            'visibility',
            'disable_asserts',
            'url'
        ])));
    }
    
    
    /**
     * 获取存储设置
     * @return \BusyPHP\app\admin\setting\StorageSetting|null
     */
    protected function getStorageSetting() : ?\BusyPHP\app\admin\setting\StorageSetting
    {
        if (class_exists('\BusyPHP\app\admin\setting\StorageSetting')) {
            return \BusyPHP\app\admin\setting\StorageSetting::instance();
        }
        
        return null;
    }
    
    
    /**
     * @return FilesystemAdapter
     */
    public function getAdapter() : FilesystemAdapter
    {
        return $this->adapter;
    }
    
    
    /**
     * 获取文件完整路径
     * @param string $path
     * @return string
     */
    public function path(string $path) : string
    {
        return $path;
    }
    
    
    /**
     * 拼接URL
     * @param string $url URL
     * @param string $path 文件路径
     * @return string
     */
    protected function concatPathToUrl(string $url, string $path) : string
    {
        return rtrim($url, '/') . '/' . ltrim($path, '/');
    }
    
    
    /**
     * 生成URL
     * @param string $path
     * @return string
     */
    public function url(string $path) : string
    {
        throw new RuntimeException('This driver does not support retrieving URLs.');
    }
    
    
    /**
     * 从URL中匹配出相对路径，如果不符合规则请返回null
     * @param string $url
     * @return string|null
     */
    public function matchRelativePathByURL(string $url) : ?string
    {
        return null;
    }
    
    
    /**
     * 获取磁盘支持的域名
     * @return array
     */
    public function getDomains() : array
    {
        return [];
    }
    
    
    /**
     * 保存文件
     * @param string              $path 路径
     * @param File                $file 文件
     * @param null|string|Closure $rule 文件名规则
     * @param array               $options 参数
     * @return bool|string
     */
    public function putFile(string $path, File $file, $rule = null, array $options = [])
    {
        return $this->putFileAs($path, $file, $file->hashName($rule), $options);
    }
    
    
    /**
     * 指定文件名保存文件
     * @param string $path 路径
     * @param File   $file 文件
     * @param string $name 文件名
     * @param array  $options 参数
     * @return bool|string
     */
    public function putFileAs(string $path, File $file, string $name, array $options = [])
    {
        $stream = fopen($file->getRealPath(), 'r');
        $path   = trim($path . '/' . $name, '/');
        
        $result = $this->put($path, $stream, $options);
        
        if (is_resource($stream)) {
            fclose($stream);
        }
        
        return $result ? $path : false;
    }
    
    
    /**
     * 保存stream到文件
     * @param string   $path 路径
     * @param resource $contents stream
     * @param array    $options 参数
     * @return bool
     */
    protected function put(string $path, $contents, array $options = []) : bool
    {
        try {
            $this->writeStream($path, $contents, $options);
        } catch (UnableToWriteFile|UnableToSetVisibility|FilesystemException $e) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * 图片处理驱动
     * @return ImageDriver
     */
    public function image() : ImageDriver
    {
        if (!$this->imageDriver) {
            $this->imageDriver = $this->createImageDriver();
        }
        
        return $this->imageDriver;
    }
    
    
    /**
     * 分块上传驱动
     * @return PartInterface
     */
    public function part() : PartInterface
    {
        if (!$this->partDriver) {
            $this->partDriver = $this->createPart();
        }
        
        return $this->partDriver;
    }
    
    
    /**
     * 获取前端服务驱动
     * @return FrontInterface
     */
    public function front() : FrontInterface
    {
        if (!$this->frontDriver) {
            $this->frontDriver = $this->createFront();
        }
        
        return $this->frontDriver;
    }
    
    
    /**
     * 是否本地磁盘
     * @return bool
     */
    public function isLocal() : bool
    {
        return ($this->config['type'] ?? '') == 'local';
    }
    
    
    /**
     * 转换路径为URL
     * @param string $path 路径
     * @param string $domain 不含协议的域名
     * @param string $scheme 请求协议，支持 https, http, 空字符为协议跟随
     * @param array  $vars 参数
     * @return Url
     */
    protected function convertPathToUrl(string $path, string $domain, string $scheme = '', array $vars = []) : Url
    {
        if ($scheme === 'http') {
            $domain = 'http://' . $domain;
        }
        
        return Route::buildUrl('/' . ltrim($path, '/'), $vars)
            ->https($scheme === 'https')
            ->domain($domain)
            ->suffix(false);
    }
    
    
    /**
     * 合并filesystem方法
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->filesystem->$method(...$parameters);
    }
}
