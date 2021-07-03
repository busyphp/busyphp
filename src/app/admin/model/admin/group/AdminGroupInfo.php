<?php


namespace BusyPHP\app\admin\model\admin\group;

use BusyPHP\helper\util\Filter;
use BusyPHP\helper\util\Transform;

/**
 * 管理员用户组信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午12:57 AdminGroupInfo.php $
 */
class AdminGroupInfo extends AdminGroupField
{
    /**
     * @var array
     */
    public $ruleArray;
    
    
    public function onParseAfter()
    {
        $this->isSystem  = Transform::dataToBool($this->isSystem);
        $this->ruleArray = Filter::trimArray(explode(',', $this->rule));
    }
}