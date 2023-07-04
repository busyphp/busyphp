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
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
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
     * 用户模型
     * @var AdminUser
     */
    protected AdminUser $model;
    
    /**
     * 模型字段类
     * @var AdminUserField|string
     */
    protected mixed $field;
    
    /**
     * 角色模型
     * @var AdminGroup
     */
    protected AdminGroup $groupModel;
    
    /**
     * 角色字段类
     * @var AdminGroupField|string
     */
    protected mixed $groupField;
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model      = AdminUser::init();
        $this->groupModel = AdminGroup::init();
        $this->field      = $this->model->getFieldClass();
        $this->groupField = $this->groupModel->getFieldClass();
    }
    
    
    /**
     * 赋值列表模板参数
     */
    protected function assignIndexData()
    {
        $this->assign('status', ['不限', '正常', '禁用', '临时禁用']);
        $sexMap = $this->model::getSexMap();
        $sexMap = [-1 => '不限'] + $sexMap;
        $this->assign('sex', $sexMap);
    }
    
    
    /**
     * 自定义列表查询条件
     * @param AdminUser   $model
     * @param ArrayOption $option
     */
    protected function indexTableQuery(AdminUser $model, ArrayOption $option)
    {
    }
    
    
    /**
     * 系统用户管理
     * @return Response
     */
    #[MenuNode(menu: true, parent: '#system_user', icon: 'bicon bicon-user-manager', sort: -100)]
    public function index() : Response
    {
        // 管理员列表数据
        if ($table = Table::initIfRequest()) {
            // 非系统管理员且设置了隐藏系统管理员的情况下
            if (!$this->adminUser->system && $this->app->config->get('app.admin.hide_system_user')) {
                $this->model->where(AdminUserField::system(0));
            }
            
            $table->model($this->model)->query(function(AdminUser $model, ArrayOption $option) {
                $option->deleteIfLt('sex', 0);
                
                switch ($option->pull('status', 0, 'intval')) {
                    // 正常
                    case 1:
                        $model->where($this->field::checked(1));
                    break;
                    // 禁用
                    case 2:
                        $model->where($this->field::checked(0));
                    break;
                    // 临时锁定
                    case 3:
                        $model->where($this->field::errorRelease('>', time()));
                    break;
                }
                
                if ($groupId = $option->pull('group_id', 0, 'trim')) {
                    $groupId = explode(',', $groupId);
                    $groupId = (int) end($groupId);
                    
                    if ($groupId) {
                        $childIds = array_column(
                            $this->groupModel->getAllSubRoles($groupId, true),
                            $this->groupField::id()->name()
                        );
                        
                        $model->where(function(AdminUser $model) use ($childIds) {
                            foreach ($childIds as $childId) {
                                $model->whereOr($this->field::groupIds(), 'like', "%,$childId,%");
                            }
                        });
                    }
                }
                
                $this->indexTableQuery($model, $option);
            });
            
            return $table->response();
        }
        
        $this->assignIndexData();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 赋值添加/更新时的模版参数
     * @param bool  $edit 是否更新
     * @param array $defaultInfo 新增时默认值
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function assignSaveData(bool $edit = false, array $defaultInfo = [])
    {
        $sexMap = $this->model::getSexMap();
        unset($sexMap[$this->model::SEX_UNKNOWN]);
        
        if ($edit) {
            $info         = $this->model->getInfo($this->get('id/d'));
            $avatarFileId = $info->id;
        } else {
            $info         = $defaultInfo + ['checked' => 1, 'system' => 0];
            $avatarFileId = SystemFile::class()::createTmp();
        }
        
        $this->assign([
            'info'           => $info,
            'avatar_file_id' => $avatarFileId,
            'validate'       => $this->model->getViewValidateRule(),
            'sex'            => $sexMap
        ]);
    }
    
    
    /**
     * 添加用户
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -90)]
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
                ->create($this->field::init($this->post()));
            
            $this->log()->filterParams(['password', 'confirm_password'])->record(self::LOG_INSERT, '添加系统用户');
            
            return $this->success('添加成功');
        }
        
        $this->assignSaveData();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 修改用户
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -80)]
    public function edit() : Response
    {
        if ($this->isPost()) {
            $id      = $this->post('id/d');
            $checked = $this->post('checked/b');
            if ($id == $this->adminUserId && $this->request->has('checked') && !$checked) {
                throw new RuntimeException('不能禁用自己');
            }
            
            $this->model->modify($this->field::init($this->post()));
            $this->log()->record(self::LOG_UPDATE, '修改系统用户');
            
            return $this->success('修改成功');
        }
        
        $this->assignSaveData(true);
        
        return $this->insideDisplay('add');
    }
    
    
    /**
     * 删除用户
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -70)]
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
    #[MenuNode(menu: false, parent: '/index', sort: -60)]
    public function password() : Response
    {
        if ($this->isPost()) {
            $this->model->modify($this->field::init($this->post()), $this->model::SCENE_PASSWORD);
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
    #[MenuNode(menu: false, parent: '/index', sort: -50)]
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
    #[MenuNode(menu: false, parent: '/index', sort: -40)]
    public function unlock() : Response
    {
        $this->model->unlock($this->get('id/d'));
        $this->log()->record(self::LOG_UPDATE, '解锁系统用户');
        
        return $this->success('解锁成功');
    }
    
    
    /**
     * 查看重要资料
     */
    #[MenuNode(menu: false, parent: '/index', sort: -30)]
    public function show_detail_important_info()
    {
        throw new HttpException(404);
    }
}