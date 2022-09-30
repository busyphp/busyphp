<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\controller\common\message\subscribe;

use BusyPHP\abstracts\BaseEventSubscribe;
use BusyPHP\app\admin\controller\common\message\info\MessageAgencyInfo;
use BusyPHP\app\admin\controller\common\message\parameter\MessageParameter;
use BusyPHP\app\admin\controller\common\message\parameter\MessageUpdateParameter;

/**
 * 待办消息订阅器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午12:18 下午 MessageAgencySubscribe.php $
 * @method void read(MessageUpdateParameter $params) 设为已读
 * @method int total(MessageParameter $params) 获取待办任务总数
 * @method MessageAgencyInfo[] list(MessageParameter $params) 获取待办任务列表
 */
class MessageAgencySubscribe extends BaseEventSubscribe
{
    final protected function registerClassName() : string
    {
        return self::class;
    }
    
    
    /**
     * 获取待办任务总数
     * @param MessageParameter $params
     * @return int
     */
    public function onTotal(MessageParameter $params) : int
    {
        return 0;
    }
    
    
    /**
     * 获取待办列表
     * @param MessageParameter $params
     * @return MessageAgencyInfo[]
     */
    public function onList(MessageParameter $params) : array
    {
        return [];
    }
    
    
    /**
     * 待办已读回调，一般情况下用不到该方法
     * @param MessageUpdateParameter $params
     */
    public function onRead(MessageUpdateParameter $params)
    {
    }
    
    
    /**
     * 是否订阅
     * @return bool
     */
    public function hasSubscribe() : bool
    {
        return $this->has('List');
    }
}