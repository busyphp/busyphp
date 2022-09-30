<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\App;
use BusyPHP\app\admin\controller\develop\plugin\SystemPluginBaseController;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\plugin\SystemPluginPackageInfo;
use BusyPHP\app\admin\model\system\plugin\SystemPlugin;
use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\exception\ClassNotFoundException;
use BusyPHP\helper\ArrayHelper;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\Response;

/**
 * 插件管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/18 下午下午6:38 PluginController.php $
 */
class PluginController extends InsideController
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
        return $this->manager($this->param('package/s', 'trim'))->install();
    }
    
    
    /**
     * 卸载插件
     * @return Response
     * @throws DataNotFoundException
     */
    public function uninstall() : Response
    {
        return $this->manager($this->param('package/s', 'trim'))->uninstall();
    }
    
    
    /**
     * 插件设置
     * @return Response
     * @throws DataNotFoundException
     */
    public function setting() : Response
    {
        return $this->manager($this->param('package/s', 'trim'))->setting();
    }
    
    
    /**
     * 创建管理类
     * @param string $package
     * @return SystemPluginBaseController
     * @throws DataNotFoundException
     */
    private function manager(string $package) : SystemPluginBaseController
    {
        $list = SystemPlugin::getPluginList();
        $list = ArrayHelper::listByKey($list, SystemPluginPackageInfo::package());
        $info = $list[$package] ?? null;
        if (!$info) {
            throw new DataNotFoundException("插件 {$package} 不存在");
        }
        
        if (!$info->class || !class_exists($info->class)) {
            throw new ClassNotFoundException($info->class, "插件 {$package} 管理类");
        }
        
        $manager = new $info->class(App::getInstance());
        if (!$manager instanceof SystemPluginBaseController) {
            throw new ClassNotExtendsException($manager, SystemPluginBaseController::class);
        }
        $manager->setParams($this->adminUser, $info);
        
        return $manager;
    }
}