<?php

namespace BusyPHP\app\admin\controller\common;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\admin\user\AdminUser;

/**
 * 后台首页
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:37 下午 Index.php $
 */
class Index extends InsideController
{
    /**
     * 后台首页
     */
    public function index()
    {
        $model            = new AdminUser();
        $mysqlVersionInfo = $model->query("select VERSION()");
        $mysqlVersion     = $mysqlVersionInfo[0]['VERSION()'];
        $softNames        = explode(' ', $_SERVER['SERVER_SOFTWARE']);
        $this->assign('mysql_version', $mysqlVersion);
        $this->assign('max_upload_size', ini_get('upload_max_filesize'));
        $this->assign('system_name', php_uname('s'));
        $this->assign('soft_name', $softNames[0]);
        $this->assign('framework_name', $this->app->getBusyName() . ' V' . $this->app->getBusyVersion());
        
        return $this->display();
    }
}