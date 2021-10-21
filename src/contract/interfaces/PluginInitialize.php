<?php

namespace BusyPHP\contract\interfaces;

use think\console\Output;

/**
 * 插件初始化接口类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午下午7:58 PluginInitialize.php $
 */
interface PluginInitialize
{
    /**
     * 执行安装
     * @param Output $output
     */
    public function handle(Output $output);
}