<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\driver;

use BusyPHP\uploader\Driver;
use BusyPHP\uploader\driver\part\exception\PartAbortedException;
use BusyPHP\uploader\driver\part\exception\PartPreparedException;
use BusyPHP\uploader\driver\part\exception\PartPuttedException;
use BusyPHP\uploader\driver\part\PartAbortData;
use BusyPHP\uploader\driver\part\PartMergeData;
use BusyPHP\uploader\driver\part\PartPrepareData;
use BusyPHP\uploader\driver\part\PartPutData;
use BusyPHP\uploader\result\UploadResult;
use InvalidArgumentException;
use Throwable;

/**
 * 分块上传
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/21 下午上午12:53 PartUpload.php $
 */
class Part extends Driver
{
    /**
     * @inheritDoc
     * @return UploadResult
     * @throws Throwable
     */
    protected function handle() : UploadResult
    {
        $part = $this->disk->part();
        
        // 预备上传
        if ($this->data instanceof PartPrepareData) {
            $basename  = $this->data->getBasename('', $this->data->getOriginalName());
            $mimetype  = $this->data->getMimetype('', '', $basename, false);
            $extension = pathinfo($basename, PATHINFO_EXTENSION);
            
            // 校验扩展名
            $this->checkExtension($extension);
            
            // 校验尺寸
            if (0 < $filesize = $this->data->getFilesize()) {
                $this->checkFilesize($filesize);
            }
            
            // 校验mimetype
            if ($mimetype) {
                $this->checkMimetype($mimetype);
            }
            
            $path = $this->buildPath($basename, $extension);
            
            throw new PartPreparedException($part->prepare($path, $this->data));
        }
        
        //
        // 上传分块
        elseif ($this->data instanceof PartPutData) {
            throw new PartPuttedException($part->put($this->data));
        }
        
        //
        // 终止上传
        elseif ($this->data instanceof PartAbortData) {
            $part->abort($this->data);
            
            throw new PartAbortedException();
        }
        
        //
        // 合并分块
        elseif ($this->data instanceof PartMergeData) {
            $result = $part->merge($this->data);
            if (!$result->getPath()) {
                throw new InvalidArgumentException('缺少参数:path');
            }
            
            // 校验
            try {
                if (!$result->getBasename()) {
                    throw new InvalidArgumentException('缺少参数:basename');
                }
                if (!$result->getMd5()) {
                    throw new InvalidArgumentException('缺少参数:md5');
                }
                
                $this->checkFilesize($result->getFilesize());
                $this->checkMimetype($result->getMimetype());
            } catch (Throwable $e) {
                $this->disk->delete($result->getPath());
                
                throw $e;
            }
            
            return $result;
        }
        
        throw new \LogicException();
    }
    
    
    /**
     * @inheritDoc
     */
    public static function configName() : string
    {
        return 'part';
    }
}