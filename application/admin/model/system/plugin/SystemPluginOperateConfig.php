<?php

namespace BusyPHP\app\admin\model\system\plugin;

use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\Field;

/**
 * 插件安装/卸载操作配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/2 下午上午8:45 SystemPluginOperateConfig.php $
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
class SystemPluginOperateConfig extends Field
{
    /**
     * 点击操作方式，支持 modal, request
     * @var string
     */
    public $type = '';
    
    /**
     * 操作成功回调，支持 busy-modal, busy-request js插件语法糖
     * @var string
     */
    public $success = '';
    
    /**
     * Modal对话框尺寸，支持：lg, sm
     * @var string
     */
    public $modalSize = '';
    
    /**
     * 请求确定提示，支持变量 :
     * __package__ : composer包名,
     * __name__ : 插件名称,
     * __version__ : 插件版本号
     * @var string
     */
    public $requestConfirm = '';
}