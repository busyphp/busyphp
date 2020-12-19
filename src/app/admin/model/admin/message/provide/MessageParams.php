<?php

namespace BusyPHP\app\admin\model\admin\message\provide;

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
    
    /** @var array */
    private $user = [];
    
    /** @var array */
    private $permission = [];
    
    
    /**
     * 设置管理员信息
     * @param array $user
     */
    public function setUser(array $user) : void
    {
        $this->user = $user;
    }
    
    
    /**
     * 设置当前权限信息
     * @param array $permission
     */
    public function setPermission(array $permission) : void
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
     * @return array
     */
    public function getPermission() : array
    {
        return $this->permission;
    }
    
    
    /**
     * 获取管理员信息
     * @return array
     */
    public function getUser() : array
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