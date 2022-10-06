<?php

namespace BusyPHP\app\admin\model\admin\user;

/**
 * 创建管理员前事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 AdminUserEventCreateBefore.php $
 * @property mixed $prepare 通过事件{@see AdminUserEventCreatePrepare}取到的数据
 */
class AdminUserEventCreateBefore extends AdminUserEventCreatePrepare
{
    public function __construct(AdminUser $model, AdminUserField $data, $prepare)
    {
        $this->prepare = $prepare;
        
        parent::__construct($model, $data);
    }
}