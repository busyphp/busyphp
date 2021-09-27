<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\js\struct\TreeFlatItemStruct;
use BusyPHP\app\admin\model\admin\group\AdminGroupInfo;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\user\AdminUser;
use BusyPHP\app\admin\model\admin\user\AdminUserField;
use BusyPHP\model\Map;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 后台管理员管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午12:02 下午 User.php $
 */
class SystemUserController extends InsideController
{
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
    public function index()
    {
        // 管理员列表数据
        if ($this->pluginTable) {
            $this->pluginTable->setQueryHandler(function(AdminUser $model, Map $data) {
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
                
                if ($this->pluginTable->sortField == AdminUserInfo::formatCreateTime()) {
                    $this->pluginTable->sortField = AdminUserInfo::createTime();
                } elseif ($this->pluginTable->sortField == AdminUserInfo::formatLastTime()) {
                    $this->pluginTable->sortField = AdminUserInfo::lastTime();
                }
            });
            
            return $this->success($this->pluginTable->build($this->model));
        }
        
        $this->assign('group_options', AdminGroup::init()->getTreeOptions());
        
        return $this->display();
    }
    
    
    /**
     * 添加管理员
     * @return Response
     * @throws DbException
     * @throws VerifyException
     * @throws Exception
     */
    public function add()
    {
        if ($this->isPost()) {
            $insert = AdminUserField::init();
            $insert->setGroupIds($this->post('group_ids/a'));
            $insert->setUsername($this->post('username/s', 'trim'));
            $insert->setPassword($this->post('password/s', 'trim'), $this->post('confirm_password/s', 'trim'));
            $insert->setPhone($this->post('phone/s', 'trim'));
            $insert->setEmail($this->post('email/s', 'trim'));
            $insert->setQq($this->post('qq/s', 'trim'));
            $insert->setChecked($this->post('checked/b'));
            $this->model->insertData($insert);
            $this->log()->filterParams(['password', 'confirm_password'])->record(self::LOG_INSERT, '添加管理员');
            
            return $this->success('添加成功');
        }
        
        // 权限数据
        if ($this->pluginTree) {
            return $this->groupTree();
        }
        
        $this->assign('info', ['checked' => 1]);
        
        return $this->display();
    }
    
    
    /**
     * 修改管理员
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ParamInvalidException
     * @throws VerifyException
     * @throws Exception
     */
    public function edit()
    {
        if ($this->isPost()) {
            $update = AdminUserField::init();
            $update->setId($this->post('id/d'));
            $update->setGroupIds($this->post('group_ids/a'));
            $update->setUsername($this->post('username/s', 'trim'));
            $update->setPhone($this->post('phone/s', 'trim'));
            $update->setEmail($this->post('email/s', 'trim'));
            $update->setQq($this->post('qq/s', 'trim'));
            $update->setChecked($this->post('checked/b'));
            $this->model->updateData($update);
            $this->log()->record(self::LOG_UPDATE, '修改管理员');
            
            return $this->success('修改成功');
        }
        
        // 权限数据
        $info = $this->model->getInfo($this->get('id/d'));
        if ($this->pluginTree) {
            return $this->groupTree($info);
        }
        
        $this->assign('info', $info);
        
        return $this->display('add');
    }
    
    
    /**
     * 角色数据
     * @param AdminUserInfo $info
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    private function groupTree(?AdminUserInfo $info = null) : Response
    {
        $this->pluginTree->setNodeHandler(function(AdminGroupInfo $item, TreeFlatItemStruct $node) use ($info) {
            $node->setText($item->name);
            $node->setParent($item->parentId);
            $node->setId($item->id);
            $node->state->setOpened(true);
            
            if ($info && in_array($item->id, $info->groupIds)) {
                $node->state->setSelected(true);
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
     * @throws Exception
     */
    public function password()
    {
        if ($this->isPost()) {
            $this->model->updatePassword($this->adminUserId, $this->post('password/s', 'trim'), $this->post('confirm_password/s', 'trim'));
            $this->log()->filterParams(['password', 'confirm_password'])->record(self::LOG_UPDATE, '修改管理员密码');
            
            return $this->success('修改成功');
        }
        
        $info = $this->model->getInfo($this->get('id/d'));
        $this->assign('info', $info);
        
        return $this->display();
    }
    
    
    /**
     * 启用/禁用管理员
     * @throws Exception
     */
    public function change_checked()
    {
        $status = $this->get('status/b');
        $this->model->changeChecked($this->get('id/d'), $status);
        $this->log()->record(self::LOG_UPDATE, '启用/禁用管理员');
        
        return $this->success($status ? '启用成功' : '禁用成功');
    }
    
    
    /**
     * 解锁管理员
     * @throws Exception
     */
    public function unlock()
    {
        $this->model->unlock($this->get('id/d'));
        $this->log()->record(self::LOG_UPDATE, '解锁管理员');
        
        return $this->success('解锁成功');
    }
    
    
    /**
     * 删除
     */
    public function delete()
    {
        $this->bind(self::CALL_BATCH_EACH, function($id) {
            $this->model->deleteInfo($id);
        });
        
        $this->bind(self::CALL_BATCH_EACH_AFTER, function($params) {
            $this->log()->record(self::LOG_DELETE, '删除管理员');
            
            return '删除成功';
        });
        
        return $this->batch();
    }
    
    
    /**
     * 修改个人资料
     * @return Response
     * @throws Exception
     */
    public function personal_info()
    {
        if ($this->isPost()) {
            $update = AdminUserField::init();
            $update->setId($this->adminUserId);
            $update->setUsername($this->post('username/s', 'trim'));
            $update->setPhone($this->post('phone/s', 'trim'));
            $update->setEmail($this->post('email/s', 'trim'));
            $update->setQq($this->post('qq/s', 'trim'));
            $this->model->whereEntity(AdminUserField::id($this->adminUserId))->updateData($update);
            $this->log()->record(self::LOG_UPDATE, '修改个人资料');
            
            return $this->success('修改成功');
        }
        
        $this->assign('info', $this->adminUser);
        
        return $this->display();
    }
    
    
    /**
     * 修改个人密码
     * @return Response
     * @throws Exception
     */
    public function personal_password()
    {
        if ($this->isPost()) {
            $oldPassword = $this->post('old_password/s', 'trim');
            if (!$oldPassword) {
                throw new VerifyException('请输入登录密码', 'old_password');
            }
            
            if (!AdminUser::verifyPassword($oldPassword, $this->adminUser->password)) {
                throw new VerifyException('登录密码输入错误', 'old_password');
            }
            
            $this->model->updatePassword($this->adminUserId, $this->post('password/s', 'trim'), $this->post('confirm_password/s', 'trim'));
            $this->log()
                ->filterParams(['old_password', 'password', 'confirm_password'])
                ->record(self::LOG_UPDATE, '修改个人密码');
            
            return $this->success('修改成功');
        }
        
        return $this->display();
    }
}