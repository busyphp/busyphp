<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\interfaces;

use BusyPHP\uploader\driver\part\PartAbortData;
use BusyPHP\uploader\driver\part\PartMergeData;
use BusyPHP\uploader\driver\part\PartPrepareData;
use BusyPHP\uploader\driver\part\PartPutData;
use BusyPHP\uploader\result\UploadResult;

/**
 * 分块上传接口类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/24 3:45 PM PartInterface.php $
 */
interface PartInterface
{
    /**
     * 预备上传
     * @param string          $path 上传文件路径
     * @param PartPrepareData $data
     * @return string
     */
    public function prepare(string $path, PartPrepareData $data) : string;
    
    
    /**
     * 上传分块
     * @param PartPutData $data
     * @return array
     */
    public function put(PartPutData $data) : array;
    
    
    /**
     * 合并分块
     * mimetype，md5，filesize，width，height 请通过文件内容来取
     * @param PartMergeData $data
     * @return UploadResult
     */
    public function merge(PartMergeData $data) : UploadResult;
    
    
    /**
     * 终止分块上传
     * @param PartAbortData $data
     */
    public function abort(PartAbortData $data);
}