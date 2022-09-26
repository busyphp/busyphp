<?php
declare(strict_types = 1);

namespace BusyPHP\upload\parameter;

use BusyPHP\upload\interfaces\PartInterface;

/**
 * 终止上传分块参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/22 9:14 AM PartAbortParameter.php $
 */
class PartAbortParameter
{
    /** @var string */
    private $uploadId;
    
    
    /**
     * 构造函数
     * @param string $uploadId UploadId 由 {@see PartInterface::init()} 生成
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