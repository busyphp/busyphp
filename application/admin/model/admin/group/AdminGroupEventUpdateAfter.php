<?php

namespace BusyPHP\app\admin\model\admin\group;

/**
 * 更新角色组后事件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/13 下午2:32 AdminGroupEventUpdateAfter.php $
 * @property AdminGroupField $info 更新后的角色组信息
 * @property AdminGroupField $finalInfo
 */
class AdminGroupEventUpdateAfter extends AdminGroupEventUpdateBefore
{
    public function __construct(AdminGroup $model, AdminGroupField $data, string $scene, $prepare, AdminGroupField $info, AdminGroupField $finalInfo)
    {
        $this->finalInfo = $finalInfo;
        
        parent::__construct($model, $data, $scene, $prepare, $info);
    }
}