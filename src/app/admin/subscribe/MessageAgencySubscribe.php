<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\subscribe;

use BusyPHP\app\admin\model\admin\message\provide\MessageAgencyItem;
use BusyPHP\app\admin\model\admin\message\provide\MessageParams;
use BusyPHP\app\admin\model\admin\message\provide\MessageUpdateParams;
use BusyPHP\contract\abstracts\Subscribe;

/**
 * 待办消息订阅器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午12:18 下午 MessageAgencySubscribe.php $
 */
class MessageAgencySubscribe extends Subscribe
{
    protected static $prefix = self::class;
    
    
    /**
     * 获取待办任务总数
     * @param MessageParams $params
     * @return int
     */
    public function onTotal(MessageParams $params) : int
    {
        return 0;
    }
    
    
    /**
     * 获取待办列表
     * @param MessageParams $params
     * @return MessageAgencyItem[]
     */
    public function onList(MessageParams $params) : array
    {
        return [];
    }
    
    
    /**
     * 待办已读回调，一般情况下用不到该方法
     * @param MessageUpdateParams $params
     */
    public function onRead(MessageUpdateParams $params)
    {
    }
    
    
    /**
     * 触发获取待办任务总数
     * @param MessageParams $params
     * @return int
     */
    public static function triggerTotal(MessageParams $params) : int
    {
        return self::trigger('Total', $params);
    }
    
    
    /**
     * 触发获取待办任务列表
     * @param MessageParams $params
     * @return MessageAgencyItem[]
     */
    public static function triggerList(MessageParams $params) : array
    {
        return self::trigger('List', $params);
    }
    
    
    /**
     * 触发待办已读事件订阅
     * @param MessageUpdateParams $params
     */
    public static function triggerRead(MessageUpdateParams $params)
    {
        self::trigger('Read', $params);
    }
    
    
    /**
     * 是否订阅
     * @return bool
     */
    public static function hasSubscribe()
    {
        return self::has('List');
    }
}