<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\message\todo;

use BusyPHP\app\admin\model\admin\user\AdminUserInfo;

/**
 * TodoParameter
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/19 19:53 TodoParameter.php $
 */
class TodoParameter
{
    /**
     * @var AdminUserInfo
     */
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