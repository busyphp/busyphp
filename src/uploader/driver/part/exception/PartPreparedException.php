<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\driver\part\exception;

use RuntimeException;

/**
 * 分块上传已准备完成
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/5/26 09:07 PartPreparedException.php $
 */
class PartPreparedException extends RuntimeException
{
    private string $uploadId;
    
    
    /**
     * 构造函数
     * @param string $uploadId 上传ID
     */
    public function __construct(string $uploadId)
    {
        $this->uploadId = $uploadId;
        
        parent::__construct('分块上传已准备完成', 0);
    }
    
    
    /**
     * 获取上传ID
     * @return string
     */
    public function getUploadId() : string
    {
        return $this->uploadId;
    }
}