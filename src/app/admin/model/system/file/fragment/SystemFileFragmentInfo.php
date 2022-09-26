<?php

namespace BusyPHP\app\admin\model\system\file\fragment;

use BusyPHP\helper\TransHelper;

/**
 * SystemFileFragmentInfo
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/8 9:16 PM SystemFileFragmentInfo.php $
 * @property bool|int $merging
 */
class SystemFileFragmentInfo extends SystemFileFragmentField
{
    public $formatCreateTime;
    
    
    public function onParseAfter() : void
    {
        $this->id               = (int) $this->id;
        $this->formatCreateTime = TransHelper::date($this->createTime);
        $this->merging          = $this->merging > 0;
    }
}