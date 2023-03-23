<?php
declare(strict_types = 1);

namespace BusyPHP\upload\front;

use BusyPHP\helper\FileHelper;
use BusyPHP\upload\driver\LocalUpload;
use BusyPHP\upload\interfaces\FrontInterface;
use BusyPHP\upload\parameter\LocalParameter;
use BusyPHP\upload\parameter\PartCompleteParameter;
use BusyPHP\upload\parameter\PartInitParameter;
use BusyPHP\upload\parameter\PartPutParameter;
use League\Flysystem\FilesystemException;
use think\file\UploadedFile;
use think\filesystem\Driver;
use Throwable;

/**
 * 本地前端服务类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/24 3:58 PM Local.php $
 */
class Local implements FrontInterface
{
    /**
     * @var Driver
     */
    private $driver;
    
    
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getTmpToken(string $path, int $expire = 1800) : array
    {
        return [];
    }
    
    
    /**
     * @inheritDoc
     */
    public function getUrl(bool $ssl = false) : string
    {
        return 'local';
    }
    
    
    /**
     * @inheritDoc
     */
    public function prepareUpload(string $path, string $md5, int $filesize, string $mimetype = '', bool $part = false) : string
    {
        if (!$part) {
            return '';
        }
        
        $parameter = new PartInitParameter($path);
        $parameter->setMd5($md5);
        $parameter->setFilesize($filesize);
        $parameter->setMimetype($mimetype);
        
        return $this->driver->part()->init($parameter);
    }
    
    
    /**
     * 上传文件或分块
     * @param string       $path 存储路径
     * @param UploadedFile $file 文件对象
     * @param string       $uploadId UploadId
     * @param int          $partNumber 分块编号
     * @return string ETag
     * @throws Throwable
     */
    public function upload(string $path, UploadedFile $file, string $uploadId, int $partNumber) : string
    {
        if ($uploadId) {
            $result = $this->driver->part()->put(new PartPutParameter($file, $uploadId, $partNumber));
            
            return $result['etag'];
        } else {
            $local  = new LocalUpload($this->driver, $path);
            $result = $local->setParameter(new LocalParameter($file))->save();
            
            return $result->getMd5();
        }
    }
    
    
    /**
     * @inheritDoc
     * @throws FilesystemException
     */
    public function doneUpload(string $path, string $uploadId, array $parts) : array
    {
        if ($uploadId) {
            $result   = $this->driver->part()->complete(new PartCompleteParameter($uploadId, $parts));
            $filesize = $result['filesize'];
            $mimetype = $result['mimetype'];
        } else {
            $filesize = $this->driver->fileSize($path);
            $mimetype = $this->driver->mimeType($path);
        }
        
        $width  = 0;
        $height = 0;
        if (FileHelper::isCommonImageByPath($path)) {
            $imageInfo = $this->driver->image()->getInfo($path);
            $width     = $imageInfo->getWidth();
            $height    = $imageInfo->getHeight();
        }
        
        return [
            'mimetype' => $mimetype,
            'filesize' => $filesize,
            'width'    => $width,
            'height'   => $height,
        ];
    }
}