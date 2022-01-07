<?php

namespace BusyPHP\model;

use BusyPHP\App;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\app\admin\model\system\config\SystemConfig;
use BusyPHP\helper\StringHelper;
use Closure;
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
     * @var string 键名
     */
    protected $key = '';
    
    /**
     * @var Setting[]
     */
    private static $inits = [];
    
    /**
     * @var App
     */
    protected $app;
    
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
     * 快速实例化
     * @return $this
     */
    public static function init() : Setting
    {
        if (!isset(self::$inits[static::class])) {
            self::$inits[static::class] = new static();
        }
        
        return self::$inits[static::class];
    }
    
    
    public function __construct()
    {
        $this->app = App::getInstance();
        
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
     * @param mixed $data
     * @throws DbException
     * @throws ParamInvalidException
     */
    final public function set($data)
    {
        SystemConfig::init()->setKey($this->key, $this->parseSet($data));
    }
    
    
    /**
     * 获取数据
     * @param string $name 数据名称
     * @param mixed  $default 默认值
     * @return mixed
     */
    final public function get(string $name = '', $default = null)
    {
        $data = $this->parseGet(SystemConfig::init()->get($this->key));
        
        if (!$name) {
            return $data;
        }
        
        return $data[$name] ?? $default;
    }
    
    
    /**
     * 打印方法结构
     */
    final public function __printMethod()
    {
        $data   = $this->get();
        $string = '';
        foreach ($data as $k => $v) {
            $name = ucfirst(StringHelper::camel($k));
            if (is_bool($v)) {
                $type = 'bool';
            } elseif (is_array($v)) {
                $type = 'array';
            } elseif (is_object($v)) {
                $type = 'object';
            } elseif (is_float($v)) {
                $type = 'float';
            } elseif (is_numeric($v)) {
                $type = 'int';
            } else {
                $type = 'string';
            }
            
            $string .= "/**<br />";
            $string .= "&nbsp;* 获取<br />";
            $string .= "&nbsp;* @return {$type}<br />";
            $string .= "&nbsp;*/<br />";
            $string .= "public function get{$name}() { <br />";
            $string .= "&nbsp;&nbsp;&nbsp;&nbsp;return \$this->get('{$k}'); <br />";
            $string .= "}<br />";
        }
        
        echo $string;
    }
    
    
    /**
     * 获取数据解析器
     * @param mixed $data
     * @return mixed
     */
    abstract protected function parseGet($data);
    
    
    /**
     * 设置数据解析器
     * @param mixed $data
     * @return mixed
     */
    abstract protected function parseSet($data);
}