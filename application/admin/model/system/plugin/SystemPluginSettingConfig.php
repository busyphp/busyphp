<?php

namespace BusyPHP\app\admin\model\system\plugin;

use BusyPHP\model\Field;

/**
 * 插件设置配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/2 下午上午8:50 SystemPluginSettingConfig.php $
 */
class SystemPluginSettingConfig extends Field
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
    
    /**
     * 是否显示上边框
     * @var bool
     */
    public $borderTop = true;
    
    /**
     * 是否显示下边框
     * @var bool
     */
    public $borderBottom = true;
    
    /**
     * 对话框高度
     * @var string
     */
    public $height = '';
    
    /**
     * 填充
     * @var string
     */
    public $padding = '';
}