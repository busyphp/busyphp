<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\driver\part;

use BusyPHP\uploader\interfaces\PartInterface;
use BusyPHP\uploader\interfaces\DataInterface;

/**
 * 终止上传分块参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/22 9:14 AM PartAbortData.php $
 */
class PartAbortData implements DataInterface
{
    /**
     * @var string
     */
    private string $uploadId;
    
    
    /**
     * 构造函数
     * @param string $uploadId UploadId 由 {@see PartInterface::prepare()} 生成
     */
    public function __construct(string $uploadId)
    {
        $this->uploadId = $uploadId;
    }
    
    
    /**
     * 获取UploadId
     * @return string
     */
    public function getUploadId() : string
    {
        return $this->uploadId;
    }
}