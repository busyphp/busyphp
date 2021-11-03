<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\event\AdminPanelDisplayEvent;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\subscribe\MessageAgencySubscribe;
use BusyPHP\app\admin\subscribe\MessageNoticeSubscribe;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 后台首页
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:37 下午 Index.php $
 */
class IndexController extends InsideController
{
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
                $dropList   = [];
                $dropList[] = [
                    'text' => '修改资料',
                    'icon' => 'bicon bicon-user-profile',
                    'attr' => [
                        'data-toggle' => 'busy-modal',
                        'data-url'    => (string) url('Common.User/profile'),
                    ]
                ];
                $dropList[] = [
                    'text' => '修改密码',
                    'icon' => 'bicon bicon-lock',
                    'attr' => [
                        'data-toggle' => 'busy-modal',
                        'data-url'    => (string) url('Common.User/password'),
                    ]
                ];
                $dropList[] = [
                    'text' => '主题设置',
                    'icon' => 'bicon bicon-theme',
                    'attr' => [
                        'data-toggle'          => 'busy-modal',
                        'data-url'             => (string) url('Common.User/theme'),
                        'data-on-hide'         => 'busyAdmin.data.themeClose',
                        'data-form-on-success' => 'busyAdmin.data.themeSuccess',
                        'data-busy-id'         => 'theme-setting',
                    ]
                ];
                $dropList[] = [
                    'type' => 'divider'
                ];
                
                $hasCacheClear  = AdminGroup::checkPermission($this->adminUser, 'system_manager/cache_clear');
                $hasCacheCreate = AdminGroup::checkPermission($this->adminUser, 'system_manager/cache_create');
                if ($hasCacheClear) {
                    $dropList[] = [
                        'text' => '清理缓存',
                        'icon' => 'bicon bicon-clear',
                        'attr' => [
                            'data-toggle'  => 'busy-request',
                            'data-url'     => (string) url('system_manager/cache_clear'),
                            'data-confirm' => '确认要清理缓存吗？<br /><span class="text-warning">当系统发生错误的时候可以通过该方法尝试性修复</span>',
                        ]
                    ];
                }
                
                if ($hasCacheCreate) {
                    $dropList[] = [
                        'text' => '生成缓存',
                        'icon' => 'bicon bicon-re-create',
                        'attr' => [
                            'data-toggle'  => 'busy-request',
                            'data-url'     => (string) url('system_manager/cache_create'),
                            'data-confirm' => '确认要生成缓存吗？<br /><span class="text-success">生成缓存后系统运行速度将会提升</span>',
                        ]
                    ];
                }
                
                if ($hasCacheCreate || $hasCacheClear) {
                    $dropList[] = [
                        'type' => 'divider'
                    ];
                }
                
                $dropList[] = [
                    'text' => '退出登录',
                    'icon' => 'bicon bicon-exit',
                    'attr' => [
                        'data-toggle'     => 'busy-request',
                        'data-url'        => (string) url('admin_out'),
                        'data-confirm'    => '确认要退出登录吗？',
                        'data-on-success' => '@route.redirect',
                    ]
                ];
                
                return $this->success([
                    'menu_default'   => $this->adminUser->defaultMenu,
                    'menu_list'      => SystemMenu::init()->getNav($this->adminUser),
                    'user_id'        => $this->adminUserId,
                    'username'       => $this->adminUsername,
                    'user_dropdowns' => $dropList,
                    
                    // 消息启用状态
                    'message_notice' => MessageNoticeSubscribe::hasSubscribe(),
                    'message_agency' => MessageAgencySubscribe::hasSubscribe(),
                ]);
            
            // 显示
            default:
                $model            = new AdminUser();
                $mysqlVersionInfo = $model->query("select VERSION()");
                $mysqlVersion     = $mysqlVersionInfo[0]['VERSION()'];
                $this->assign('mysql_version', $mysqlVersion);
                $this->assign('max_upload_size', ini_get('upload_max_filesize'));
                $this->assign('system_name', php_uname('s'));
                $this->assign('soft_name', $_SERVER['SERVER_SOFTWARE'] ?? '');
                $this->assign('framework_name', $this->app->getFrameworkName() . ' V' . $this->app->getFrameworkVersion());
                $this->assign('extend_template', AdminPanelDisplayEvent::triggerEvent('Common.Index/index'));
                $this->setPageTitle('首页');
                
                return $this->display();
        }
    }
}