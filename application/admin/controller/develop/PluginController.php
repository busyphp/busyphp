<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\App;
use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\js\driver\Table;
use BusyPHP\app\admin\controller\develop\plugin\SystemPluginBaseController;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\plugin\SystemPlugin;
use BusyPHP\app\admin\model\system\plugin\SystemPluginPackageInfo;
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
#[MenuRoute(path: 'system_plugin', class: true)]
class PluginController extends InsideController
{
    protected function initialize($checkLogin = true)
    {
        $this->releaseDisabled();
        
        parent::initialize($checkLogin);
    }
    
    
    /**
     * 插件管理
     */
    #[MenuNode(menu: true, parent: '#developer', icon: 'fa fa-plug', sort: 30, canDisable: false)]
    public function index() : Response
    {
        if ($table = Table::initIfRequest()) {
            $list = Collection::make(SystemPlugin::class()::getPluginList());
            if ($word = $table->getWord()) {
                $list = $list->whereLike(SystemPluginPackageInfo::name()->name(), $word);
            }
            
            return $table->list($list, function(Collection $list) {
                return array_values($list->all());
            })->response();
        }
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 安装插件
     * @return Response
     * @throws DataNotFoundException
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function install() : Response
    {
        return $this->manager($this->param('package/s', 'trim'))->install();
    }
    
    
    /**
     * 卸载插件
     * @return Response
     * @throws DataNotFoundException
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function uninstall() : Response
    {
        return $this->manager($this->param('package/s', 'trim'))->uninstall();
    }
    
    
    /**
     * 设置插件
     * @return Response
     * @throws DataNotFoundException
     */
    #[MenuNode(menu: false, parent: '/index')]
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
        $list = SystemPlugin::class()::getPluginList();
        $list = ArrayHelper::listByKey($list, SystemPluginPackageInfo::package()->name());
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