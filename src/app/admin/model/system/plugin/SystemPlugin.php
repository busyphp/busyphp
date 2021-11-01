<?php

namespace BusyPHP\app\admin\model\system\plugin;

use BusyPHP\App;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\command\InstallCommand;
use BusyPHP\contract\abstracts\PluginManager;
use BusyPHP\contract\structs\items\PackageInfo;
use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\exception\ClassNotFoundException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\Model;
use Composer\InstalledVersions;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 插件管理模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/1 下午上午11:51 SystemPlugin.php $
 * @method SystemPluginInfo findInfo($data = null, $notFoundMessage = null)
 * @method SystemPluginInfo getInfo($data, $notFoundMessage = null)
 * @method SystemPluginInfo[] selectList()
 */
class SystemPlugin extends Model
{
    protected $bindParseClass      = SystemPluginInfo::class;
    
    protected $dataNotFoundMessage = '插件不存在';
    
    
    /**
     * 获取插件
     * @param string $package
     * @return SystemPluginInfo
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function getPackage(string $package)
    {
        $info = $this->whereEntity(SystemPluginField::id(self::createId($package)))->findInfo();
        if ($info) {
            return $info;
        }
        
        $data             = SystemPluginField::init();
        $data->id         = self::createId($package);
        $data->createTime = time();
        $data->updateTime = time();
        $data->package    = $package;
        $data->setting    = '';
        $this->addData($data);
        
        return $this->getInfo($data->id);
    }
    
    
    /**
     * 设为已安装
     * @param string $package
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function setInstall(string $package)
    {
        $this->whereEntity(SystemPluginField::id($this->getPackage($package)->id))
            ->setField(SystemPluginField::install(), 1);
    }
    
    
    /**
     * 设为已卸载
     * @param string $package
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function setUninstall(string $package)
    {
        $this->whereEntity(SystemPluginField::id($this->getPackage($package)->id))
            ->setField(SystemPluginField::install(), 0);
    }
    
    
    /**
     * 设置参数
     * @param string $package
     * @param array  $setting
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function setSetting(string $package, array $setting = [])
    {
        $this->whereEntity(SystemPluginField::id($this->getPackage($package)->id))
            ->setField(SystemPluginField::setting(), json_encode($setting, JSON_UNESCAPED_UNICODE));
    }
    
    
    /**
     * 获取设置
     * @param string $package 包名
     * @param string $key 参数名称
     * @param mixed  $default 参数默认值
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getSetting(string $package, ?string $key = null, $default = null)
    {
        $config = [];
        $info   = $this->getList()[self::createId($package)] ?? null;
        if ($info) {
            $config = $info->setting ?: [];
        }
        
        if (is_null($key)) {
            return $config;
        }
        
        return $config[$key] ?? $default;
    }
    
    
    /**
     * 获取插件集合
     * @param bool $must
     * @return SystemPluginInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getList($must = false) : array
    {
        $list = $this->getCache('list');
        if (!$list || $must) {
            $list = $this->order(SystemPluginField::sort(), 'asc')->order(SystemPluginField::id(), 'asc')->selectList();
            $list = ArrayHelper::listByKey($list, SystemPluginField::id());
            $this->setCache('list', $list);
        }
        
        return $list;
    }
    
    
    /**
     * 生成ID
     * @param string $package
     * @return string
     */
    public static function createId(string $package) : string
    {
        return md5($package);
    }
    
    
    /**
     * 创建管理类
     * @param string        $package
     * @param AdminUserInfo $userInfo
     * @return PluginManager
     * @throws DataNotFoundException
     */
    public static function manager(string $package, AdminUserInfo $userInfo) : PluginManager
    {
        $list = static::getPackageList();
        $list = ArrayHelper::listByKey($list, PackageInfo::package());
        $info = $list[$package] ?? null;
        if (!$info) {
            throw new DataNotFoundException("插件 {$package} 不存在");
        }
        
        if (!$info->class || !class_exists($info->class)) {
            throw new ClassNotFoundException($info->class, "插件 {$package} 管理类");
        }
        
        $manager = new $info->class(App::init());
        if (!$manager instanceof PluginManager) {
            throw new ClassNotExtendsException($manager, PluginManager::class, "插件 {$package} 管理类");
        }
        $manager->setParams($userInfo, $info);
        
        return $manager;
    }
    
    
    /**
     * 获取Composer插件集合
     * @return PackageInfo[]
     */
    public static function getPackageList() : array
    {
        $list = [];
        foreach (InstallCommand::packages(App::init()->debug() ? './composer.json' : '') as $item) {
            $manager = $item['manager'] ?? [];
            if (!$manager) {
                continue;
            }
            
            // 基本信息
            $manager['description'] = $item['description'] ?? '';
            $manager['package']     = $item['name'];
            $manager['version']     = InstalledVersions::getVersion($item['name']);
            $manager["authors"]     = $item['authors'] ?? [];
            $manager["keywords"]    = $item['keywords'] ?? [];
            $manager["homepage"]    = $item['homepage'] ?? '';
            $manager['class']       = $manager['class'] ?? '';
            $list[]                 = PackageInfo::parse($manager);
        }
        
        return $list;
    }
    
    
    /**
     * 获取包信息
     * @param string $package
     * @return PackageInfo
     * @throws DataNotFoundException
     */
    public static function getPackageInfo(string $package) : PackageInfo
    {
        $list = self::getPackageList();
        $list = ArrayHelper::listByKey($list, PackageInfo::package());
        if (!isset($list[$package])) {
            throw new DataNotFoundException("包 {$package} 信息不存在");
        }
        
        return $list[$package];
    }
    
    
    /**
     * @inheritDoc
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function onChanged(string $method, $id, array $options)
    {
        $this->getList(true);
    }
}