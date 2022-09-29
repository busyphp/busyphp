<?php

namespace BusyPHP\app\admin\model\system\plugin;

use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 插件信息结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/1 下午上午11:01 SystemPluginPackageInfo.php $
 * @method static Entity name() 名称
 * @method static Entity description() 说明
 * @method static Entity package() 包名
 * @method static Entity version() 版本号
 * @method static Entity authors() 作者信息
 * @method static Entity keywords() 关键词
 * @method static Entity homepage() 主页
 * @method static Entity class() 管理类
 * @method static Entity install() 是否可以安装/卸载
 * @method static Entity setting() 是否可以设置
 * @method static Entity canInstall() 是否允许安装
 * @method static Entity canUninstall() 是否允许卸载
 * @method static Entity canSetting() 是否允许设置
 */
class SystemPluginPackageInfo extends Field
{
    /**
     * 名称
     * @var string
     */
    public $name;
    
    /**
     * 说明
     * @var string
     */
    public $description;
    
    /**
     * 包名
     * @var string
     */
    public $package;
    
    /**
     * 版本号
     * @var string
     */
    public $version;
    
    /**
     * 作者信息
     * @var SystemPluginAuthorInfo[]
     */
    public $authors;
    
    /**
     * 关键词
     * @var string[]
     */
    public $keywords;
    
    /**
     * 主页
     * @var array
     */
    public $homepage;
    
    /**
     * 管理类
     * @var string
     */
    public $class;
    
    /**
     * 是否可以安装/卸载
     * @var bool
     */
    public $install;
    
    /**
     * 安装/卸载配置
     * @var SystemPluginInstallConfig
     */
    public $installConfig;
    
    /**
     * 是否可以设置
     * @var bool
     */
    public $setting;
    
    /**
     * 设置配置
     * @var SystemPluginSettingConfig
     */
    public $settingConfig;
    
    /**
     * 是否允许安装
     * @var bool
     */
    public $canInstall;
    
    /**
     * 是否允许卸载
     * @var bool
     */
    public $canUninstall;
    
    /**
     * 是否允许设置
     * @var string
     */
    public $canSetting;
    
    /**
     * @var SystemPluginInfo[]
     */
    protected static $_pluginList;
    
    
    /**
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function onParseAfter()
    {
        if (!isset(self::$_pluginList)) {
            self::$_pluginList = SystemPlugin::init()->getList();
        }
        
        if ($this->installConfig->uninstallOperate->requestConfirm) {
            $this->installConfig->uninstallOperate->requestConfirm = str_replace([
                '__package__',
                '__name__',
                '__version__'
            ], [
                $this->package,
                $this->name,
                $this->version
            ], $this->installConfig->uninstallOperate->requestConfirm);
        }
        
        
        if ($this->installConfig->installOperate->requestConfirm) {
            $this->installConfig->installOperate->requestConfirm = str_replace([
                '__package__',
                '__name__',
                '__version__'
            ], [
                $this->package,
                $this->name,
                $this->version
            ], $this->installConfig->installOperate->requestConfirm);
        }
        
        // 查询配置
        if ($pluginInfo = self::$_pluginList[SystemPlugin::createId($this->package)] ?? null) {
            // 已标记安装
            if ($pluginInfo->install) {
                $canInstall   = false;
                $canUninstall = $this->install;
                $canSetting   = $this->setting;
            } else {
                $canInstall   = $this->install;
                $canUninstall = false;
                $canSetting   = false;
            }
        } else {
            // 支持安装
            if ($this->install) {
                $canInstall   = $this->install;
                $canUninstall = false;
                $canSetting   = false;
            } else {
                $canInstall   = false;
                $canUninstall = false;
                $canSetting   = $this->setting;
            }
        }
        
        $this->canInstall   = $canInstall;
        $this->canUninstall = $canUninstall;
        $this->canSetting   = $canSetting;
    }
}