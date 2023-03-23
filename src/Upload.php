<?php
declare(strict_types = 1);

namespace BusyPHP;

use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\helper\FileHelper;
use BusyPHP\upload\interfaces\BindDriverParameterInterface;
use BusyPHP\upload\result\UploadResult;
use Closure;
use League\Flysystem\FilesystemException;
use LogicException;
use think\Container;
use think\exception\FileException;
use think\exception\InvalidArgumentException;
use think\facade\Filesystem;
use think\File;
use think\filesystem\Driver;

/**
 * 上传驱动基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/19 12:21 PM Driver.php $
 */
abstract class Upload
{
    /** @var Driver */
    protected $disk;
    
    /** @var bool */
    protected $local;
    
    /** @var string|callable|Closure */
    protected $path;
    
    /** @var string[] */
    protected $limitMimetypes = [];
    
    /** @var string[] */
    protected $limitExtensions = [];
    
    /** @var int */
    protected $limitMaxsize = 0;
    
    /** @var BindDriverParameterInterface */
    protected $parameter;
    
    
    /**
     * 构造
     * @param Driver|string                $disk 磁盘系统
     * @param string|callable|Closure|null $path 保存的文件路径
     */
    public function __construct($disk, $path = null)
    {
        if (!$disk instanceof Driver) {
            $disk = Filesystem::disk((string) $disk);
        }
        
        $this->disk = $disk;
        $this->path = $path;
    }
    
    
    /**
     * 设置保存文件路径
     * @param string|callable|Closure $path
     * @return static
     */
    public function setPath($path) : static
    {
        $this->path = $path;
        
        return $this;
    }
    
    
    /**
     * 限制文件大小
     * @param int $maxsize
     * @return static
     */
    public function limitMaxsize(int $maxsize) : static
    {
        $this->limitMaxsize = $maxsize;
        
        return $this;
    }
    
    
    /**
     * 限制文件扩展名
     * @param string[] $extensions
     * @return static
     */
    public function limitExtensions(array $extensions) : static
    {
        $this->limitExtensions = $extensions;
        
        return $this;
    }
    
    
    /**
     * 限制文件mimetype
     * @param string[] $mimetypes
     * @return static
     */
    public function limitMimetypes(array $mimetypes) : static
    {
        $this->limitMimetypes = $mimetypes;
        
        return $this;
    }
    
    
    /**
     * 设置上传参数
     * @param BindDriverParameterInterface $parameter
     * @return static
     */
    public function setParameter(BindDriverParameterInterface $parameter) : static
    {
        $this->parameter = $parameter;
        
        return $this;
    }
    
    
    /**
     * 执行上传
     * @return UploadResult
     */
    public function save() : UploadResult
    {
        if (!$this->parameter) {
            throw new InvalidArgumentException('没有设置上传参数');
        }
        
        return $this->handle();
    }
    
    
    /**
     * 处理上传
     * @return UploadResult
     */
    abstract protected function handle() : UploadResult;
    
    
    /**
     * 写入文件到磁盘上
     * @param File   $file 文件对象
     * @param string $path 文件相对路径
     * @return string
     * @throws FilesystemException
     */
    protected function putFileToDisk(File $file, string $path = '') : string
    {
        $path = $path === '' ? $this->buildPath($file, $file->extension()) : $path;
        
        // 本地磁盘的话，直接移动效率最高
        if ($this->disk->isLocal()) {
            $info = pathinfo($this->disk->path($path));
            $file->move($info['dirname'], $info['basename']);
            
            return $path;
        }
        
        // 复制文件并删除
        $this->copyFileToDisk($file, $path);
        unlink($file->getPathname());
        
        return $path;
    }
    
    
    /**
     * 复制文件到磁盘上
     * @param File   $file 文件对象
     * @param string $path 文件相对路径
     * @return string
     * @throws FilesystemException
     */
    protected function copyFileToDisk(File $file, string $path = '') : string
    {
        $path = $path === '' ? $this->buildPath($file, $file->extension()) : $path;
        
        try {
            if (!$stream = fopen($file->getPathname(), 'rb')) {
                throw new FileException("读取文件失败: {$file->getPathname()}");
            }
            $this->disk->writeStream($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
        
        return $path;
    }
    
    
    /**
     * 写入内容到文件
     * @param string $content 文件内容
     * @param string $extension 文件扩展名
     * @param string $path 文件相对路径
     * @return string
     * @throws FilesystemException
     */
    protected function putContentToDisk(string $content, string $extension, string $path = '') : string
    {
        $path = $path === '' ? $this->buildPath($content, $extension) : $path;
        $this->disk->write($path, $content);
        
        return $path;
    }
    
    
    /**
     * 构建保存的文件路径
     * @param File|string $file
     * @param string      $extension
     * @return string
     */
    protected function buildPath($file, string $extension = '') : string
    {
        if ($extension === '' && $file instanceof File) {
            $extension = $file->extension();
        }
        
        if (is_null($this->path)) {
            if ($extension === '') {
                throw new InvalidArgumentException('扩展名不能为空');
            }
            
            $path = sprintf('%s.%s', date('YmdHis'), $extension);
        } elseif (is_callable($this->path)) {
            if ($extension === '') {
                throw new InvalidArgumentException('扩展名不能为空');
            }
            
            $path = (string) Container::getInstance()->invokeFunction($this->path, [$file, $extension]);
        } else {
            $path = (string) $this->path;
        }
        
        if ($path === '') {
            throw new LogicException('保存的文件路径为空');
        }
        
        return $path;
    }
    
    
    /**
     * 检测文件扩展名是否合规
     * @param string $extension 文件扩展名，不含.
     */
    protected function checkExtension(string $extension)
    {
        FileHelper::checkExtension($this->limitExtensions, $extension);
    }
    
    
    /**
     * 检测文件MimeType是否合规
     * @param string $mimetype
     */
    protected function checkMimetype(string $mimetype)
    {
        FileHelper::checkMimetype($this->limitMimetypes, $mimetype);
    }
    
    
    /**
     * 检测文件大小是否合规
     * @param int $filesize
     */
    protected function checkFilesize(int $filesize)
    {
        FileHelper::checkFilesize($this->limitMaxsize, $filesize);
    }
    
    
    /**
     * 通过参数模版获取上传驱动实例
     * @param BindDriverParameterInterface $parameter
     * @param Driver|string                $disk 磁盘系统
     * @param string|callable|Closure|null $path 保存的文件路径
     * @return Upload
     */
    public static function init(BindDriverParameterInterface $parameter, $disk, $path = null) : Upload
    {
        $object = Container::getInstance()->make($parameter->getDriver(), [$disk, $path], true);
        if (!$object instanceof Upload) {
            throw new ClassNotExtendsException($object, Upload::class);
        }
        
        return $object->setParameter($parameter);
    }
}