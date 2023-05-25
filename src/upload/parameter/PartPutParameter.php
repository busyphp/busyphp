<?php
declare(strict_types = 1);

namespace BusyPHP\upload\parameter;

use think\File;

/**
 * 执行分块上传参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/21 2:54 PM PartPutParameter.php $
 */
class PartPutParameter
{
    /** @var File|string */
    protected string|File $file;
    
    /** @var string */
    protected string $uploadId;
    
    /** @var int */
    protected int $partNumber;
    
    
    /**
     * 构造函数
     * @param File|string $file 文件对象或文件内容
     */
    public function __construct(File|string $file, string $uploadId, int $partNumber)
    {
        $this->file       = $file;
        $this->uploadId   = $uploadId;
        $this->partNumber = $partNumber;
    }
    
    
    /**
     * 获取文件对象或文件内容
     * @return array|File
     */
    public function getFile() : File|string
    {
        return $this->file;
    }
    
    
    /**
     * 获取文件上传ID
     * @return string
     */
    public function getUploadId() : string
    {
        return $this->uploadId;
    }
    
    
    /**
     * 设置文件上传ID
     * @param string $uploadId
     * @return $this
     */
    public function setUploadId(string $uploadId) : static
    {
        $this->uploadId = $uploadId;
        
        return $this;
    }
    
    
    /**
     * 获取分块编号
     * @return int
     */
    public function getPartNumber() : int
    {
        return max($this->partNumber, 1);
    }
    
    
    /**
     * 设置分块编号，从1开始
     * @param int $partNumber
     * @return $this
     */
    public function setPartNumber(int $partNumber) : static
    {
        $this->partNumber = $partNumber;
        
        return $this;
    }
}