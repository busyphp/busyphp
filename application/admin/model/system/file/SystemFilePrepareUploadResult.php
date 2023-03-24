<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\file;

/**
 * FrontPrepareResult
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/24 6:11 PM SystemFilePrepareUploadResult.php $
 */
class SystemFilePrepareUploadResult
{
    /**
     * @var SystemFileField
     */
    private $info;
    
    /**
     * @var string
     */
    private $uploadId;
    
    /**
     * @var string
     */
    private $serverUrl;
    
    
    /**
     * @param SystemFileField $info
     * @param string         $uploadId
     */
    public function __construct(SystemFileField $info, string $uploadId, string $serverUrl)
    {
        $this->info      = $info;
        $this->uploadId  = $uploadId;
        $this->serverUrl = $serverUrl;
    }
    
    
    /**
     * @return string
     */
    public function getServerUrl() : string
    {
        return $this->serverUrl;
    }
    
    
    /**
     * @return SystemFileField
     */
    public function getInfo() : SystemFileField
    {
        return $this->info;
    }
    
    
    /**
     * @return string
     */
    public function getUploadId() : string
    {
        return $this->uploadId;
    }
}