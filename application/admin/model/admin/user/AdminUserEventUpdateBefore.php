<?php

namespace BusyPHP\app\admin\model\admin\user;

/**
 * 更新管理员前事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 AdminUserEventUpdateBefore.php $
 * @property AdminUserField $info 更新前的数据
 * @property mixed         $prepare 通过{@see AdminUserEventUpdatePrepare}取到的数据
 */
class AdminUserEventUpdateBefore extends AdminUserEventUpdatePrepare
{
    public function __construct(AdminUser $model, AdminUserField $data, string $scene, $prepare, AdminUserField $info)
    {
        $this->prepare = $prepare;
        $this->info    = $info;
        
        parent::__construct($model, $data, $scene);
    }
}