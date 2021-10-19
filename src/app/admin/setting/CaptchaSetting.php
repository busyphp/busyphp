<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\setting;

use BusyPHP\model\Setting;

/**
 * 图形验证码配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/19 下午下午3:44 CaptchaSetting.php $
 */
class CaptchaSetting extends Setting
{
    /**
     * 获取数据解析器
     * @param mixed $data
     * @return mixed
     */
    protected function parseGet($data)
    {
        return $data;
    }
    
    
    /**
     * 设置数据解析器
     * @param mixed $data
     * @return mixed
     */
    protected function parseSet($data)
    {
        return $data;
    }
}