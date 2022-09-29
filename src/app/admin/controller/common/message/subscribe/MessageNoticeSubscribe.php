<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\controller\common\message\subscribe;

use BusyPHP\abstracts\BaseEventSubscribe;
use BusyPHP\app\admin\controller\common\message\info\MessageNoticeInfo;
use BusyPHP\app\admin\controller\common\message\parameter\MessageListParameter;
use BusyPHP\app\admin\controller\common\message\parameter\MessageParameter;
use BusyPHP\app\admin\controller\common\message\parameter\MessageUpdateParameter;
use BusyPHP\app\admin\model\admin\message\AdminMessage;
use BusyPHP\app\admin\model\admin\message\AdminMessageField;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 通知消息订阅器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午12:18 下午 MessageNoticeSubscribe.php $
 * @method void read(MessageUpdateParameter $params) 设为已读
 * @method void allRead(MessageParameter $params) 全部设为已读
 * @method void delete(MessageUpdateParameter $params) 删除消息
 * @method void clear(MessageParameter $params) 清空消息
 * @method int total(MessageParameter $params) 获取未读消息总数
 * @method MessageNoticeInfo[] list(MessageListParameter $params) 获取消息列表
 */
class MessageNoticeSubscribe extends BaseEventSubscribe
{
    final protected function registerClassName() : string
    {
        return self::class;
    }
    
    
    /**
     * 获取未读消息总数
     * @param MessageParameter $params
     * @return int
     */
    public function onTotal(MessageParameter $params) : int
    {
        return AdminMessage::init()
            ->whereEntity(AdminMessageField::userId($params->getUser()->id), AdminMessageField::read(0))
            ->count();
    }
    
    
    /**
     * 获取消息列表
     * @param MessageListParameter $params
     * @return MessageNoticeInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function onList(MessageListParameter $params) : array
    {
        $size  = 20;
        $page  = $params->getPage();
        $page  = max($page, 1);
        $model = AdminMessage::init();
        $model->whereEntity(AdminMessageField::userId($params->getUser()->id));
        $model->order(AdminMessageField::id(), 'desc');
        $model->page($page, $size);
        $data = $model->selectList();
        $list = [];
        
        foreach ($data as $info) {
            $item = new MessageNoticeInfo();
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
     * @param MessageUpdateParameter $params
     * @throws DbException
     */
    public function onRead(MessageUpdateParameter $params)
    {
        AdminMessage::init()->whereEntity(AdminMessageField::userId($params->getUser()->id))->setRead($params->getId());
    }
    
    
    /**
     * 全部已读
     * @param MessageParameter $params
     * @throws DbException
     */
    public function onAllRead(MessageParameter $params)
    {
        AdminMessage::init()->setAllReadByUserId($params->getUser()->id);
    }
    
    
    /**
     * 删除消息
     * @param MessageUpdateParameter $params
     * @throws DbException
     */
    public function onDelete(MessageUpdateParameter $params)
    {
        AdminMessage::init()
            ->whereEntity(AdminMessageField::userId($params->getUser()->id))
            ->deleteInfo($params->getId());
    }
    
    
    /**
     * 清空消息
     * @param MessageParameter $params
     * @throws DbException
     */
    public function onClear(MessageParameter $params)
    {
        AdminMessage::init()->clearByUserId($params->getUser()->id);
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