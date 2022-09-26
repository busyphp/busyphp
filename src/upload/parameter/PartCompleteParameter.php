<?php
declare(strict_types = 1);

namespace BusyPHP\upload\parameter;

use BusyPHP\upload\Driver;
use BusyPHP\upload\driver\PartUpload;
use BusyPHP\upload\interfaces\BindDriverParameterInterface;

/**
 * 合成分块参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/21 2:54 PM PartCompleteParameter.php $
 */
class PartCompleteParameter implements BindDriverParameterInterface
{
    /** @var string */
    protected $uploadId;
    
    /** @var array */
    protected $parts;
    
    
    /**
     * 构造函数
     * @param string $uploadId uploadId
     * @param array  $parts 分块数据
     */
    public function __construct(string $uploadId, array $parts = [])
    {
        $this->uploadId = $uploadId;
        $this->parts    = $parts;
    }
    
    
    /**
     * 获取uploadId
     * @return string
     */
    public function getUploadId() : string
    {
        return $this->uploadId;
    }
    
    
    /**
     * 获取分块数据
     * @return array
     */
    public function getParts() : array
    {
        return $this->parts;
    }
    
    
    /**
     * 获取上传驱动类
     * @return class-string<Driver>
     */
    public function getDriver() : string
    {
        return PartUpload::class;
    }
}