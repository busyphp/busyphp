<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\event\AdminPanelDisplayEvent;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\admin\message\provide\MessageListParams;
use BusyPHP\app\admin\model\admin\message\provide\MessageUpdateParams;
use BusyPHP\app\admin\model\admin\message\provide\MessageParams;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\subscribe\MessageAgencySubscribe;
use BusyPHP\app\admin\subscribe\MessageNoticeSubscribe;
use BusyPHP\exception\PartUploadSuccessException;
use BusyPHP\file\upload\PartUpload;
use BusyPHP\helper\util\Transform;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 后台首页
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:37 下午 Index.php $
 */
class IndexController extends InsideController
{
    protected function initialize($checkLogin = true)
    {
        // 不记路操作
        if ($this->requestPluginName === 'AppMessage') {
            $this->setSaveOperate(false);
        }
        
        parent::initialize($checkLogin);
    }
    
    
    /**
     * 后台首页
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     */
    public function index()
    {
        switch ($this->requestPluginName) {
            // 输出程序信息
            case 'AppInfo':
                return $this->appInfo();
            
            // 系统消息/待办
            case 'AppMessage':
                return $this->appMessage();
            
            // 文件上传
            case 'Upload':
                return $this->upload();
            
            // 显示
            default:
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
                $this->setPageTitle('首页');
                
                return $this->display();
        }
    }
    
    
    /**
     * 程序信息
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function appInfo()
    {
        return $this->success([
            'menu_default'   => '',
            'menu_list'      => SystemMenu::init()->getNav($this->adminUser),
            'user_id'        => $this->adminUserId,
            'username'       => $this->adminUsername,
            
            // 用户菜单
            'user_dropdowns' => [
                [
                    'text' => '修改资料',
                    'icon' => 'bicon bicon-user-profile',
                    'attr' => [
                        'data-toggle' => 'busy-modal',
                        'data-url'    => (string) url('system_user/personal_info'),
                    ]
                ],
                [
                    'text' => '修改密码',
                    'icon' => 'bicon bicon-lock',
                    'attr' => [
                        'data-toggle' => 'busy-modal',
                        'data-url'    => (string) url('system_user/personal_password'),
                    ]
                ],
                [
                    'type' => 'divider'
                ],
                [
                    'text' => '清理缓存',
                    'icon' => 'bicon bicon-clear',
                    'attr' => [
                        'data-toggle'  => 'busy-request',
                        'data-url'     => (string) url('system_manager/cache_clear'),
                        'data-confirm' => '确认要清理缓存吗？<br /><span class="text-warning">当系统发生错误的时候可以通过该方法尝试性修复</span>',
                    ]
                ],
                [
                    'text' => '生成缓存',
                    'icon' => 'bicon bicon-re-create',
                    'attr' => [
                        'data-toggle'  => 'busy-request',
                        'data-url'     => (string) url('system_manager/cache_create'),
                        'data-confirm' => '确认要生成缓存吗？<br /><span class="text-success">生成缓存后系统运行速度将会提升</span>',
                    ]
                ],
                [
                    'text' => '退出登录',
                    'icon' => 'bicon bicon-exit',
                    'attr' => [
                        'data-toggle'     => 'busy-request',
                        'data-url'        => (string) url('admin_out'),
                        'data-confirm'    => '确认要退出登录吗？',
                        'data-on-success' => '@route.redirect',
                    ]
                ]
            ],
            
            // 消息启用状态
            'message_notice' => MessageNoticeSubscribe::hasSubscribe(),
            'message_agency' => MessageAgencySubscribe::hasSubscribe(),
        ]);
    }
    
    
    /**
     * 上传文件
     * @throws Exception
     */
    protected function upload()
    {
        $this->request->setRequestIsAjax();
        $classType     = $this->post('class_type/s', 'trim');
        $classValue    = $this->post('class_value/s', 'trim');
        $chunkFilename = $this->post('chunk_filename/s', 'trim');
        $chunkComplete = $this->post('chunk_complete/b');
        $chunkTotal    = $this->post('chunk_total/d');
        $chunkCurrent  = $this->post('chunk_current/d');
        $chunkId       = $this->post('chunk_guid/s', 'trim');
        
        try {
            $upload = new PartUpload();
            $upload->setUserId($this->adminUserId);
            $upload->setClassType($classType, $classValue);
            $upload->setName($chunkFilename);
            $upload->setComplete($chunkComplete);
            $upload->setTotal($chunkTotal);
            $upload->setCurrent($chunkCurrent);
            $upload->setId($chunkId);
            
            $result = $upload->upload($this->request->file('upload'));
        } catch (PartUploadSuccessException $e) {
            return $this->success('PART SUCCESS');
        }
        
        $data = [
            'file_url'  => $result->url,
            'file_id'   => $result->id,
            'name'      => $result->name,
            'filename'  => $result->file->getFilename(),
            'extension' => $result->file->getExtension(),
        ];
        $this->log()->record(self::LOG_INSERT, '上传文件', json_encode($data, JSON_UNESCAPED_UNICODE));
        
        return $this->success('上传成功', $data);
    }
    
    
    /**
     * 消息通知
     */
    protected function appMessage()
    {
        $action = $this->get('action', 'trim');
        $type   = $this->get('type', 'trim');
        
        $params = new MessageParams();
        $params->setUser($this->adminUser);
        
        if ($type == 'notice') {
            if ($action == 'list') {
                $listParams = new MessageListParams();
                $listParams->setUser($this->adminUser);
                $listParams->setPage($this->get('page'));
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
                
                return $this->success(['list' => $list]);
            } elseif ($action == 'read' || $action == 'delete') {
                $updateParams = new MessageUpdateParams();
                $updateParams->setUser($this->adminUser);
                $updateParams->setId($this->get('id'));
                
                if ($action == 'delete') {
                    MessageNoticeSubscribe::triggerDelete($updateParams);
                    $message = '删除成功';
                } else {
                    MessageNoticeSubscribe::triggerRead($updateParams);
                    $message = '消息已读';
                }
            } elseif ($action == 'clear' || $action == 'all_read') {
                if ($action == 'clear') {
                    MessageNoticeSubscribe::triggerClear($params);
                    $message = '清空成功';
                } else {
                    MessageNoticeSubscribe::triggerAllRead($params);
                    $message = '操作成功';
                }
            } else {
                $message = '';
            }
            
            return $this->success($message);
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
                
                return $this->success(['list' => $list]);
            } elseif ($action == 'read') {
                $updateParams = new MessageUpdateParams();
                $updateParams->setUser($this->adminUser);
                $updateParams->setId($this->get('id'));
                
                MessageAgencySubscribe::triggerRead($updateParams);
            }
            
            return $this->success();
        } else {
            $noticeTotal = MessageNoticeSubscribe::triggerTotal($params);
            $agencyTotal = MessageAgencySubscribe::triggerTotal($params);
            
            return $this->success([
                'notice_total' => $noticeTotal,
                'agency_total' => $agencyTotal,
                'total'        => $noticeTotal + $agencyTotal
            ]);
        }
    }
}