<?php

namespace BusyPHP\contract\structs\items;

use BusyPHP\model\Field;

/**
 * 插件设置配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/2 下午上午8:50 PluginSettingConfig.php $
 */
class PluginSettingConfig extends Field
{
    /**
     * Modal对话框尺寸，支持 sm, lg
     * @var string
     */
    public $size = '';
    
    /**
     * 操作成功回调，支持 busy-modal js插件语法糖
     * @var string
     */
    public $success = '';
    
    /**
     * 是否底部按钮栏
     * @var bool
     */
    public $footer = true;
    
    /**
     * Modal类型
     * @var string
     */
    public $type = 'form';
}