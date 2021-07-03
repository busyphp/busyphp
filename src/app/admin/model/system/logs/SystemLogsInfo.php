<?php


namespace BusyPHP\app\admin\model\system\logs;

use BusyPHP\helper\util\Transform;

/**
 * 操作记录模型信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午5:04 SystemLogsInfo.php $
 */
class SystemLogsInfo extends SystemLogsField
{
    /**
     * 格式化的时间
     * @var string
     */
    public $formatCreateTime;
    
    /**
     * 操作类型名称
     * @var string
     */
    public $typeName;
    
    
    public function onParseAfter()
    {
        $this->formatCreateTime = Transform::date($this->createTime);
        $this->typeName         = SystemLogs::getTypes($this->type);
        $this->content          = unserialize($this->content);
        $this->isAdmin          = $this->isAdmin > 0;
    }
}