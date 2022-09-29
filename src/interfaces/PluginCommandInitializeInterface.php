<?php

namespace BusyPHP\interfaces;

use think\console\Output;

/**
 * 插件命令行初始化接口类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午下午7:58 PluginCommandInitializeInterface.php $
 */
interface PluginCommandInitializeInterface
{
    /**
     * 执行初始化
     * @param Output $output
     */
    public function onPluginCommandInitialize(Output $output);
}