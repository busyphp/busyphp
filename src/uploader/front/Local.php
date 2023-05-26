<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\front;

use BusyPHP\facade\Uploader;
use BusyPHP\helper\FileHelper;
use BusyPHP\uploader\driver\local\LocalData;
use BusyPHP\uploader\driver\part\PartMergeData;
use BusyPHP\uploader\driver\part\PartPrepareData;
use BusyPHP\uploader\driver\part\PartPutData;
use BusyPHP\uploader\interfaces\FrontInterface;
use League\Flysystem\FilesystemException;
use think\File;
use think\filesystem\Driver;

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
    private Driver $driver;
    
    
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getFrontTmpToken(string $path, int $expire = 1800) : array
    {
        return [];
    }
    
    
    /**
     * @inheritDoc
     */
    public function getFrontServerUrl(bool $ssl = false) : string
    {
        return 'local';
    }
    
    
    /**
     * @inheritDoc
     */
    public function frontPrepareUpload(string $path, string $basename, string $md5, int $filesize, string $mimetype = '', bool $part = true) : string
    {
        if (!$part) {
            return '';
        }
        
        $data = new PartPrepareData($basename, $md5, $mimetype, $filesize);
        
        return $this->driver->part()->prepare($path, $data);
    }
    
    
    /**
     * 上传文件或分块
     * @param string      $path 存储路径
     * @param string|File $file 文件对象或文件内容
     * @param string      $uploadId UploadId
     * @param int         $partNumber 分块编号
     * @return array{etag: string, filesize: int, part_number: int} 该数据用于执行 {@see Local::frontDoneUpload()} 时回传
     */
    public function upload(string $path, string|File $file, string $uploadId, int $partNumber) : array
    {
        if ($uploadId) {
            return $this->driver->part()->put(new PartPutData($file, $uploadId, $partNumber));
        } else {
            $result = Uploader::disk($this->driver)->path($path)->upload(new LocalData($file));
            
            return [
                'etag'        => $result->getMd5(),
                'filesize'    => $result->getFilesize(),
                'part_number' => 0
            ];
        }
    }
    
    
    /**
     * @inheritDoc
     * @throws FilesystemException
     */
    public function frontDoneUpload(string $path, string $uploadId, array $parts) : array
    {
        if ($uploadId) {
            $result   = $this->driver->part()->merge(new PartMergeData($uploadId, $parts));
            $filesize = $result->getFilesize();
            $mimetype = $result->getMimetype();
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