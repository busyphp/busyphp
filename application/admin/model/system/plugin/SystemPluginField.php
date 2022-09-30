<?php

namespace BusyPHP\app\admin\model\system\plugin;

use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * 插件管理模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/1 下午上午11:52 SystemPluginField.php $
 * @method static Entity id($op = null, $value = null) 包名HASH
 * @method static Entity package($op = null, $value = null) 包名
 * @method static Entity createTime($op = null, $value = null) 创建时间
 * @method static Entity updateTime($op = null, $value = null) 更新时间
 * @method static Entity install($op = null, $value = null) 是否已安装
 * @method static Entity panel($op = null, $value = null) 是否在主页展示
 * @method static Entity setting($op = null, $value = null) 设置参数
 * @method static Entity sort($op = null, $value = null) 排序
 */
class SystemPluginField extends Field
{
    /**
     * 包名HASH
     * @var string
     */
    public $id;
    
    /**
     * 包名
     * @var string
     */
    public $package;
    
    /**
     * 创建时间
     * @var int
     */
    public $createTime;
    
    /**
     * 更新时间
     * @var int
     */
    public $updateTime;
    
    /**
     * 是否已安装
     * @var int
     */
    public $install;
    
    /**
     * 是否在主页展示
     * @var int
     */
    public $panel;
    
    /**
     * 设置参数
     * @var string
     */
    public $setting;
    
    /**
     * 排序
     * @var int
     */
    public $sort;
}