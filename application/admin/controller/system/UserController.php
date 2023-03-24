<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\js\driver\Table;
use BusyPHP\app\admin\component\js\driver\Tree;
use BusyPHP\app\admin\component\js\driver\tree\TreeFlatNode;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\group\AdminGroupField;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\model\ArrayOption;
use RuntimeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
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
    protected $model;
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = AdminUser::init();
    }
    
    
    /**
     * 管理员管理
     * @return Response
     */
    #[MenuNode(menu: true, parent: '#system_user', icon: 'bicon bicon-user-manager', sort: 1)]
    public function index() : Response
    {
        // 管理员列表数据
        if ($table = Table::initIfRequest()) {
            $table->model($this->model);
            
            switch ($table->getOrderField()) {
                case AdminUserField::formatCreateTime():
                    $table->setOrderField(AdminUserField::createTime());
                break;
                case AdminUserField::formatLastTime():
                    $table->setOrderField(AdminUserField::lastTime());
                break;
            }
            
            $table->query(function(AdminUser $model, ArrayOption $option) {
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
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 添加管理员
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function add() : Response
    {
        if ($this->isPost()) {
            $this->model->create(AdminUserField::parse($this->post()));
            $this->log()->filterParams(['password', 'confirm_password'])->record(self::LOG_INSERT, '添加管理员');
            
            return $this->success('添加成功');
        }
        
        $this->assign([
            'info' => [
                'checked' => 1,
                'system'  => 0
            ]
        ]);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 修改管理员
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
            
            $this->model->modify(AdminUserField::parse($this->post()));
            $this->log()->record(self::LOG_UPDATE, '修改管理员');
            
            return $this->success('修改成功');
        }
        
        $this->assign([
            'info' => $this->model->getInfo($this->get('id/d'))
        ]);
        
        return $this->insideDisplay('add');
    }
    
    
    /**
     * 角色数据
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function group_tree_data() : Response
    {
        $info = null;
        if ($id = $this->get('user_id/d')) {
            $info = $this->model->getInfo($id);
        }
        
        return Tree::init()
            ->setOrder([
                AdminGroupField::sort()->field() => 'asc',
                AdminGroupField::id()->field()   => 'asc',
            ])
            ->model(AdminGroup::init())
            ->list(function(TreeFlatNode $node, AdminGroupField $item, int $index) use ($info) {
                $node->setText($item->name);
                $node->setParent($item->parentId);
                $node->setId($item->id);
                $node->setOpened(true);
                
                if ($info && in_array($item->id, $info->groupIds)) {
                    $node->setSelected(true);
                }
                
                if ($info && $info->system) {
                    $node->setDisabled(true);
                    $node->setOpened(false);
                }
            })
            ->response();
    }
    
    
    /**
     * 删除管理员
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function delete() : Response
    {
        foreach ($this->param('id/list/请选择要删除的用户') as $id) {
            $this->model->remove($id);
        }
        
        $this->log()->record(self::LOG_DELETE, '删除管理员');
        
        return $this->success('删除成功');
    }
    
    
    /**
     * 修改管理员密码
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function password() : Response
    {
        if ($this->isPost()) {
            $this->model->modify(AdminUserField::parse($this->post()), AdminUser::SCENE_PASSWORD);
            $this->log()->filterParams(['password', 'confirm_password'])->record(self::LOG_UPDATE, '修改管理员密码');
            
            return $this->success('修改成功');
        }
        
        $this->assign(['info' => $this->model->getInfo($this->get('id/d'))]);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 启用/禁用管理员
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
        $this->log()->record(self::LOG_UPDATE, '启用/禁用管理员');
        
        return $this->success($status ? '启用成功' : '禁用成功');
    }
    
    
    /**
     * 解锁管理员
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function unlock() : Response
    {
        $this->model->unlock($this->get('id/d'));
        $this->log()->record(self::LOG_UPDATE, '解锁管理员');
        
        return $this->success('解锁成功');
    }
}