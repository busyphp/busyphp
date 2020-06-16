<?php

namespace BusyPHP\model;

use BusyPHP\App;
use BusyPHP\exception\SQLException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\file\File;
use BusyPHP\app\admin\model\system\config\SystemConfig;

/**
 * Config基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午11:53 上午 Setting.php $
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
    
    
    public function __construct()
    {
        if (!$this->key) {
            $this->getKey();
        }
    }
    
    
    /**
     * 快速实例化
     * @return $this
     */
    public static function init()
    {
        if (!isset(self::$inits[static::class])) {
            self::$inits[static::class] = new static();
        }
        
        return self::$inits[static::class];
    }
    
    
    /**
     * 获取键名
     * @return string
     */
    protected function getKey()
    {
        if (!$this->key) {
            $name = basename(str_replace('\\', '/', static::class));
            if (strtolower(substr($name, -7)) === 'setting') {
                $name = substr($name, 0, -7);
            }
            $this->key = parse_name($name);
        }
        
        return $this->key;
    }
    
    
    /**
     * 设置数据
     * @param mixed $data
     * @throws SQLException
     * @throws VerifyException
     */
    final public function set($data)
    {
        SystemConfig::init()->setKey($this->key, $this->parseSet($data));
        
        // 生成配置
        self::createConfig();
    }
    
    
    /**
     * 获取数据
     * @param string $name 数据名称
     * @return mixed|null
     */
    final public function get($name = '')
    {
        static $_data;
        
        if (!isset($_data)) {
            $_data = $this->parseGet(SystemConfig::init()->get($this->key));
        }
        
        if (!$name) {
            return $_data;
        }
        
        return $_data[$name] ?? null;
    }
    
    
    /**
     * 打印方法结构
     */
    final public function __printMethod()
    {
        $data   = $this->get();
        $string = '';
        foreach ($data as $k => $v) {
            $name = parse_name($k, 1);
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
    
    
    /**
     * 生成全局配置
     */
    public static function createConfig()
    {
        $list   = SystemConfig::init()->field('type')->whereRaw("is_append=1 OR type='public'")->selecting();
        $config = [];
        static::parseNamespace($list, 'BusyPHP\\app\\admin\\setting\\', $config);
        static::parseNamespace($list, 'core\\setting\\', $config);
        
        // 生成系统配置
        $string = var_export($config, true);
        File::write(App::runtimeConfigPath() . 'config.php', "<?php // 本配置由系统自动生成 \n\n return {$string};");
    }
    
    
    protected static function parseNamespace($list, $namespace, &$config)
    {
        foreach ($list as $i => $r) {
            $name         = parse_name($r['type'], 1);
            $class        = $namespace . $name;
            $classSetting = $class . 'Setting';
            if (class_exists($class)) {
                $setting = new $class;
            } elseif (class_exists($classSetting)) {
                $setting = new $classSetting;
            } else {
                continue;
            }
            
            $config[$r['type']] = $setting->get();
        }
    }
}