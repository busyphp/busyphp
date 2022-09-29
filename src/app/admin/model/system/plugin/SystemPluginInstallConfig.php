<?php

namespace BusyPHP\app\admin\model\system\plugin;

use BusyPHP\model\Field;

/**
 * 插件安装/卸载配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/2 下午上午8:37 SystemPluginInstallConfig.php $
 */
class SystemPluginInstallConfig extends Field
{
    /**
     * 安装操作配置
     * @var SystemPluginOperateConfig
     */
    public $installOperate;
    
    /**
     * 卸载操作配置
     * @var SystemPluginOperateConfig
     */
    public $uninstallOperate;
}