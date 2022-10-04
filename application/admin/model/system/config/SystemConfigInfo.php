<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\config;

/**
 * 系统配置信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午3:10 SystemConfigInfo.php $
 */
class SystemConfigInfo extends SystemConfigField
{
    protected function onParseAfter()
    {
        $this->system  = $this->system > 0;
        $this->append  = $this->append > 0;
        $this->content = unserialize($this->content);
    }
}