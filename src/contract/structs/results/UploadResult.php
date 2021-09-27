<?php

namespace BusyPHP\contract\structs\results;

use think\File;

/**
 * 上传返回结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/21 下午下午3:37 UploadResult.php $
 */
class UploadResult
{
    /**
     * 文件对象
     * @var File
     */
    public $file = null;
    
    /**
     * 文件访问地址
     * @var string
     */
    public $url = '';
    
    /**
     * 文件名
     * @var string
     */
    public $name = '';
    
    /**
     * 文件ID
     * @var int
     */
    public $id = 0;
    
    /**
     * 图片宽度
     * @var int
     */
    public $width = 0;
    
    /**
     * 图片高度
     * @var int
     */
    public $height = 0;
}