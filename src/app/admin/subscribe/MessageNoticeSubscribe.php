<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\subscribe;

use BusyPHP\app\admin\model\admin\message\AdminMessage;
use BusyPHP\app\admin\model\admin\message\AdminMessageField;
use BusyPHP\app\admin\model\admin\message\provide\MessageNoticeItem;
use BusyPHP\app\admin\model\admin\message\provide\MessageListParams;
use BusyPHP\app\admin\model\admin\message\provide\MessageUpdateParams;
use BusyPHP\app\admin\model\admin\message\provide\MessageParams;
use BusyPHP\contract\abstracts\Subscribe;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 通知消息订阅器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
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
        return intval(AdminMessage::init()
            ->whereEntity(AdminMessageField::userId($params->getUser()->id), AdminMessageField::read(0))
            ->count());
    }
    
    
    /**
     * 获取消息列表
     * @param MessageListParams $params
     * @return MessageNoticeItem[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function onList(MessageListParams $params) : array
    {
        $size  = 20;
        $page  = $params->getPage();
        $page  = $page <= 1 ? 1 : $page;
        $model = AdminMessage::init();
        $model->whereEntity(AdminMessageField::userId($params->getUser()->id));
        $model->order(AdminMessageField::id(), 'desc');
        $model->page($page, $size);
        $data = $model->selectList();
        $list = [];
        
        foreach ($data as $info) {
            $item = new MessageNoticeItem();
            $item->setId($info->id);
            $item->setRead($info->read);
            $item->setCreateTime($info->createTime);
            $item->setReadTime($info->readTime);
            $item->setTitle($info->content);
            $item->setDesc($info->description);
            $item->setIcon($info->iconIsClass, $info->icon, $info->iconColor);
            $item->setOperateUrl($info->url);
            $list[] = $item;
        }
        
        return $list;
    }
    
    
    /**
     * 读取消息
     * @param MessageUpdateParams $params
     * @throws DbException
     */
    public function onRead(MessageUpdateParams $params)
    {
        AdminMessage::init()->whereEntity(AdminMessageField::userId($params->getUser()->id))->setRead($params->getId());
    }
    
    
    /**
     * 全部已读
     * @param MessageParams $params
     * @throws DbException
     */
    public function onAllRead(MessageParams $params)
    {
        AdminMessage::init()->setAllReadByUserId($params->getUser()->id);
    }
    
    
    /**
     * 删除消息
     * @param MessageUpdateParams $params
     * @throws DbException
     */
    public function onDelete(MessageUpdateParams $params)
    {
        AdminMessage::init()
            ->whereEntity(AdminMessageField::userId($params->getUser()->id))
            ->deleteInfo(intval($params->getId()));
    }
    
    
    /**
     * 清空消息
     * @param MessageParams $params
     * @throws DbException
     */
    public function onClear(MessageParams $params)
    {
        AdminMessage::init()->clearByUserId($params->getUser()->id);
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