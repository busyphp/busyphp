<?php

namespace BusyPHP\app\admin\js\driver\formverifyremote;

use BusyPHP\app\admin\js\driver\FormVerifyRemote;
use BusyPHP\app\admin\js\Handler;

/**
 * JS组件[busyAdmin.plugins.FormVerify] Remote 处理类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/14 11:13 FormVerifyRemoteHandler.php $
 * @see FormVerifyRemote
 * @property FormVerifyRemote $driver
 */
class FormVerifyRemoteHandler extends Handler
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