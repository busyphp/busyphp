<?php
declare(strict_types = 1);

namespace BusyPHP\traits;

/**
 * 容器单例特征支持
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/22 21:17 ContainerInstance.php $
 * @mixin ContainerDefine
 */
trait ContainerInstance
{
    /**
     * 获取单例
     * @return static
     */
    final public static function instance() : static
    {
        return self::makeContainer();
    }
}