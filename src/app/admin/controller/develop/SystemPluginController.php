<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\plugin\SystemPluginPackageInfo;
use BusyPHP\app\admin\model\system\plugin\SystemPlugin;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\Response;

/**
 * 插件管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/18 下午下午6:38 SystemPluginController.php $
 */
class SystemPluginController extends InsideController
{
    /**
     * 插件列表
     */
    public function index() : Response
    {
        if ($this->pluginTable) {
            $list = Collection::make(SystemPlugin::getPluginList());
            
            if ($this->pluginTable->word) {
                $list = $list->whereLike(SystemPluginPackageInfo::name(), $this->pluginTable->word);
                $list = array_values($list->toArray());
            }
            
            return $this->success($this->pluginTable->result($list, count($list)));
        }
        
        
        return $this->display();
    }
    
    
    /**
     * 安装插件
     * @return Response
     * @throws DataNotFoundException
     */
    public function install() : Response
    {
        return SystemPlugin::manager($this->param('package/s', 'trim'), $this->adminUser)->install();
    }
    
    
    /**
     * 卸载插件
     * @return Response
     * @throws DataNotFoundException
     */
    public function uninstall() : Response
    {
        return SystemPlugin::manager($this->param('package/s', 'trim'), $this->adminUser)->uninstall();
    }
    
    
    /**
     * 插件设置
     * @return Response
     * @throws DataNotFoundException
     */
    public function setting() : Response
    {
        return SystemPlugin::manager($this->param('package/s', 'trim'), $this->adminUser)->setting();
    }
}