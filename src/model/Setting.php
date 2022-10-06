<?php
declare(strict_types = 1);

namespace BusyPHP\model;

use BusyPHP\App;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\app\admin\model\system\config\SystemConfig;
use BusyPHP\helper\StringHelper;
use Closure;
use Psr\Log\LoggerInterface;
use think\db\exception\DbException;

/**
 * Config基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/27 下午上午10:22 Setting.php $
 */
abstract class Setting
{
    /**
     * 服务注入
     * @var Closure[]
     */
    protected static $maker = [];
    
    /**
     * @var Setting[]
     */
    private static $inits = [];
    
    /**
     * @var SystemConfig
     */
    private static $manager;
    
    /**
     * @var string 键名
     */
    protected $key = '';
    
    /**
     * @var App
     */
    protected $app;
    
    /**
     * @var SystemConfig
     */
    protected $model;
    
    
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
     * 快速实例化
     * @param LoggerInterface|null $logger 日志接口
     * @param string               $connect 连接标识
     * @param bool                 $force 是否强制重连
     * @return $this
     */
    public static function init(LoggerInterface $logger = null, string $connect = '', bool $force = false) : self
    {
        if (!isset(self::$inits[static::class])) {
            self::$inits[static::class] = new static($logger, $connect, $force);
        }
        
        return self::$inits[static::class];
    }
    
    
    /**
     * @param LoggerInterface|null $logger 日志接口
     * @param string               $connect 连接标识
     * @param bool                 $force 是否强制重连
     */
    protected function __construct(LoggerInterface $logger = null, string $connect = '', bool $force = false)
    {
        $this->app = App::getInstance();
        
        if (!isset(self::$manager)) {
            self::$manager = SystemConfig::init();
        }
        
        if ($logger || $connect !== '' || $force) {
            $this->model = SystemConfig::init($logger, $connect, $force);
        } else {
            $this->model = self::$manager;
        }
        
        if (!$this->key) {
            $this->getKey();
        }
        
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
    protected function getKey() : string
    {
        if (!$this->key) {
            $name = basename(str_replace('\\', '/', static::class));
            if (strtolower(substr($name, -7)) === 'setting') {
                $name = substr($name, 0, -7);
            }
            $this->key = StringHelper::snake($name);
        }
        
        return $this->key;
    }
    
    
    /**
     * 设置数据
     * @param array $data
     * @throws DbException
     * @throws ParamInvalidException
     */
    final public function set(array $data)
    {
        $this->model->setKey($this->key, $this->parseSet($data));
    }
    
    
    /**
     * 获取数据
     * @param string $name 数据名称
     * @param mixed  $default 默认值
     * @return mixed
     */
    final public function get(string $name = '', $default = null)
    {
        $data = $this->parseGet($this->model->get($this->key));
        
        if (!$name) {
            return $data;
        }
        
        return $data[$name] ?? $default;
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