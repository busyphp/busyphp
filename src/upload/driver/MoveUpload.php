<?php
declare(strict_types = 1);

namespace BusyPHP\upload\driver;

use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\exception\FileNotFoundException;
use BusyPHP\helper\FileHelper;
use BusyPHP\upload\Driver;
use BusyPHP\upload\parameter\MoveParameter;
use BusyPHP\upload\result\UploadResult;
use ReflectionException;
use think\File;
use think\file\UploadedFile;

/**
 * 移动文件上传类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/22 下午上午10:54 MoveUpload.php $
 */
class MoveUpload extends Driver
{
    /**
     * 执行上传
     * @param MoveParameter $parameter
     * @return UploadResult
     * @throws ReflectionException
     */
    public function upload($parameter) : UploadResult
    {
        if (!$parameter instanceof MoveParameter) {
            throw new ClassNotExtendsException($parameter, MoveParameter::class);
        }
        
        $file = $parameter->getFile();
        if (!$file instanceof File) {
            $file = new File($file, false);
        }
        
        if (!$file->isFile()) {
            throw new FileNotFoundException($file->getPathname());
        }
        
        if ($file instanceof UploadedFile) {
            $mimetype = $file->getOriginalMime();
            $basename = $file->getOriginalName();
        } else {
            $mimetype = $file->getMime();
            $basename = $file->getBasename();
        }
        
        $basename  = $parameter->getBasename($file, $basename);
        $mimetype  = $parameter->getMimetype($file, $mimetype, $basename);
        $extension = pathinfo($basename, PATHINFO_EXTENSION);
        
        // 校验文件
        $this->checkExtension($extension);
        $this->checkFilesize($filesize = $file->getSize());
        $this->checkMimetype($mimetype);
        [$width, $height] = FileHelper::checkImage($file->getRealPath(), $extension);
        $md5 = $file->md5();
        
        // 本地磁盘，且不保留源文件，直接移动，效率最高
        if ($this->disk->isLocal() && !$parameter->isRetain()) {
            $info = pathinfo($this->disk->path($path = $this->buildPath($file, $extension)));
            $file->move($info['dirname'], $info['basename']);
        }
        
        //
        // 复制文件到磁盘中
        else {
            $path = $this->copyFileToDisk($file);
            
            // 不保留源文件
            if (!$parameter->isRetain()) {
                unlink($file->getRealPath());
            }
        }
        
        $result = new UploadResult();
        $result->setBasename($basename);
        $result->setMimetype($mimetype);
        $result->setPath($path);
        $result->setFilesize($filesize);
        $result->setMd5($md5);
        $result->setWidth($width);
        $result->setHeight($height);
        
        return $result;
    }
}