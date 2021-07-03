<?php

namespace BusyPHP\app\admin\model\admin\message\provide;

use BusyPHP\app\admin\model\admin\group\AdminGroupInfo;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;

/**
 * 消息参数模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/17 下午9:17 下午 MessageParams.php $
 */
class MessageParams
{
    /** @var int */
    private $userId = 0;
    
    /** @var string */
    private $username = '';
    
    /** @var AdminUserInfo */
    private $user;
    
    /** @var AdminGroupInfo */
    private $permission;
    
    
    /**
     * 设置管理员信息
     * @param AdminUserInfo $user
     */
    public function setUser(AdminUserInfo $user) : void
    {
        $this->user = $user;
    }
    
    
    /**
     * 设置当前权限信息
     * @param AdminGroupInfo $permission
     */
    public function setPermission(AdminGroupInfo $permission) : void
    {
        $this->permission = $permission;
    }
    
    
    /**
     * 设置管理员ID
     * @param int $userId
     */
    public function setUserId($userId) : void
    {
        $this->userId = intval($userId);
    }
    
    
    /**
     * 设置管理员账号
     * @param string $username
     */
    public function setUsername($username) : void
    {
        $this->username = $username;
    }
    
    
    /**
     * 获取管理员ID
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId;
    }
    
    
    /**
     * 获取当前权限信息
     * @return AdminGroupInfo
     */
    public function getPermission() : AdminGroupInfo
    {
        return $this->permission;
    }
    
    
    /**
     * 获取管理员信息
     * @return AdminUserInfo
     */
    public function getUser() : AdminUserInfo
    {
        return $this->user;
    }
    
    
    /**
     * 获取管理员账号
     * @return string
     */
    public function getUsername() : string
    {
        return $this->username;
    }
}