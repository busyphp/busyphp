<?php
declare(strict_types = 1);

namespace BusyPHP\file\upload;

use BusyPHP\file\Upload;
use Exception;
use think\exception\FileException;
use think\File;
use think\file\UploadedFile;

/**
 * 移动文件上传类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/22 下午上午10:54 MoveUpload.php $
 */
class MoveUpload extends Upload
{
    /**
     * 文件名处理
     * @var callable
     */
    protected $name = null;
    
    
    /**
     * 设置文件名回调
     * @param callable $name
     * @return $this
     */
    public function setName(callable $name) : self
    {
        $this->name = $name;
        
        return $this;
    }
    
    
    /**
     * 上传处理
     * @param File|string $file 移动的文件对象或文件路径
     * @return array [文件名称,文件对象,图像信息]
     * @throws Exception
     */
    protected function handle($file) : array
    {
        if (!$file instanceof File) {
            $file = new File($file);
        }
        
        $realPath = $file->getRealPath();
        if (!is_file($realPath)) {
            throw new FileException("文件不存在: {$realPath}");
        }
        
        if ($file instanceof UploadedFile) {
            $extension = $file->getOriginalExtension();
            $mimeType  = $file->getOriginalMime();
            $name      = $file->getOriginalName();
        } else {
            $extension = $file->getExtension();
            $mimeType  = $file->getMime();
            $name      = $file->getBasename();
        }
        
        if (is_callable($this->name)) {
            $name = call_user_func_array($this->name, [$file]);
        }
        
        $this->checkExtension($extension);
        $this->checkFileSize($file->getSize());
        $this->checkMimeType($mimeType);
        $imageInfo = $this->checkImage($realPath, $extension);
        
        $path   = $this->createFilename($file);
        $dir    = pathinfo($path, PATHINFO_DIRNAME);
        $system = $this->fileSystem();
        if (!$system->createDir($dir)) {
            throw new FileException("创建文件夹失败: {$dir}");
        }
        
        if (!rename($realPath, $system->path($path))) {
            throw new FileException("移动文件失败: {$realPath}");
        }
        
        return [$name, $path, $imageInfo];
    }
}