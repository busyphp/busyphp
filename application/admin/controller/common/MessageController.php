<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\common\message\parameter\MessageListParameter;
use BusyPHP\app\admin\controller\common\message\parameter\MessageParameter;
use BusyPHP\app\admin\controller\common\message\parameter\MessageUpdateParameter;
use BusyPHP\app\admin\controller\common\message\subscribe\MessageAgencySubscribe;
use BusyPHP\app\admin\controller\common\message\subscribe\MessageNoticeSubscribe;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\helper\TransHelper;
use think\Response;

/**
 * 通用通知/待办
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/3 下午下午9:08 MessageController.php $
 */
class MessageController extends InsideController
{
    protected function initialize($checkLogin = true)
    {
        // 不记录操作
        if ($this->requestPluginName === 'AppMessage') {
            $this->setSaveOperate(false);
        }
        
        parent::initialize($checkLogin);
    }
    
    
    /**
     * 消息通知
     */
    public function index() : Response
    {
        $action = $this->get('action', 'trim');
        $type   = $this->get('type', 'trim');
        
        $params = new MessageParameter();
        $params->setUser($this->adminUser);
        
        // 通知
        if ($type == 'notice') {
            switch ($action) {
                case 'read':
                    return $this->noticeRead();
                case 'all_read':
                    return $this->noticeAllRead();
                case 'delete':
                    return $this->noticeDelete();
                case 'clear':
                    return $this->noticeClear();
                default:
                    return $this->noticeList();
            }
        } elseif ($type == 'agency') {
            switch ($action) {
                case 'read':
                    return $this->agencyRead();
                default:
                    return $this->agencyList();
            }
        } else {
            $noticeTotal = MessageNoticeSubscribe::getInstance()->total($params);
            $agencyTotal = MessageAgencySubscribe::getInstance()->total($params);
            
            return $this->success([
                'notice_total' => $noticeTotal,
                'agency_total' => $agencyTotal,
                'total'        => $noticeTotal + $agencyTotal
            ]);
        }
    }
    
    
    /**
     * 通知列表
     */
    protected function noticeList() : Response
    {
        $listParams = new MessageListParameter();
        $listParams->setUser($this->adminUser);
        $listParams->setPage($this->get('page/d'));
        $list = [];
        foreach (MessageNoticeSubscribe::getInstance()->list($listParams) as $item) {
            $list[] = [
                'id'          => $item->getId(),
                'title'       => $item->getTitle(),
                'desc'        => $item->getDesc(),
                'create_time' => TransHelper::date($item->getCreateTime()),
                'is_read'     => $item->isRead(),
                'url'         => $item->getOperateUrl(),
                'icon'        => [
                    'is_class' => $item->isIconClass(),
                    'value'    => $item->isIconClass() ? $item->getIcon() : $item->getImageUrl(),
                    'color'    => $item->getIconColor()
                ]
            ];
        }
        
        return $this->success(['list' => $list]);
    }
    
    
    /**
     * 读取通知
     */
    protected function noticeRead() : Response
    {
        $updateParams = new MessageUpdateParameter();
        $updateParams->setUser($this->adminUser);
        $updateParams->setId($this->get('id/d'));
        MessageNoticeSubscribe::getInstance()->read($updateParams);
        $this->log()->record(self::LOG_UPDATE, '读取通知');
        
        return $this->success();
    }
    
    
    /**
     * 全部已读通知
     */
    protected function noticeAllRead() : Response
    {
        $params = new MessageParameter();
        $params->setUser($this->adminUser);
        MessageNoticeSubscribe::getInstance()->allRead($params);
        $this->log()->record(self::LOG_UPDATE, '全部已读通知');
        
        return $this->success('操作成功');
    }
    
    
    /**
     * 删除通知
     */
    protected function noticeDelete() : Response
    {
        $updateParams = new MessageUpdateParameter();
        $updateParams->setUser($this->adminUser);
        $updateParams->setId($this->get('id/d'));
        MessageNoticeSubscribe::getInstance()->delete($updateParams);
        $this->log()->record(self::LOG_DELETE, '删除通知');
        
        return $this->success('删除成功');
    }
    
    
    /**
     * 清空通知
     */
    protected function noticeClear() : Response
    {
        $params = new MessageParameter();
        $params->setUser($this->adminUser);
        MessageNoticeSubscribe::getInstance()->clear($params);
        $this->log()->record(self::LOG_DELETE, '清空通知');
        
        return $this->success('清空成功');
    }
    
    
    /**
     * 待办列表
     */
    protected function agencyList() : Response
    {
        $params = new MessageParameter();
        $params->setUser($this->adminUser);
        
        $list = [];
        foreach (MessageAgencySubscribe::getInstance()->list($params) as $item) {
            $list[] = [
                'id'    => $item->getId(),
                'url'   => $item->getOperateUrl(),
                'title' => $item->getTitle(),
                'desc'  => $item->getDesc(),
            ];
        }
        
        return $this->success(['list' => $list]);
    }
    
    
    /**
     * 读取待办
     */
    protected function agencyRead() : Response
    {
        $updateParams = new MessageUpdateParameter();
        $updateParams->setUser($this->adminUser);
        $updateParams->setId($this->get('id/d'));
        
        MessageAgencySubscribe::getInstance()->read($updateParams);
        $this->log()->record(self::LOG_UPDATE, '读取待办');
        
        return $this->success();
    }
}