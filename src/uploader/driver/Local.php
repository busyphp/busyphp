<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\driver;

use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\helper\FileHelper;
use BusyPHP\uploader\Driver;
use BusyPHP\uploader\driver\local\LocalData;
use BusyPHP\uploader\result\UploadResult;
use think\file\UploadedFile;
use Throwable;

/**
 * 本地上传类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/20 下午下午4:06 LocalUpload.php $
 * @property LocalData $data
 */
class Local extends Driver
{
    /**
     * 执行上传
     * @return UploadResult
     * @throws Throwable
     */
    protected function handle() : UploadResult
    {
        if (!$this->data instanceof LocalData) {
            throw new ClassNotExtendsException($this->data, LocalData::class);
        }
        
        $file = $this->data->getFile();
        switch (true) {
            case $file instanceof UploadedFile:
                $basename = $file->getOriginalName();
                $mimetype = $file->getOriginalMime();
            break;
            default:
                $basename = $file->getBasename();
                $mimetype = $file->getMime();
        }
        
        $basename  = $this->data->getBasename($file, $basename);
        $mimetype  = $this->data->getMimetype($file, $mimetype, $basename);
        $extension = pathinfo($basename, PATHINFO_EXTENSION);
        
        // 校验文件
        try {
            $this->checkExtension($extension);
            $this->checkFilesize($filesize = $file->getSize());
            $this->checkMimetype($mimetype);
            [$width, $height] = FileHelper::checkImage($file->getPathname(), $extension);
            $md5 = $file->md5();
        } catch (Throwable $e) {
            unlink($file->getPathname());
            
            throw $e;
        }
        
        $result = new UploadResult();
        $result->setBasename($basename);
        $result->setPath($this->putFileToDisk($file));
        $result->setMd5($md5);
        $result->setMimetype($mimetype);
        $result->setWidth($width);
        $result->setHeight($height);
        $result->setFilesize($filesize);
        
        return $result;
    }
    
    
    /**
     * @inheritDoc
     */
    public static function configName() : string
    {
        return 'local';
    }
}