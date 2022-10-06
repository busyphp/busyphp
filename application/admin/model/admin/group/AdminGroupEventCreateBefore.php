<?php

namespace BusyPHP\app\admin\model\admin\group;

/**
 * 创建角色组前事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 AdminGroupEventCreateBefore.php $
 * @property mixed $prepare 通过 {@see AdminGroupEventCreatePrepare} 取到的参数
 */
class AdminGroupEventCreateBefore extends AdminGroupEventCreatePrepare
{
    public function __construct(AdminGroup $model, AdminGroupField $data, $prepare)
    {
        $this->prepare = $prepare;
        
        parent::__construct($model, $data);
    }
}