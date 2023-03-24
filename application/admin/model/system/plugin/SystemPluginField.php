<?php

namespace BusyPHP\app\admin\model\system\plugin;

use BusyPHP\model\annotation\field\Json;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * 插件管理模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/1 下午上午11:52 SystemPluginField.php $
 * @method static Entity id(mixed $op = null, mixed $condition = null) 包名HASH
 * @method static Entity package(mixed $op = null, mixed $condition = null) 包名
 * @method static Entity createTime(mixed $op = null, mixed $condition = null) 创建时间
 * @method static Entity updateTime(mixed $op = null, mixed $condition = null) 更新时间
 * @method static Entity install(mixed $op = null, mixed $condition = null) 是否已安装
 * @method static Entity panel(mixed $op = null, mixed $condition = null) 是否在主页展示
 * @method static Entity setting(mixed $op = null, mixed $condition = null) 设置参数
 * @method static Entity sort(mixed $op = null, mixed $condition = null) 排序
 * @method $this setId(mixed $id) 设置包名HASH
 * @method $this setPackage(mixed $package) 设置包名
 * @method $this setCreateTime(mixed $createTime) 设置创建时间
 * @method $this setUpdateTime(mixed $updateTime) 设置更新时间
 * @method $this setInstall(mixed $install) 设置是否已安装
 * @method $this setPanel(mixed $panel) 设置是否在主页展示
 * @method $this setSetting(mixed $setting) 设置设置参数
 * @method $this setSort(mixed $sort) 设置排序
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
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
     * @var bool
     */
    public $install;
    
    /**
     * 是否在主页展示
     * @var bool
     */
    public $panel;
    
    /**
     * 设置参数
     * @var array
     */
    #[Json]
    public $setting;
    
    /**
     * 排序
     * @var int
     */
    public $sort;
}