<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;

/**
 * TODO 插件管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/18 下午下午6:38 SystemPluginController.php $
 */
class SystemPluginController extends InsideController
{
    /**
     * 插件列表
     */
    public function index()
    {
        return $this->display();
    }
    
    
    /**
     * 安装插件
     */
    public function install()
    {
    }
    
    
    /**
     * 卸载插件
     */
    public function uninstall()
    {
    }
}