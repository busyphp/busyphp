<?php

namespace BusyPHP\app\admin\model\admin\user;

/**
 * 更新管理员后事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 AdminUserEventUpdateAfter.php $
 * @property AdminUserField $finalInfo 更新后的用户信息
 */
class AdminUserEventUpdateAfter extends AdminUserEventUpdateBefore
{
    public function __construct(AdminUser $model, AdminUserField $data, string $scene, $prepare, AdminUserField $info, AdminUserField $finalInfo)
    {
        $this->finalInfo = $finalInfo;
        
        parent::__construct($model, $data, $scene, $prepare, $info);
    }
}