<?php
declare(strict_types = 1);

namespace BusyPHP\upload\interfaces;

use BusyPHP\upload\parameter\PartAbortParameter;
use BusyPHP\upload\parameter\PartCompleteParameter;
use BusyPHP\upload\parameter\PartInitParameter;
use BusyPHP\upload\parameter\PartPutParameter;

/**
 * 分块上传接口类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/24 3:45 PM PartInterface.php $
 */
interface PartInterface
{
    /**
     * 初始化分块上传
     * @param PartInitParameter $parameter
     * @return string
     */
    public function init(PartInitParameter $parameter) : string;
    
    
    /**
     * 上传分块
     * @param PartPutParameter $parameter
     * @return array
     */
    public function put(PartPutParameter $parameter) : array;
    
    
    /**
     * 完成整个分块上传
     * mimetype，md5，filesize，width，height 请通过文件内容来取
     * @param PartCompleteParameter $parameter
     * @return array{path: string, md5: string, basename: string, mimetype: string, filesize: int, width: int, height: int}
     */
    public function complete(PartCompleteParameter $parameter) : array;
    
    
    /**
     * 终止分块上传
     * @param PartAbortParameter $parameter
     */
    public function abort(PartAbortParameter $parameter);
}