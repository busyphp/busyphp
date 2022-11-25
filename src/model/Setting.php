<?php
declare(strict_types = 1);

namespace BusyPHP\model;

use BusyPHP\App;
use BusyPHP\exception\ClassNotFoundException;
use BusyPHP\exception\ClassNotImplementsException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\interfaces\SettingInterface;
use BusyPHP\traits\ContainerDefine;
use BusyPHP\traits\ContainerInstance;
use Closure;
use Psr\Log\LoggerInterface;
use think\Container;

/**
 * Setting基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/27 下午上午10:22 Setting.php $
 */
abstract class Setting
{
    use ContainerDefine;
    use ContainerInstance;
    
    
    /**
     * 数据名称
     * @var string
     */
    protected $name = '';
    
    /**
     * @var App
     */
    protected $app;
    
    /**
     * 数据管理类对象
     * @var SettingInterface
     */
    protected $driver;
    
    /**
     * 数据管理类
     * @var class-string<SettingInterface>
     */
    protected static $manager = '\BusyPHP\app\admin\model\system\config\SystemConfig';
    
    /**
     * 服务注入
     * @var Closure[]
     */
    protected static $maker = [];
    
    
    /**
     * 设置服务注入
     * @param Closure $maker
     * @return void
     */
    public static function maker(Closure $maker)
    {
        static::$maker[] = $maker;
    }
    
    
    /**
     * 设置数据管理类
     * @param class-string<SettingInterface> $manager
     */
    public static function setManager(string $manager)
    {
        static::$manager = $manager;
    }
    
    
    /**
     * 获取实例
     * @param LoggerInterface|null $logger 日志接口
     * @param string               $connect 连接标识
     * @param bool                 $force 是否强制重连
     * @return static
     */
    public static function init(LoggerInterface $logger = null, string $connect = '', bool $force = false)
    {
        $vars = [$connect, $force];
        if ($logger) {
            $vars = [$logger, $connect, $force];
        }
        
        return self::makeContainer($vars, true);
    }
    
    
    /**
     * 构造函数
     * @param LoggerInterface|null $logger 日志接口
     * @param string               $connect 连接标识
     * @param bool                 $force 是否强制重连
     */
    public function __construct(LoggerInterface $logger = null, string $connect = '', bool $force = false)
    {
        $this->app = App::getInstance();
        
        // 获取数据名称
        if (is_null($this->name) || $this->name === '') {
            $this->getName();
        }
        
        // 实例化数据管理类
        $manager = static::$manager;
        if (!class_exists($manager)) {
            throw new ClassNotFoundException($manager);
        }
        
        if (!is_subclass_of($manager, SettingInterface::class)) {
            throw new ClassNotImplementsException($manager, SettingInterface::class);
        }
        $vars = [$connect, $force];
        if ($logger) {
            $vars = [$logger, $connect, $force];
        }
        $this->driver = Container::getInstance()->make($manager, $vars, true);
        
        // 执行服务注入
        if (!empty(static::$maker)) {
            foreach (static::$maker as $maker) {
                call_user_func($maker, $this);
            }
        }
    }
    
    
    /**
     * 获取键名
     * @return string
     */
    protected function getName() : string
    {
        if (!$this->name) {
            $name = basename(str_replace('\\', '/', self::getDefineContainer()));
            if (strtolower(substr($name, -7)) === 'setting') {
                $name = substr($name, 0, -7);
            }
            $this->name = StringHelper::snake($name);
        }
        
        return $this->name;
    }
    
    
    /**
     * 设置数据
     * @param array $data
     */
    final public function set(array $data)
    {
        $this->driver->setSettingData($this->name, $this->parseSet($data));
    }
    
    
    /**
     * 获取数据
     * @param string|null $name 数据名称
     * @param mixed       $default 默认值
     * @return mixed
     */
    final public function get(string $name = null, $default = null)
    {
        $data = $this->parseGet($this->driver->getSettingData($this->name));
        if (is_null($name)) {
            return $data;
        }
        
        return ArrayHelper::get($data, $name, $default);
    }
    
    
    /**
     * 设置数据解析器
     * @param array $data
     * @return array
     */
    protected function parseSet(array $data) : array
    {
        return $data;
    }
    
    
    /**
     * 获取数据解析器
     * @param array $data
     * @return array
     */
    protected function parseGet(array $data) : array
    {
        return $data;
    }
}