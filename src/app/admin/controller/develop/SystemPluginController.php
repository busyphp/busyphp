<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\plugin\SystemPlugin;
use BusyPHP\contract\abstracts\PluginManager;
use BusyPHP\contract\structs\items\PackageInfo;
use Exception;
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
     * 获取管理器
     * @param string $package 包名
     * @return PluginManager
     * @throws DataNotFoundException
     */
    private function manager(string $package) : PluginManager
    {
        return SystemPlugin::manager($package, $this->adminUser);
    }
    
    
    /**
     * 插件列表
     */
    public function index()
    {
        if ($this->pluginTable) {
            $list = Collection::make(SystemPlugin::getPackageList());
            
            if ($this->pluginTable->word) {
                $list = $list->whereLike(PackageInfo::name(), $this->pluginTable->word);
                $list = array_values($list->toArray());
            }
            
            return $this->success($this->pluginTable->result($list, count($list)));
        }
        
        
        return $this->display();
    }
    
    
    /**
     * 安装插件
     * @return Response
     * @throws Exception
     */
    public function install()
    {
        return $this->manager($this->param('package/s', 'trim'))->install();
    }
    
    
    /**
     * 卸载插件
     * @return Response
     * @throws Exception
     */
    public function uninstall()
    {
        return $this->manager($this->param('package/s', 'trim'))->uninstall();
    }
    
    
    /**
     * 插件设置
     * @return Response
     * @throws Exception
     */
    public function setting()
    {
        return $this->manager($this->param('package/s', 'trim'))->setting();
    }
}