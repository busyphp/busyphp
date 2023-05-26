<?php
declare(strict_types = 1);

namespace BusyPHP;

use BusyPHP\helper\ArrayHelper;
use BusyPHP\uploader\Driver;
use BusyPHP\uploader\driver\Local;
use think\Manager;

/**
 * Uploads
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/5/25 23:20 Uploader.php $
 * @mixin Driver
 */
class Uploader extends Manager
{
    protected       $namespace = '\\BusyPHP\\uploader\\driver\\';
    
    protected array $nameMap   = [];
    
    protected array $classMap  = [];
    
    
    /**
     * @inheritDoc
     */
    public function getDefaultDriver()
    {
        return $this->getConfig('default') ?: Local::class;
    }
    
    
    /**
     * 让驱动名称支持类名
     * @param string $name
     * @return string
     */
    protected function name(string $name) : string
    {
        if (!isset($this->nameMap[$name])) {
            if (is_subclass_of($name, Driver::class)) {
                $class                 = $name;
                $name                  = $name::configName();
                $this->classMap[$name] = $class;
            }
            $this->nameMap[$name] = $name;
        }
        
        return $this->nameMap[$name];
    }
    
    
    /**
     * @inheritDoc
     */
    protected function resolveType(string $name)
    {
        return $this->getDriverConfig($name, 'type', Local::configName());
    }
    
    
    /**
     * @inheritDoc
     */
    protected function resolveConfig(string $name)
    {
        return $this->getDriverConfig($name);
    }
    
    
    /**
     * 获取驱动实例
     * @inheritDoc
     * @return Driver
     */
    public function driver(string $name = null) : Driver
    {
        return parent::driver($name);
    }
    
    
    /**
     * 获取配置
     * @param string|null $name 配置名称
     * @param mixed|null  $default 默认值
     * @return mixed
     */
    public function getConfig(string $name = null, mixed $default = null) : mixed
    {
        if (null === $name) {
            return $this->app->config->get('uploader', []);
        }
        
        return $this->app->config->get('uploader.' . $name, $default);
    }
    
    
    /**
     * 获取驱动配置
     * @param string      $driver 驱动名称
     * @param string|null $name 配置名称
     * @param mixed|null  $default 默认值
     * @return mixed
     */
    public function getDriverConfig(string $driver, string $name = null, mixed $default = null) : mixed
    {
        $driver = $this->name($driver);
        $config = $this->getConfig('drivers.' . $driver, []);
        if (!$config && isset($this->classMap[$driver])) {
            $config = ['type' => $driver];
        }
        
        return ArrayHelper::get($config, $name, $default);
    }
}