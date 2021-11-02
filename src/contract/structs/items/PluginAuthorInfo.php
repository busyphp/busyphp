<?php

namespace BusyPHP\contract\structs\items;

use BusyPHP\model\Field;

/**
 * 插件作者信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/2 下午上午8:31 PluginAuthorInfo.php $
 */
class PluginAuthorInfo extends Field
{
    /**
     * 姓名
     * @var string
     */
    public $name;
    
    /**
     * 邮箱
     * @var string
     */
    public $email;
}