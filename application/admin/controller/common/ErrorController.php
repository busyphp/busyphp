<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\AdminController;

/**
 * ErrorController
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/9 11:37 ErrorController.php $
 */
class ErrorController extends AdminController
{
    protected function initialize($checkLogin = false)
    {
        parent::initialize($checkLogin);
    }
    
    
    public function __call($name, $arguments)
    {
        if ($this->isLogin()) {
            return $this->display(__DIR__ . '/../../view/error.html');
        } else {
            return $this->display(__DIR__ . '/../../view/error_page.html');
        }
    }
}