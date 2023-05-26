<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\driver\part;

use BusyPHP\uploader\interfaces\DataInterface;
use LogicException;
use think\File;

/**
 * 单个分块上传参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/21 2:54 PM PartPutData.php $
 */
class PartPutData implements DataInterface
{
    /**
     * @var File|string
     */
    protected string|File $file;
    
    /**
     * @var string
     */
    protected string $uploadId;
    
    /**
     * @var int
     */
    protected int $partNumber;
    
    
    /**
     * 构造函数
     * @param File|string $file 文件对象或文件内容
     * @param string      $uploadId uploadId 由 {@see PartInterface::prepare()} 返回
     * @param int         $partNumber 当前上传的第几个分块，必须从1开始
     */
    public function __construct(File|string $file, string $uploadId, int $partNumber)
    {
        if ($partNumber < 1) {
            throw new LogicException('$partNumber must start from 1');
        }
        
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
        return $this->partNumber;
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