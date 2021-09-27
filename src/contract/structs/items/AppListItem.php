<?php

namespace BusyPHP\contract\structs\items;

use BusyPHP\helper\AppHelper;
use BusyPHP\model\Map;

/**
 * 应用集合结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/25 下午下午5:50 AppListItem.php $
 * @see AppHelper::getList()
 * @method static mixed dir();
 * @method static mixed name();
 * @method static mixed path();
 * @method static mixed config();
 */
class AppListItem extends Map
{
    /**
     * 应用目录名称
     * @var string
     */
    public $dir;
    
    /**
     * 应用名称
     * @var string
     */
    public $name;
    
    /**
     * 应用路径
     * @var string
     */
    public $path;
    
    /**
     * 应用配置
     * @var array
     */
    public $config;
}