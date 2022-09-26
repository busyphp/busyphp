<?php
declare(strict_types = 1);

namespace BusyPHP\upload\parameter;

use BusyPHP\upload\Driver;
use BusyPHP\upload\driver\Base64Upload;

/**
 * Base64上传参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/19 2:26 PM Base64Parameter.php $
 * @method string getData() 获取Base64数据
 */
class Base64Parameter extends ContentParameter
{
    /**
     * 构造函数
     * @param string $base64Data base64字符
     */
    public function __construct(string $base64Data)
    {
        parent::__construct($base64Data);
    }
    
    
    /**
     * 获取上传驱动类
     * @return class-string<Driver>
     */
    public function getDriver() : string
    {
        return Base64Upload::class;
    }
}