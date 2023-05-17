<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\common\SimpleForm;
use BusyPHP\app\admin\component\js\driver\Table;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\group\AdminGroupField;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\admin\user\AdminUserEventCreateAfter;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\model\ArrayOption;
use RuntimeException;
use think\exception\HttpException;
use think\Response;
use Throwable;

/**
 * 管理员管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午12:02 下午 UserController.php $
 */
#[MenuRoute(path: 'system_user', class: true)]
class UserController extends InsideController
{
    /**
     * @var AdminUser
     */
    protected AdminUser $model;
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = AdminUser::init();
    }
    
    
    /**
     * 系统用户管理
     * @return Response
     */
    #[MenuNode(menu: true, parent: '#system_user', icon: 'bicon bicon-user-manager', sort: 1)]
    public function index() : Response
    {
        // 管理员列表数据
        if ($table = Table::initIfRequest()) {
            $table->model($this->model)->query(function(AdminUser $model, ArrayOption $option) {
                $option->deleteIfLt('sex', 0, true);
                
                switch ($option->pull('status', 0, 'intval')) {
                    // 正常
                    case 1:
                        $model->where(AdminUserField::checked(1));
                    break;
                    // 禁用
                    case 2:
                        $model->where(AdminUserField::checked(0));
                    break;
                    // 临时锁定
                    case 3:
                        $model->where(AdminUserField::errorRelease('>', time()));
                    break;
                }
                
                if ($groupId = $option->pull('group_id', 0, 'trim')) {
                    $groupId = explode(',', $groupId);
                    $groupId = (int) end($groupId);
                    
                    if ($groupId) {
                        $childIds = array_column(
                            AdminGroup::instance()->getAllSubRoles($groupId, true),
                            AdminGroupField::id()->name()
                        );
                        
                        $model->where(function(AdminUser $model) use ($childIds) {
                            foreach ($childIds as $childId) {
                                $model->whereOr(AdminUserField::groupIds(), 'like', "%,$childId,%");
                            }
                        });
                    }
                }
            });
            
            return $table->response();
        }
        
        $this->assign('status', ['不限', '正常', '禁用', '临时禁用']);
        $sexs = $this->model::getSexs();
        $sexs = [0 => '不限'] + $sexs;
        $this->assign('sex', $sexs);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 添加用户
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function add() : Response
    {
        if ($this->isPost()) {
            $this->model
                ->listen(AdminUserEventCreateAfter::class, function(AdminUserEventCreateAfter $event) {
                    $fileId = $this->post('avatar_file_id/s', 'trim');
                    if ($fileId !== '') {
                        SystemFile::init()->updateValue($fileId, "{$event->info->id}");
                    }
                })
                ->create(AdminUserField::init($this->post()));
            
            $this->log()->filterParams(['password', 'confirm_password'])->record(self::LOG_INSERT, '添加系统用户');
            
            return $this->success('添加成功');
        }
        
        $this->assign([
            'info'           => [
                'checked' => 1,
                'system'  => 0,
            ],
            'avatar_file_id' => SystemFile::createTmp(),
            'validate'       => $this->model->getViewValidateRule(),
        ]);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 修改用户
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function edit() : Response
    {
        if ($this->isPost()) {
            $id      = $this->post('id/d');
            $checked = $this->post('checked/b');
            if ($id == $this->adminUserId && $this->request->has('checked') && !$checked) {
                throw new RuntimeException('不能禁用自己');
            }
            
            $this->model->modify(AdminUserField::init($this->post()));
            $this->log()->record(self::LOG_UPDATE, '修改系统用户');
            
            return $this->success('修改成功');
        }
        
        $info = $this->model->getInfo($this->get('id/d'));
        $this->assign([
            'info'           => $info,
            'avatar_file_id' => $info->id,
            'validate'       => $this->model->getViewValidateRule(),
        ]);
        
        return $this->insideDisplay('add');
    }
    
    
    /**
     * 删除用户
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function delete() : Response
    {
        SimpleForm::init()->batch($this->param('id/a', 'intval'), '请选择要删除的用户', function(int $id) {
            $this->model->remove($id);
        });
        
        $this->log()->record(self::LOG_DELETE, '删除系统用户');
        
        return $this->success('删除成功');
    }
    
    
    /**
     * 修改密码
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function password() : Response
    {
        if ($this->isPost()) {
            $this->model->modify(AdminUserField::init($this->post()), AdminUser::SCENE_PASSWORD);
            $this->log()->filterParams(['password', 'confirm_password'])->record(self::LOG_UPDATE, '修改系统用户密码');
            
            return $this->success('修改成功');
        }
        
        $this->assign(['info' => $this->model->getInfo($this->get('id/d'))]);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 启用/禁用用户
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function change_checked() : Response
    {
        $id = $this->get('id/d');
        if ($id == $this->adminUserId) {
            throw new RuntimeException('不能禁用自己');
        }
        
        $status = $this->get('status/b');
        $this->model->changeChecked($id, $status);
        $this->log()->record(self::LOG_UPDATE, '启用/禁用系统用户');
        
        return $this->success($status ? '启用成功' : '禁用成功');
    }
    
    
    /**
     * 解锁用户
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function unlock() : Response
    {
        $this->model->unlock($this->get('id/d'));
        $this->log()->record(self::LOG_UPDATE, '解锁系统用户');
        
        return $this->success('解锁成功');
    }
    
    
    /**
     * 查看重要资料
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function show_detail_important_info()
    {
        throw new HttpException(404);
    }
}