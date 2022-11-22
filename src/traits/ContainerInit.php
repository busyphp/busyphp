<?php

namespace BusyPHP\traits;

/**
 * 容器实例特征支持
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/22 21:18 ContainerInit.php $
 * @mixin ContainerDefine
 */
trait ContainerInit
{
    /**
     * 获取实例
     * @return static
     */
    final public static function init()
    {
        return self::makeContainer([], true);
    }
}