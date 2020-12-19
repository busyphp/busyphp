<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\event\AdminPanelDisplayEvent;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\message\MessageListParams;
use BusyPHP\app\admin\model\message\MessageUpdateParams;
use BusyPHP\app\admin\model\message\MessageParams;
use BusyPHP\app\admin\subscribe\MessageAgencySubscribe;
use BusyPHP\app\admin\subscribe\MessageNoticeSubscribe;
use BusyPHP\helper\util\Transform;

/**
 * 后台首页
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:37 下午 Index.php $
 */
class Index extends InsideController
{
    /**
     * 后台首页
     */
    public function index()
    {
        $model            = new AdminUser();
        $mysqlVersionInfo = $model->query("select VERSION()");
        $mysqlVersion     = $mysqlVersionInfo[0]['VERSION()'];
        $softNames        = explode(' ', $_SERVER['SERVER_SOFTWARE']);
        $this->assign('mysql_version', $mysqlVersion);
        $this->assign('max_upload_size', ini_get('upload_max_filesize'));
        $this->assign('system_name', php_uname('s'));
        $this->assign('soft_name', $softNames[0]);
        $this->assign('framework_name', $this->app->getBusyName() . ' V' . $this->app->getBusyVersion());
        $this->assign('extend_template', AdminPanelDisplayEvent::triggerEvent('Common.Index/index'));
        
        return $this->display();
    }
    
    
    /**
     * 消息通知
     */
    public function message()
    {
        try {
            $action = $this->iGet('action', 'trim');
            $type   = $this->iGet('type', 'trim');
            
            $params = new MessageParams();
            $params->setUserId($this->adminUserId);
            $params->setUsername($this->adminUsername);
            $params->setUser($this->adminUser);
            $params->setPermission($this->adminPermission);
            
            if ($type == 'notice') {
                if ($action == 'list') {
                    $listParams = new MessageListParams();
                    $listParams->setUserId($this->adminUserId);
                    $listParams->setUsername($this->adminUsername);
                    $listParams->setUser($this->adminUser);
                    $listParams->setPermission($this->adminPermission);
                    $listParams->setPage($this->iGet('page'));
                    $list = [];
                    foreach (MessageNoticeSubscribe::triggerList($listParams) as $item) {
                        $list[] = [
                            'id'          => $item->getId(),
                            'title'       => $item->getTitle(),
                            'desc'        => $item->getDesc(),
                            'create_time' => Transform::date($item->getCreateTime()),
                            'is_read'     => $item->isRead(),
                            'url'         => $item->getOperateUrl(),
                            'icon'        => [
                                'is_class' => $item->isIconClass(),
                                'value'    => $item->isIconClass() ? $item->getIcon() : $item->getImageUrl(),
                                'color'    => $item->getIconColor()
                            ]
                        ];
                    }
                    
                    return $this->success('', '', ['list' => $list]);
                } elseif ($action == 'read' || $action == 'delete') {
                    $updateParams = new MessageUpdateParams();
                    $updateParams->setUserId($this->adminUserId);
                    $updateParams->setUsername($this->adminUsername);
                    $updateParams->setUser($this->adminUser);
                    $updateParams->setPermission($this->adminPermission);
                    $updateParams->setId($this->iGet('id'));
                    
                    if ($action == 'delete') {
                        MessageNoticeSubscribe::triggerDelete($updateParams);
                    } else {
                        MessageNoticeSubscribe::triggerRead($updateParams);
                    }
                } elseif ($action == 'clear' || $action == 'all_read') {
                    if ($action == 'clear') {
                        MessageNoticeSubscribe::triggerClear($params);
                    } else {
                        MessageNoticeSubscribe::triggerAllRead($params);
                    }
                }
                
                return $this->success('', '', []);
            } elseif ($type == 'agency') {
                if ($action == 'list') {
                    $list = [];
                    foreach (MessageAgencySubscribe::triggerList($params) as $item) {
                        $list[] = [
                            'id'    => $item->getId(),
                            'url'   => $item->getOperateUrl(),
                            'title' => $item->getTitle(),
                            'desc'  => $item->getDesc(),
                        ];
                    }
                    
                    return $this->success('', '', ['list' => $list]);
                } elseif ($action == 'read') {
                    $updateParams = new MessageUpdateParams();
                    $updateParams->setUserId($this->adminUserId);
                    $updateParams->setUsername($this->adminUsername);
                    $updateParams->setUser($this->adminUser);
                    $updateParams->setPermission($this->adminPermission);
                    $updateParams->setId($this->iGet('id'));
                    
                    MessageAgencySubscribe::triggerRead($updateParams);
                }
                
                return $this->success('', '', []);
            } else {
                $noticeTotal = MessageNoticeSubscribe::triggerTotal($params);
                $agencyTotal = MessageAgencySubscribe::triggerTotal($params);
                
                return $this->success('', '', [
                    'notice_total' => $noticeTotal,
                    'agency_total' => $agencyTotal,
                    'total'        => $noticeTotal + $agencyTotal
                ]);
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}