<?php

namespace BusyPHP\app\admin\model\system\plugin;

use BusyPHP\App;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\command\InstallCommand;
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
 * @method SystemPluginInfo[] buildListWithField(array $values, $key = null, $field = null) : array
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
    protected function get(string $package) : SystemPluginInfo
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
        $this->whereEntity(SystemPluginField::id($this->get($package)->id))->setField(SystemPluginField::install(), 1);
    }
    
    
    /**
     * 设为已卸载
     * @param string $package
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function setUninstall(string $package)
    {
        $this->whereEntity(SystemPluginField::id($this->get($package)->id))->setField(SystemPluginField::install(), 0);
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
        $this->whereEntity(SystemPluginField::id($this->get($package)->id))
            ->setField(SystemPluginField::setting(), json_encode($setting, JSON_UNESCAPED_UNICODE));
    }
    
    
    /**
     * 获取设置
     * @param string      $package 包名
     * @param string|null $key 参数名称
     * @param mixed       $default 参数默认值
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getSetting(string $package, ?string $key = null, $default = null)
    {
        $config = [];
        $info   = $this->getList()[self::createId($package)] ?? null;
        if ($info && $info->install) {
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
     * @return SystemPluginManager
     * @throws DataNotFoundException
     */
    public static function manager(string $package, AdminUserInfo $userInfo) : SystemPluginManager
    {
        $list = static::getPluginList();
        $list = ArrayHelper::listByKey($list, SystemPluginPackageInfo::package());
        $info = $list[$package] ?? null;
        if (!$info) {
            throw new DataNotFoundException("插件 {$package} 不存在");
        }
        
        if (!$info->class || !class_exists($info->class)) {
            throw new ClassNotFoundException($info->class, "插件 {$package} 管理类");
        }
        
        $manager = new $info->class(App::getInstance());
        if (!$manager instanceof SystemPluginManager) {
            throw new ClassNotExtendsException($manager, SystemPluginManager::class, "插件 {$package} 管理类");
        }
        $manager->setParams($userInfo, $info);
        
        return $manager;
    }
    
    
    /**
     * 获取Composer插件集合
     * @return SystemPluginPackageInfo[]
     */
    public static function getPluginList() : array
    {
        $list = [];
        foreach (InstallCommand::packages(App::getInstance()->debug() ? './composer.json' : '') as $item) {
            $manager = $item['manager'] ?? [];
            if (!$manager) {
                continue;
            }
            
            // 基本信息
            $manager['description'] = $item['description'] ?? '';
            $manager['package']     = $item['name'];
            $manager['version']     = InstalledVersions::getVersion($item['name']);
            $manager["keywords"]    = $item['keywords'] ?? [];
            $manager["homepage"]    = $item['homepage'] ?? '';
            $manager['class']       = $manager['class'] ?? '';
            
            // 作者
            $manager['authors'] = [];
            foreach ($item['authors'] ?? [] as $value) {
                $manager['authors'][] = SystemPluginAuthorInfo::parse($value);
            }
            
            // 安装配置
            $install                     = $manager['install'] ?? false;
            $manager['install']          = $install ? true : false;
            $config                      = is_bool($install) ? [] : (array) $install;
            $config['install_operate']   = SystemPluginOperateConfig::parse((array) ($config['install_operate'] ?? []));
            $config['uninstall_operate'] = SystemPluginOperateConfig::parse((array) ($config['uninstall_operate'] ?? []));
            $manager['install_config']   = SystemPluginInstallConfig::parse($config);
            
            // 设置配置
            $setting                   = $manager['setting'] ?? false;
            $manager['setting']        = $setting ? true : false;
            $manager['setting_config'] = SystemPluginSettingConfig::parse(is_bool($setting) ? [] : (array) $setting);
            
            $list[] = SystemPluginPackageInfo::parse($manager);
        }
        
        return $list;
    }
    
    
    /**
     * 获取包信息
     * @param string $package
     * @return SystemPluginPackageInfo
     * @throws DataNotFoundException
     */
    public static function getPluginInfo(string $package) : SystemPluginPackageInfo
    {
        $list = self::getPluginList();
        $list = ArrayHelper::listByKey($list, SystemPluginPackageInfo::package());
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