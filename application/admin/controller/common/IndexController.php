<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\common\message\subscribe\MessageAgencySubscribe;
use BusyPHP\app\admin\controller\common\message\subscribe\MessageNoticeSubscribe;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use think\Container;
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
    /** @var string 后台首页魔板 */
    const TEMPLATE_INDEX = self::class . 'index';
    
    
    /**
     * 后台首页
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function index() : Response
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
                
                // 是否启用切换主题
                if ($this->app->config->get('app.admin.theme', true)) {
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
                }
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
                
                // 自定义header下拉菜单
                foreach ($this->app->config->get('app.admin.user_dropdowns', []) as $item) {
                    if (is_callable($item)) {
                        $dropList[] = Container::getInstance()->invokeFunction($item, [$this->adminUser]);
                    } else {
                        $dropList[] = $item;
                    }
                }
                
                $dropList[] = [
                    'text' => '退出登录',
                    'icon' => 'bicon bicon-exit',
                    'attr' => [
                        'data-toggle'     => 'busy-request',
                        'data-url'        => (string) url('admin_out'),
                        'data-confirm'    => '确认要退出登录吗？',
                        'data-on-success' => 'busyAdmin.app._outLogin',
                    ]
                ];
                
                
                // 自定义全局数据
                $data = $this->app->config->get('app.admin.data', []);
                if ($data) {
                    if (is_callable($data)) {
                        $data = Container::getInstance()->invokeFunction($data, [$this->adminUser]);
                    } else {
                        $data = is_array($data) ? $data : [];
                    }
                } else {
                    $data = [];
                }
                
                return $this->success([
                    'menu_default'   => $this->adminUser->defaultMenu,
                    'menu_list'      => SystemMenu::init()->getNav($this->adminUser),
                    'user_id'        => $this->adminUserId,
                    'username'       => $this->adminUsername,
                    'user_dropdowns' => $dropList,
                    'user'           => [
                        'id'               => $this->adminUser->id,
                        'username'         => $this->adminUser->username,
                        'phone'            => $this->adminUser->phone,
                        'qq'               => $this->adminUser->qq,
                        'email'            => $this->adminUser->email,
                        'group_names'      => $this->adminUser->groupNames,
                        'group_rule_ids'   => $this->adminUser->groupRuleIds,
                        'group_rule_paths' => $this->adminUser->groupRulePaths,
                        'system'           => $this->adminUser->system,
                        'theme'            => $this->adminUser->theme,
                    ],
                    'data'           => $data,
                    
                    // 消息启用状态
                    'message_notice' => MessageNoticeSubscribe::getInstance()->hasSubscribe(),
                    'message_agency' => MessageAgencySubscribe::getInstance()->hasSubscribe(),
                ]);
            
            // 显示
            default:
                $model            = new AdminUser();
                $mysqlVersionInfo = $model->query("select VERSION()");
                $mysqlVersion     = $mysqlVersionInfo[0]['VERSION()'];
                $this->setPageTitle('首页');
                
                return $this->display($this->getUseTemplate(self::TEMPLATE_INDEX, '', [
                    'mysql_version'   => $mysqlVersion,
                    'max_upload_size' => ini_get('upload_max_filesize'),
                    'system_name'     => php_uname('s'),
                    'soft_name'       => $_SERVER['SERVER_SOFTWARE'] ?? '',
                    'framework_name'  => $this->app->getFrameworkName() . ' V' . $this->app->getFrameworkVersion(),
                ]));
        }
    }
}