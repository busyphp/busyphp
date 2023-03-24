<?php

namespace BusyPHP\app\admin\model\admin\user;

/**
 * 删除管理员前事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 AdminUserEventDeleteBefore.php $
 * @property AdminUserField $info 删除前的用户数据
 * @property mixed         $prepare 通过事件{@see AdminUserEventDeletePrepare}取到的数据
 */
class AdminUserEventDeleteBefore extends AdminUserEventDeletePrepare
{
    public function __construct(AdminUser $model, int $id, AdminUserField $info, $prepare)
    {
        $this->info    = $info;
        $this->prepare = $prepare;
        
        parent::__construct($model, $id);
    }
}