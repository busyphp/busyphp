<?php

namespace BusyPHP\app\admin\setting;

use BusyPHP\model\Setting;
use BusyPHP\helper\util\Filter;

/**
 * 系统基本配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午6:02 下午 PublicSetting.php $
 */
class PublicSetting extends Setting
{
    protected function parseSet($data)
    {
        return Filter::trim($data);
    }
    
    
    protected function parseGet($data)
    {
        return $data;
    }
}