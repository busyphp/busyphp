<?php
declare(strict_types = 1);

namespace BusyPHP\upload\driver;

use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\Upload;
use BusyPHP\upload\parameter\PartAbortParameter;
use BusyPHP\upload\parameter\PartInitParameter;
use BusyPHP\upload\parameter\PartCompleteParameter;
use BusyPHP\upload\parameter\PartCreateParameter;
use BusyPHP\upload\parameter\PartPutParameter;
use BusyPHP\upload\result\UploadResult;
use InvalidArgumentException;
use Throwable;

/**
 * 分块上传
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/21 下午上午12:53 PartUpload.php $
 * @property PartCompleteParameter $parameter
 */
class PartUpload extends Upload
{
    /**
     * 初始化分块上传，并返回 uploadId 用于后续上传
     * @param PartCreateParameter $parameter
     * @return string
     */
    public function create(PartCreateParameter $parameter) : string
    {
        $basename  = $parameter->getBasename('', $parameter->getOriginalName());
        $mimetype  = $parameter->getMimetype('', '', $basename, false);
        $extension = pathinfo($basename, PATHINFO_EXTENSION);
        
        // 校验扩展名
        $this->checkExtension($extension);
        
        // 校验尺寸
        if (0 < $filesize = $parameter->getFilesize()) {
            $this->checkFilesize($filesize);
        }
        
        // 校验mimetype
        if ($mimetype) {
            $this->checkMimetype($mimetype);
        }
        
        // 初始化分块上传
        $initParameter = new PartInitParameter($this->buildPath($basename, $extension));
        $initParameter->setMd5($parameter->getMd5());
        $initParameter->setBasename($basename);
        $initParameter->setFilesize($filesize);
        $initParameter->setMimetype($mimetype);
        $initParameter->setTmpDisk($parameter->getTmpDisk());
        $initParameter->setTmpDir($parameter->getTmpDir());
        
        return $this->disk->part()->init($initParameter);
    }
    
    
    /**
     * 上传分块
     * @param PartPutParameter $parameter
     * @return array
     */
    public function put(PartPutParameter $parameter) : array
    {
        return $this->disk->part()->put($parameter);
    }
    
    
    /**
     * 合并分块，别名{@see PartUpload::complete()}
     * @return UploadResult
     * @throws Throwable
     */
    protected function handle() : UploadResult
    {
        if (!$this->parameter instanceof PartCompleteParameter) {
            throw new ClassNotExtendsException($this->parameter, PartCompleteParameter::class);
        }
        
        return $this->complete($this->parameter);
    }
    
    
    /**
     * 合并分块
     * @param PartCompleteParameter $parameter
     * @return UploadResult
     * @throws Throwable
     */
    public function complete(PartCompleteParameter $parameter) : UploadResult
    {
        $response = $this->disk->part()->complete($parameter);
        $path     = $response['path'] ?? '';
        if (!$path) {
            throw new InvalidArgumentException('缺少参数:path');
        }
        
        // 校验
        try {
            $basename = $response['basename'] ?? '';
            $md5      = $response['md5'] ?? '';
            $filesize = intval($response['filesize'] ?? 0);
            $mimetype = $response['mimetype'] ?? '';
            if (!$basename) {
                throw new InvalidArgumentException('缺少参数:basename');
            }
            if (!$md5) {
                throw new InvalidArgumentException('缺少参数:md5');
            }
            
            $this->checkFilesize($filesize);
            $this->checkMimetype($mimetype);
        } catch (Throwable $e) {
            $this->disk->delete($path);
            throw $e;
        }
        
        $result = new UploadResult();
        $result->setBasename($basename);
        $result->setMimetype($mimetype);
        $result->setFilesize($filesize);
        $result->setMd5($md5);
        $result->setPath($path);
        $result->setWidth(intval($response['width'] ?? 0));
        $result->setHeight(intval($response['height'] ?? 0));
        
        return $result;
    }
    
    
    /**
     * 终止分块上传
     * @param PartAbortParameter $parameter
     * @return void
     */
    public function abort(PartAbortParameter $parameter) : void
    {
        $this->disk->part()->abort($parameter);
    }
}