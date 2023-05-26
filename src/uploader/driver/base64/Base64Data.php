<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\driver\base64;

use BusyPHP\uploader\driver\content\ContentData;

/**
 * Base64上传参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/19 2:26 PM Base64Parameter.php $
 * @method string getData() 获取Base64数据
 */
class Base64Data extends ContentData
{
    /**
     * 构造函数
     * @param string $base64Data base64字符
     */
    public function __construct(string $base64Data)
    {
        parent::__construct($base64Data);
    }
}