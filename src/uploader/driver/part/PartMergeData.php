<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\driver\part;

use BusyPHP\uploader\interfaces\PartInterface;
use BusyPHP\uploader\interfaces\DataInterface;

/**
 * 合成分块参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/21 2:54 PM PartMergeData.php $
 */
class PartMergeData implements DataInterface
{
    /**
     * @var string
     */
    protected string $uploadId;
    
    /**
     * @var array
     */
    protected array $parts;
    
    
    /**
     * 构造函数
     * @param string $uploadId uploadId 由 {@see PartInterface::prepare()} 返回
     * @param array  $parts 分块数据合集，由 {@see PartInterface::put()} 返回
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
}