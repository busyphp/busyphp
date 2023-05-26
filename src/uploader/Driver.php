<?php
declare(strict_types = 1);

namespace BusyPHP\uploader;

use BusyPHP\helper\FileHelper;
use BusyPHP\helper\FilesystemHelper;
use BusyPHP\uploader\interfaces\DataInterface;
use BusyPHP\uploader\result\UploadResult;
use Closure;
use League\Flysystem\FilesystemException;
use LogicException;
use think\Container;
use think\exception\FileException;
use think\exception\InvalidArgumentException;
use think\facade\Filesystem;
use think\File;
use think\filesystem\Driver as FilesystemDriver;

/**
 * Uploader上传驱动基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/5/25 23:37 Driver.php $
 */
abstract class Driver
{
    protected array $config = [];
    
    /**
     * @var string[]
     */
    protected array $limitMimetypes = [];
    
    /**
     * @var string[]
     */
    protected array $limitExtensions = [];
    
    /**
     * @var int
     */
    protected int $limitMaxsize = 0;
    
    /**
     * @var DataInterface
     */
    protected DataInterface $data;
    
    /**
     * @var FilesystemDriver
     */
    protected FilesystemDriver $disk;
    
    /**
     * @var Closure|string|null
     */
    protected Closure|string|null $path = null;
    
    
    /**
     * 构造函数
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_merge($this->config, $config);
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
     * 指定上传磁盘
     * @param FilesystemDriver|string $disk
     * @return static
     */
    public function disk(string|FilesystemDriver $disk) : static
    {
        if (!$disk instanceof FilesystemDriver) {
            $disk = Filesystem::disk($disk);
        }
        
        $this->disk = $disk;
        
        return $this;
    }
    
    
    /**
     * 设置保存路径
     * @param string|Closure $path
     * @return $this
     */
    public function path(string|Closure $path) : static
    {
        $this->path = $path;
        
        return $this;
    }
    
    
    /**
     * 执行上传
     * @param DataInterface $data 上传参数
     * @return UploadResult
     */
    public function upload(DataInterface $data) : UploadResult
    {
        $this->data = $data;
        
        if (!isset($this->disk)) {
            $this->disk(FilesystemHelper::public());
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
    protected function buildPath(File|string $file, string $extension = '') : string
    {
        if ('' === $extension && $file instanceof File) {
            $extension = $file->extension();
        }
        
        if (null === $this->path) {
            if ($extension === '') {
                throw new InvalidArgumentException('扩展名不能为空');
            }
            
            $path = sprintf('%s.%s', date('YmdHis'), $extension);
        } elseif ($this->path instanceof Closure) {
            if ($extension === '') {
                throw new InvalidArgumentException('扩展名不能为空');
            }
            
            $path = (string) Container::getInstance()->invokeFunction($this->path, [$file, $extension]);
        } else {
            $path = (string) $this->path;
        }
        
        if ('' === $path) {
            throw new LogicException('保存的文件路径为空');
        }
        
        return $path;
    }
    
    
    /**
     * 检测文件扩展名是否合规
     * @param string $extension 文件扩展名，不含.
     */
    protected function checkExtension(string $extension) : void
    {
        FileHelper::checkExtension($this->limitExtensions, $extension);
    }
    
    
    /**
     * 检测文件MimeType是否合规
     * @param string $mimetype
     */
    protected function checkMimetype(string $mimetype) : void
    {
        FileHelper::checkMimetype($this->limitMimetypes, $mimetype);
    }
    
    
    /**
     * 检测文件大小是否合规
     * @param int $filesize
     */
    protected function checkFilesize(int $filesize) : void
    {
        FileHelper::checkFilesize($this->limitMaxsize, $filesize);
    }
    
    
    /**
     * 定义驱动配置名称
     * @return mixed
     */
    public static function configName() : string
    {
        return static::class;
    }
}