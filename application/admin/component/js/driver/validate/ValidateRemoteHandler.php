<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\driver\validate;

use BusyPHP\app\admin\component\js\driver\ValidateRemote;
use BusyPHP\app\admin\component\js\Handler;

/**
 * JS组件[busyAdmin.plugins.FormVerify] Remote 处理类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/14 11:13 ValidateRemoteHandler.php $
 * @see ValidateRemote
 * @property ValidateRemote $driver
 */
class ValidateRemoteHandler extends Handler
{
    /**
     * 查询处理回调
     * @return void|null|false 返回false代表阻止系统处理关键词搜索的相关请求参数
     */
    public function query()
    {
        return null;
    }
}