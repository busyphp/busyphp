<?php

namespace BusyPHP\app\admin\model\admin\group;

/**
 * 删除角色组前事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32  AdminGroupEventDeleteBefore.php $
 * @property AdminGroupField $info 删除前的角色组数据
 * @property mixed          $prepare 通过 {@see AdminGroupEventDeletePrepare} 获取的数据
 */
class AdminGroupEventDeleteBefore extends AdminGroupEventDeletePrepare
{
    public function __construct(AdminGroup $model, int $id, AdminGroupField $info, $prepare)
    {
        $this->prepare = $prepare;
        $this->info    = $info;
        
        parent::__construct($model, $id);
    }
}