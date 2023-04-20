<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\component\common\ConsoleLog;
use BusyPHP\app\admin\component\js\Driver;
use BusyPHP\app\admin\component\message\Notice;
use BusyPHP\app\admin\component\message\Todo;
use BusyPHP\app\admin\controller\AdminHandle;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\helper\ArrayHelper;
use Closure;
use RuntimeException;
use stdClass;
use think\Response;
use Throwable;

/**
 * 后台首页
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:37 下午 IndexController.php $
 */
class IndexController extends InsideController
{
    /**
     * @var bool 是否启用切换主题
     */
    protected $isEnableAppInfoTheme;
    
    
    protected function initialize($checkLogin = true)
    {
        // 不记录操作
        if (Driver::getRequestName() === 'AppMessage') {
            $this->setSaveOperate(false);
        }
        
        // 读取配置获取是否启用切换主题
        if (!isset($this->isEnableAppInfoTheme)) {
            $this->isEnableAppInfoTheme = $this->app->config->get('app.admin.theme', true);
        }
        
        parent::initialize($checkLogin);
    }
    
    
    /**
     * 后台首页
     * @return Response
     * @throws Throwable
     */
    final public function index() : Response
    {
        // 自动响应JS组件数据
        Driver::autoResponse();
        
        return match (Driver::getRequestName()) {
            'AppInfo'    => $this->buildAppInfo(),
            'AppMessage' => $this->buildAppMessage($this->get('type/s', 'trim'), $this->get('action/s', 'trim')),
            'ConsoleLog' => $this->buildConsoleLog($this->param('id/s', 'trim')),
            default      => $this->renderIndex(),
        };
    }
    
    
    /**
     * 构建 AppInfo
     * @return Response
     */
    protected function buildAppInfo() : Response
    {
        $dropList   = [];
        $dropList[] = [
            'text' => '修改资料',
            'icon' => 'bicon bicon-user-profile',
            'attr' => [
                'data-toggle' => 'busy-modal',
                'data-url'    => (string) url('common.user/profile'),
            ]
        ];
        $dropList[] = [
            'text' => '修改密码',
            'icon' => 'bicon bicon-lock',
            'attr' => [
                'data-toggle' => 'busy-modal',
                'data-url'    => (string) url('common.user/password'),
            ]
        ];
        
        // 是否启用切换主题
        if ($this->isEnableAppInfoTheme) {
            $dropList[] = [
                'text' => '主题设置',
                'icon' => 'bicon bicon-theme',
                'attr' => [
                    'data-toggle'          => 'busy-modal',
                    'data-url'             => (string) url('common.user/theme'),
                    'data-on-hide'         => 'busyAdmin.data.themeClose',
                    'data-form-on-success' => 'busyAdmin.data.themeSuccess',
                    'data-busy-id'         => 'theme-setting',
                ]
            ];
        }
        $dropList[] = [
            'type' => 'divider'
        ];
        
        // 清理缓存
        $hasCacheClear  = AdminGroup::class()::checkPermission($this->adminUser, 'system_manager/cache_clear');
        $hasCacheCreate = AdminGroup::class()::checkPermission($this->adminUser, 'system_manager/cache_create');
        if ($hasCacheClear) {
            $dropList[] = [
                'text' => '清理缓存',
                'icon' => 'bicon bicon-clear',
                'attr' => [
                    'data-toggle'     => 'busy-request',
                    'data-url'        => (string) url('system_manager/cache_clear'),
                    'data-confirm'    => '确认要清理缓存吗？<br /><span class="text-warning">当系统发生错误的时候可以通过该方法尝试性修复</span>',
                    'data-on-success' => '@app.reload|@!busyAdmin.app.clearCache'
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
            if ($item instanceof Closure) {
                $dropList[] = $this->app->invokeFunction($item, [$this->adminUser]);
            } else {
                $dropList[] = $item;
            }
        }
        $dropList = array_merge($dropList, $this->buildAppInfoUserDropdowns());
        
        // 退出登录
        $dropList[] = [
            'text' => '退出登录',
            'icon' => 'bicon bicon-exit',
            'attr' => [
                'data-toggle'     => 'busy-request',
                'data-url'        => (string) url('admin_out'),
                'data-confirm'    => '确认要退出登录吗？',
                'data-on-success' => '!busyAdmin.app.outLogin',
            ]
        ];
        
        
        // 自定义全局数据
        $data = $this->app->config->get('app.admin.data', []);
        if ($data instanceof Closure) {
            $data = $this->app->invokeFunction($data, [$this->adminUser]);
        }
        $data = array_merge(is_array($data) ? $data : [], $this->buildAppInfoData());
        if (!ArrayHelper::isAssoc($data)) {
            $data = [];
        }
        
        return $this->success([
            'menu_default'   => $this->adminUser->defaultMenu,
            'menu_list'      => SystemMenu::init()->getNav($this->adminUser),
            'user_id'        => $this->adminUserId,
            'username'       => $this->adminUsername,
            'user_dropdowns' => $dropList,
            'user'           => [
                'id'       => $this->adminUser->id,
                'username' => $this->adminUser->username,
                'phone'    => $this->adminUser->phone,
                'qq'       => $this->adminUser->qq,
                'email'    => $this->adminUser->email,
                'groups'   => $this->adminUser->groupNames,
                'rules'    => $this->adminUser->groupRulePaths,
                'system'   => $this->adminUser->system,
                'theme'    => $this->adminUser->theme,
            ],
            'data'           => $data ?: new stdClass(),
            
            // 消息启用状态
            'message_notice' => Notice::instance()->isEnable(),
            'message_todo'   => Todo::instance()->isEnable(),
        ]);
    }
    
    
    /**
     * 构建 AppInfo 中的 user_dropdowns 数据
     * @return array
     */
    protected function buildAppInfoUserDropdowns() : array
    {
        return [];
    }
    
    
    /**
     * 构建 AppInfo 中的 data 数据
     * @return array
     */
    protected function buildAppInfoData() : array
    {
        return [];
    }
    
    
    /**
     * 构建 AppMessage
     * @param string $type 业务类型
     * @param string $action 操作类型
     * @return Response
     * @throws Throwable
     */
    protected function buildAppMessage(string $type, string $action) : Response
    {
        $notice = Notice::instance();
        $todo   = Todo::instance();
        switch ($type) {
            // 待办
            case 'todo':
                switch ($action) {
                    // 读取
                    case 'read':
                        $todo->setRead($this->adminUser, $this->get('id/s', 'trim'));
                        $this->log()->record(self::LOG_UPDATE, '处理待办');
                        
                        return $this->success();
                    
                    // 获取列表
                    default:
                        $list   = $todo->getList($this->adminUser);
                        $levels = array_column($list, 'level');
                        $sorts  = array_column($list, 'sort');
                        array_multisort($levels, SORT_ASC, $sorts, SORT_ASC, $list);
                        
                        return $this->success([
                            'list' => $list
                        ]);
                }
            
            // 通知
            case 'notice':
                switch ($action) {
                    // 读取
                    case 'read':
                        $notice->setRead($this->adminUser, $this->get('id/s', 'trim'));
                        $this->log()->record(self::LOG_UPDATE, '处理通知');
                        
                        return $this->success();
                    
                    // 全部已读
                    case 'all_read':
                        $notice->setAllRead($this->adminUser);
                        $this->log()->record(self::LOG_UPDATE, '全部已读');
                        
                        return $this->success('操作成功');
                    
                    // 删除通知
                    case 'delete':
                        $notice->delete($this->adminUser, $this->get('id/s', 'trim'));
                        $this->log()->record(self::LOG_DELETE, '删除通知');
                        
                        return $this->success('删除成功');
                    
                    // 清空通知
                    case 'clear':
                        $notice->clear($this->adminUser);
                        $this->log()->record(self::LOG_DELETE, '清空通知');
                        
                        return $this->success('清空完成');
                    
                    // 通知列表
                    default:
                        return $this->success([
                            'list' => $notice->getList($this->adminUser, $this->get('page/d'))
                        ]);
                }
            
            default:
                $todoTotal   = 0;
                $noticeTotal = 0;
                $todo        = Todo::instance();
                $notice      = Notice::instance();
                if ($todo->isEnable()) {
                    $todoTotal = $todo->getTotal($this->adminUser);
                }
                
                if ($notice->isEnable()) {
                    $noticeTotal = $notice->getUnreadTotal($this->adminUser);
                }
                
                return $this->success([
                    'notice_total' => $noticeTotal,
                    'todo_total'   => $todoTotal,
                    'total'        => $noticeTotal + $todoTotal
                ]);
        }
    }
    
    
    /**
     * 构建 ConsoleLog
     * @param string $id 日志ID
     * @return Response
     */
    protected function buildConsoleLog(string $id) : Response
    {
        if (!$id) {
            throw new RuntimeException('日志ID不能为空', AdminHandle::CODE_NEED_EMPTY_CONSOLE_LOG);
        }
        if (!$log = ConsoleLog::get($id)) {
            throw new RuntimeException('无法获取日志', AdminHandle::CODE_NEED_EMPTY_CONSOLE_LOG);
        }
        
        return $this->success($log);
    }
    
    
    /**
     * 渲染首页
     * @return Response
     * @throws Throwable
     */
    protected function renderIndex() : Response
    {
        $this->setPageTitle('首页');
        $this->assign([
            'mysql_version'   => AdminUser::instance()->query("select VERSION()")[0]['VERSION()'] ?? 'Unknown',
            'max_upload_size' => ini_get('upload_max_filesize'),
            'system_name'     => php_uname('s'),
            'soft_name'       => $_SERVER['SERVER_SOFTWARE'] ?? '',
            'framework_name'  => $this->app->getFrameworkName() . ' V' . $this->app->getFrameworkVersion(),
        ]);
        
        return $this->insideDisplay();
    }
}