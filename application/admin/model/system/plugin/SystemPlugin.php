<?php

namespace BusyPHP\app\admin\model\system\plugin;

use BusyPHP\App;
use BusyPHP\command\Publish;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use Composer\InstalledVersions;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 插件管理模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/1 下午上午11:51 SystemPlugin.php $
 * @method SystemPluginField getInfo(string $id, string $notFoundMessage = null)
 * @method SystemPluginField|null findInfo(string $id = null)
 * @method SystemPluginField[] selectList()
 * @method SystemPluginField[] indexList(string|Entity $key = '')
 * @method SystemPluginField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 */
class SystemPlugin extends Model implements ContainerInterface
{
    protected string $fieldClass = SystemPluginField::class;
    
    protected string $dataNotFoundMessage = '插件不存在';
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 获取插件
     * @param string $package
     * @return SystemPluginField
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function get(string $package) : SystemPluginField
    {
        $info = $this->where(SystemPluginField::id(static::createId($package)))->findInfo();
        if ($info) {
            return $info;
        }
        
        $data = SystemPluginField::init();
        $data->setId(static::createId($package));
        $data->setCreateTime(time());
        $data->setUpdateTime(time());
        $data->setPackage($package);
        $data->setSetting([]);
        $this->insert($data);
        
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
        $this->where(SystemPluginField::id($this->get($package)->id))->setField(SystemPluginField::install(), 1);
    }
    
    
    /**
     * 设为已卸载
     * @param string $package
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function setUninstall(string $package)
    {
        $this->where(SystemPluginField::id($this->get($package)->id))->setField(SystemPluginField::install(), 0);
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
        $this->where(SystemPluginField::id($this->get($package)->id))
            ->setField(SystemPluginField::setting(), json_encode($setting, JSON_UNESCAPED_UNICODE));
    }
    
    
    /**
     * 获取设置
     * @param string      $package 包名
     * @param string|null $key 参数名称
     * @param mixed       $default 参数默认值
     * @return mixed
     */
    public function getSetting(string $package, ?string $key = null, $default = null)
    {
        $config = [];
        $info   = $this->getList()[static::createId($package)] ?? null;
        if ($info && $info->install) {
            $config = $info->setting;
        }
        
        if (is_null($key)) {
            return $config;
        }
        
        return $config[$key] ?? $default;
    }
    
    
    /**
     * 获取插件集合
     * @param bool $force
     * @return array<string, SystemPluginField>
     */
    public function getList(bool $force = false) : array
    {
        return $this->rememberCacheByCallback('list', function() {
            $list = $this->order(SystemPluginField::sort(), 'asc')->order(SystemPluginField::id(), 'asc')->selectList();
            
            return ArrayHelper::listByKey($list, SystemPluginField::id()->name());
        }, $force);
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
     * 获取Composer插件集合
     * @return SystemPluginPackageInfo[]
     */
    public static function getPluginList() : array
    {
        $list = [];
        foreach (Publish::packages(App::getInstance()->debug() ? './composer.json' : '') as $item) {
            $manager = $item['manager'] ?? [];
            if (!$manager) {
                continue;
            }
            
            // 基本信息
            $manager['description'] = $item['description'] ?? '';
            $manager['package']     = $item['name'];
            $manager['version']     = InstalledVersions::getPrettyVersion($item['name']);
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
            $manager['install']          = (bool) $install;
            $config                      = is_bool($install) ? [] : (array) $install;
            $config['install_operate']   = SystemPluginOperateConfig::parse((array) ($config['install_operate'] ?? []));
            $config['uninstall_operate'] = SystemPluginOperateConfig::parse((array) ($config['uninstall_operate'] ?? []));
            $manager['install_config']   = SystemPluginInstallConfig::parse($config);
            
            // 设置配置
            $setting                   = $manager['setting'] ?? false;
            $manager['setting']        = (bool) $setting;
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
        $list = static::getPluginList();
        $list = ArrayHelper::listByKey($list, SystemPluginPackageInfo::package()->name());
        if (!isset($list[$package])) {
            throw new DataNotFoundException("包 $package 信息不存在");
        }
        
        return $list[$package];
    }
    
    
    /**
     * @inheritDoc
     */
    public function onChanged(string $method, $id, array $options)
    {
        $this->getList(true);
    }
}