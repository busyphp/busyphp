<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\message\provide;

use BusyPHP\app\admin\model\admin\user\AdminUserInfo;

/**
 * 消息参数模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/17 下午9:17 下午 MessageParams.php $
 */
class MessageParams
{
    /** @var AdminUserInfo */
    private $user;
    
    
    /**
     * 设置管理员信息
     * @param AdminUserInfo $user
     */
    public function setUser(AdminUserInfo $user) : void
    {
        $this->user = $user;
    }
    
    
    /**
     * 获取管理员信息
     * @return AdminUserInfo
     */
    public function getUser() : AdminUserInfo
    {
        return $this->user;
    }
}