<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\plugin\table\TableHandler;
use BusyPHP\app\admin\plugin\TablePlugin;
use BusyPHP\app\admin\plugin\tree\TreeFlatItemStruct;
use BusyPHP\app\admin\model\admin\group\AdminGroupInfo;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\app\admin\plugin\tree\TreeHandler;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\Model;
use BusyPHP\model\Map;
use think\Container;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;
use Throwable;

/**
 * 后台管理员管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午12:02 下午 User.php $
 */
class UserController extends InsideController
{
    /** @var string 列表模板 */
    const TEMPLATE_INDEX = self::class . 'index';
    
    /** @var string 添加模板 */
    const TEMPLATE_ADD = self::class . 'add';
    
    /** @var string 修改模板 */
    const TEMPLATE_EDIT = self::class . 'edit';
    
    /** @var string 修改密码魔板 */
    const TEMPLATE_PWD = self::class . 'password';
    
    /** @var string 修改个人资料模板 */
    const TEMPLATE_MY_PROFILE = self::class . 'my_profile';
    
    /** @var string 修改个人密码模板 */
    const TEMPLATE_MY_PWD = self::class . 'my_pwd';
    
    /** @var string 用户详情模板 */
    const TEMPLATE_DETAIL = self::class . 'detail';
    
    /**
     * @var AdminUser
     */
    private $model;
    
    
    public function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = AdminUser::init();
    }
    
    
    /**
     * 管理员列表
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function index() : Response
    {
        // 管理员列表数据
        if ($this->pluginTable) {
            $callback = $this->getUseTemplateAttr(self::TEMPLATE_INDEX, 'plugin_table', '');
            if ($callback) {
                Container::getInstance()->invokeFunction($callback, [$this->pluginTable]);
            } else {
                $this->pluginTable->setHandler(new class extends TableHandler {
                    public function query(TablePlugin $plugin, Model $model, Map $data) : void
                    {
                        switch ($data->get('status', 0)) {
                            // 正常
                            case 1:
                                $model->whereEntity(AdminUserField::checked(1));
                            break;
                            // 禁用
                            case 2:
                                $model->whereEntity(AdminUserField::checked(0));
                            break;
                            // 零时锁定
                            case 3:
                                $model->whereEntity(AdminUserField::errorRelease('>', time()));
                            break;
                        }
                        $data->remove('status');
                        
                        if ($groupId = $data->get('group_id', 0)) {
                            $model->whereEntity(AdminUserField::groupIds('like', '%,' . $groupId . ',%'));
                        }
                        $data->remove('group_id');
                        
                        if ($plugin->sortField == AdminUserInfo::formatCreateTime()) {
                            $plugin->sortField = AdminUserInfo::createTime();
                        } elseif ($plugin->sortField == AdminUserInfo::formatLastTime()) {
                            $plugin->sortField = AdminUserInfo::lastTime();
                        }
                    }
                });
            }
            
            return $this->success($this->pluginTable->build($this->model));
        }
        
        return $this->display($this->getUseTemplate(self::TEMPLATE_INDEX, '', [
            'group_options' => AdminGroup::init()->getTreeOptions()
        ]));
    }
    
    
    /**
     * 添加管理员
     * @return Response
     * @throws DbException
     * @throws VerifyException
     * @throws Throwable
     */
    public function add() : Response
    {
        if ($this->isPost()) {
            $this->model->createInfo(AdminUserField::parse($this->post()));
            $this->log()->filterParams(['password', 'confirm_password'])->record(self::LOG_INSERT, '添加管理员');
            
            return $this->success('添加成功');
        }
        
        // 权限数据
        if ($this->pluginTree) {
            return $this->groupTree();
        }
        
        return $this->display($this->getUseTemplate(self::TEMPLATE_ADD, '', [
            'info' => ['checked' => 1]
        ]));
    }
    
    
    /**
     * 修改管理员
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ParamInvalidException
     * @throws VerifyException
     * @throws Throwable
     */
    public function edit() : Response
    {
        if ($this->isPost()) {
            $id      = $this->post('id/d');
            $checked = $this->post('checked/b');
            if ($id == $this->adminUserId && !$checked) {
                throw new VerifyException('不能禁用自己');
            }
            
            $update = AdminUserField::init();
            $update->setId($id);
            $update->setGroupIds($this->post('group_ids/a'));
            $update->setDefaultGroupId($this->post('default_group_id/d'));
            $update->setUsername($this->post('username/s', 'trim'));
            $update->setPhone($this->post('phone/s', 'trim'));
            $update->setEmail($this->post('email/s', 'trim'));
            $update->setQq($this->post('qq/s', 'trim'));
            $update->setChecked($checked);
            $this->model->updateInfo($update);
            $this->log()->record(self::LOG_UPDATE, '修改管理员');
            
            return $this->success('修改成功');
        }
        
        // 权限数据
        $info = $this->model->getInfo($this->get('id/d'));
        if ($this->pluginTree) {
            return $this->groupTree($info);
        }
        
        return $this->display($this->getUseTemplate(self::TEMPLATE_EDIT, 'add', [
            'info' => $info
        ]));
    }
    
    
    /**
     * 角色数据
     * @param AdminUserInfo|null $info
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    private function groupTree(?AdminUserInfo $info = null) : Response
    {
        $this->pluginTree->setHandler(new class($info) extends TreeHandler {
            /**
             * @var AdminUserInfo|null
             */
            private $info;
            
            
            public function __construct(?AdminUserInfo $info)
            {
                $this->info = $info;
            }
            
            
            /**
             * @param AdminGroupInfo     $item
             * @param TreeFlatItemStruct $node
             */
            public function node($item, TreeFlatItemStruct $node) : void
            {
                $node->setText($item->name);
                $node->setParent($item->parentId);
                $node->setId($item->id);
                $node->state->setOpened(true);
                
                if ($this->info && in_array($item->id, $this->info->groupIds)) {
                    $node->state->setSelected(true);
                }
            }
        });
        
        return $this->success($this->pluginTree->build(AdminGroup::init()));
    }
    
    
    /**
     * 修改密码
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws VerifyException
     * @throws ParamInvalidException
     * @throws Throwable
     */
    public function password() : Response
    {
        if ($this->isPost()) {
            $this->model->updateInfo(AdminUserField::parse($this->post()), AdminUser::SCENE_PASSWORD);
            $this->log()->filterParams(['password', 'confirm_password'])->record(self::LOG_UPDATE, '修改管理员密码');
            
            return $this->success('修改成功');
        }
        
        return $this->display($this->getUseTemplate(self::TEMPLATE_PWD, '', [
            'info' => $this->model->getInfo($this->get('id/d'))
        ]));
    }
    
    
    /**
     * 启用/禁用管理员
     * @throws Throwable
     */
    public function change_checked() : Response
    {
        $id = $this->get('id/d');
        if ($id == $this->adminUserId) {
            throw new VerifyException('不能禁用自己');
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
    public function unlock() : Response
    {
        $this->model->unlock($this->get('id/d'));
        $this->log()->record(self::LOG_UPDATE, '解锁管理员');
        
        return $this->success('解锁成功');
    }
    
    
    /**
     * 删除
     * @throws Throwable
     */
    public function delete() : Response
    {
        foreach ($this->param('id/list/请选择要删除的用户') as $id) {
            $this->model->deleteInfo($id);
        }
        
        $this->log()->record(self::LOG_DELETE, '删除管理员');
        
        return $this->success('删除成功');
    }
}