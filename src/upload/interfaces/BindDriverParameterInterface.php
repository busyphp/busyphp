<?php

namespace BusyPHP\upload\interfaces;

use BusyPHP\upload\Driver;

/**
 * 上传参数模版接口类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/22 9:28 PM BindDriverParameterInterface.php $
 */
interface BindDriverParameterInterface
{
    /**
     * 获取上传驱动类
     * @return class-string<Driver>
     */
    public function getDriver() : string;
}