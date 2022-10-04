<?php

namespace BusyPHP\app\admin\model\system\plugin;

/**
 * 插件管理模型信息
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/1 下午上午11:52 SystemPluginInfo.php $
 * @property bool $install
 * @property bool $panel
 */
class SystemPluginInfo extends SystemPluginField
{
    protected function onParseAfter()
    {
        $this->setting = json_decode($this->setting, true) ?: [];
        $this->install = $this->install > 0;
        $this->panel   = $this->panel > 0;
    }
}