<?php
declare(strict_types = 1);

namespace BusyPHP\model;

use BusyPHP\App;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\app\admin\model\system\config\SystemConfig;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\traits\Container;
use Closure;
use Psr\Log\LoggerInterface;
use think\db\exception\DbException;

/**
 * Setting基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/27 下午上午10:22 Setting.php $
 */
abstract class Setting
{
    use Container;
    
    /**
     * 服务注入
     * @var Closure[]
     */
    protected static $maker = [];
    
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
        $this->app   = App::getInstance();
        $this->model = SystemConfig::init($logger, $connect, $force);
        
        if (is_null($this->key) || $this->key === '') {
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
            $name = basename(str_replace('\\', '/', self::defineContainer()));
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
     * @param string|null $name 数据名称
     * @param mixed       $default 默认值
     * @return mixed
     */
    final public function get(string $name = null, $default = null)
    {
        $data = $this->parseGet($this->model->get($this->key));
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