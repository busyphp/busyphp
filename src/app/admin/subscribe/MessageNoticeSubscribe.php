<?php

namespace BusyPHP\app\admin\subscribe;

use BusyPHP\app\admin\model\admin\message\AdminMessage;
use BusyPHP\app\admin\model\message\MessageNoticeItem;
use BusyPHP\app\admin\model\message\MessageListParams;
use BusyPHP\app\admin\model\message\MessageUpdateParams;
use BusyPHP\app\admin\model\message\MessageParams;
use BusyPHP\contract\abstracts\Subscribe;
use BusyPHP\exception\SQLException;
use BusyPHP\exception\VerifyException;

/**
 * 通知消息订阅器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午12:18 下午 MessageNoticeSubscribe.php $
 */
class MessageNoticeSubscribe extends Subscribe
{
    protected static $prefix = self::class;
    
    
    /**
     * 获取未读消息总数
     * @param MessageParams $params
     * @return int
     */
    public function onTotal(MessageParams $params) : int
    {
        return intval(AdminMessage::init()->where('user_id', $params->getUserId())->where('is_read', 0)->count());
    }
    
    
    /**
     * 获取消息列表
     * @param MessageListParams $params
     * @return MessageNoticeItem[]
     */
    public function onList(MessageListParams $params) : array
    {
        $size  = 20;
        $page  = $params->getPage();
        $page  = $page <= 1 ? 1 : $page;
        $model = AdminMessage::init();
        $model->where('user_id', $params->getUserId());
        $model->order('id', 'desc');
        $model->limit($size * ($page - 1), $size);
        $data = $model->selectList();
        $list = [];
        
        foreach ($data as $r) {
            $item = new MessageNoticeItem();
            $item->setId($r['id']);
            $item->setRead($r['is_read']);
            $item->setCreateTime($r['create_time']);
            $item->setReadTime($r['read_time']);
            $item->setTitle($r['content']);
            $item->setDesc($r['description']);
            $item->setIcon($r['icon_is_class'], $r['icon'], $r['icon_color']);
            $item->setOperateUrl($r['url']);
            $list[] = $item;
        }
        
        return $list;
    }
    
    
    /**
     * 读取消息
     * @param MessageUpdateParams $params
     * @throws SQLException
     */
    public function onRead(MessageUpdateParams $params)
    {
        AdminMessage::init()->where('user_id', $params->getUserId())->setRead($params->getId());
    }
    
    
    /**
     * 全部已读
     * @param MessageParams $params
     * @throws SQLException
     */
    public function onAllRead(MessageParams $params)
    {
        AdminMessage::init()->setAllReadByUserId($params->getUserId());
    }
    
    
    /**
     * 删除消息
     * @param MessageUpdateParams $params
     * @throws SQLException
     * @throws VerifyException
     */
    public function onDelete(MessageUpdateParams $params)
    {
        AdminMessage::init()->where('user_id', $params->getUserId())->del(intval($params->getId()));
    }
    
    
    /**
     * 清空消息
     * @param MessageParams $params
     * @throws SQLException
     */
    public function onClear(MessageParams $params)
    {
        AdminMessage::init()->clearByUserId($params->getUserId());
    }
    
    
    /**
     * 触发获取未读消息总数事件订阅
     * @param MessageParams $params
     * @return int
     */
    public static function triggerTotal(MessageParams $params) : int
    {
        return self::trigger('Total', $params);
    }
    
    
    /**
     * 触发获取消息列表事件订阅
     * @param MessageListParams $params
     * @return MessageNoticeItem[]
     */
    public static function triggerList(MessageListParams $params) : array
    {
        return self::trigger('List', $params);
    }
    
    
    /**
     * 触发读取消息事件订阅
     * @param MessageUpdateParams $params
     */
    public static function triggerRead(MessageUpdateParams $params)
    {
        self::trigger('Read', $params);
    }
    
    
    /**
     * 触发删除单条通知事件订阅
     * @param MessageUpdateParams $params
     */
    public static function triggerDelete(MessageUpdateParams $params)
    {
        self::trigger('Delete', $params);
    }
    
    
    /**
     * 触发全部已读事件订阅
     * @param MessageParams $params
     */
    public static function triggerAllRead(MessageParams $params)
    {
        self::trigger('AllRead', $params);
    }
    
    
    /**
     * 触发清空通知事件订阅
     * @param MessageParams $params
     */
    public static function triggerClear(MessageParams $params)
    {
        self::trigger('Clear', $params);
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