<?php


namespace BusyPHP\app\admin\model\system\config;

/**
 * 系统配置信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午3:10 SystemConfigInfo.php $
 */
class SystemConfigInfo extends SystemConfigField
{
    public function onParseAfter()
    {
        $this->isSystem = $this->isSystem > 0;
        $this->isAppend = $this->isAppend > 0;
        $this->content  = unserialize($this->content);
    }
}