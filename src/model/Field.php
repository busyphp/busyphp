<?php

namespace BusyPHP\model;

use ArrayAccess;
use BusyPHP\exception\MethodNotFoundException;
use BusyPHP\helper\util\Str;
use JsonSerializable;
use think\contract\Arrayable;
use think\contract\Jsonable;

/**
 * 模型字段基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/6 下午3:07 下午 Field.php $
 * @method void onParseAfter() 将数据转为对象后的后置方法
 */
class Field implements Arrayable, Jsonable, ArrayAccess, JsonSerializable
{
    /**
     * Join别名
     * @var array
     */
    private static $_fieldJoinAliasList = [];
    
    
    /**
     * 快速实例化
     * @return $this
     */
    public static function init()
    {
        return new static();
    }
    
    
    /**
     * 数据转对象
     * @param array $array 数据
     * @return $this
     */
    public static function parse($array)
    {
        $obj = static::init();
        foreach ($array as $key => $value) {
            $obj->{Str::camel($key)} = $value;
        }
        
        // 后置操作
        if (method_exists($obj, 'onParseAfter')) {
            $obj->onParseAfter();
        }
        
        return $obj;
    }
    
    
    public function __get($name)
    {
        return $this->{Str::camel($name)};
    }
    
    
    public function __set($name, $value)
    {
        $this->{Str::camel($name)} = $value;
    }
    
    
    /**
     * @param $name
     * @param $arguments
     * @return array|string
     * @throws MethodNotFoundException
     */
    public static function __callStatic($name, $arguments)
    {
        static $fields = [];
        
        $key = static::class;
        if (!isset($fields[$key])) {
            $fields[$key] = array_keys(get_object_vars(new $key()));
        }
        
        // 静态方法名称存在属性中，则返回属性名称
        if (in_array(Str::camel($name), $fields[$key])) {
            $info  = new Entity();
            $field = Str::snake($name);
            $info->setField($field);
            
            if (self::$_fieldJoinAliasList[static::class] ?? false) {
                if (self::$_fieldJoinAliasList[static::class] === true) {
                    $alias = basename(str_replace('\\', '/', static::class));
                } else {
                    $alias = self::$_fieldJoinAliasList[static::class];
                }
                $info->setAlias($alias);
            }
            
            switch (count($arguments)) {
                case 1:
                    $info->setOp('=');
                    $info->setValue($arguments[0]);
                break;
                case 2:
                    $info->setOp($arguments[0]);
                    $info->setValue($arguments[1]);
                break;
            }
            
            return $info;
        }
        
        throw new MethodNotFoundException(static::class, $name, 'static');
    }
    
    
    /**
     * 设置join别名，用完一定要清理 {@see Field::clearJoinAlias()}
     * @param string $alias
     */
    public static function setJoinAlias($alias = null)
    {
        self::$_fieldJoinAliasList[static::class] = $alias;
    }
    
    
    /**
     * 清理join别名
     */
    public static function clearJoinAlias()
    {
        unset(self::$_fieldJoinAliasList[static::class]);
    }
    
    
    /**
     * 获取join查询别名
     * @param string $name 字段名
     * @return string
     */
    public static function getJoinAlias($name = null)
    {
        $alias = self::$_fieldJoinAliasList[static::class] ?? '';
        if ($name) {
            if ($alias) {
                return "{$alias}.{$name}";
            }
            
            return $name;
        }
        
        return $alias;
    }
    
    
    /**
     * @param $name
     * @param $arguments
     * @return $this
     * @throws MethodNotFoundException
     */
    public function __call($name, $arguments)
    {
        if (substr($name, 0, 3) == 'set') {
            $this->{Str::camel(substr($name, 3))} = $arguments[0];
            
            return $this;
        } elseif (substr($name, 0, 3) == 'get') {
            return $this->{Str::camel(substr($name, 3))};
        }
        
        throw new MethodNotFoundException($this, $name);
    }
    
    
    /**
     * 获取数据库插入的数据
     * @return array
     */
    public function getDBData()
    {
        $vars   = get_object_vars($this);
        $params = [];
        foreach ($vars as $key => $value) {
            // 下划线开头的被认为是私有变量，过滤
            // 为NULL类型则过滤
            if (substr($key, 0, 1) == '_' || is_null($value)) {
                continue;
            }
            
            $key = Str::snake($key);
            
            // 布尔类型转 1 0
            $value = is_bool($value) ? ($value ? 1 : 0) : $value;
            
            // 数组类型保留系统参数
            if (is_array($value)) {
                if (isset($value[0]) && $value[0] !== 'exp' || !isset($value[0])) {
                    $value = serialize($value);
                }
            } elseif (is_object($value)) {
                $value = serialize($value);
            }
            
            
            $params[$key] = $value;
        }
        
        return $params;
    }
    
    
    /**
     * 获取查询条件
     * @return array
     */
    public function getWhere()
    {
        $vars   = get_object_vars($this);
        $params = [];
        foreach ($vars as $key => $value) {
            // 为NULL类型则过滤
            if (is_null($value)) {
                continue;
            }
            
            $key = Str::snake($key);
            
            // 布尔类型转 1 0
            $value        = is_bool($value) ? ($value ? 1 : 0) : $value;
            $params[$key] = $value;
        }
        
        return $params;
    }
    
    
    public function toArray() : array
    {
        $vars   = get_object_vars($this);
        $params = [];
        foreach ($vars as $key => $value) {
            // 下划线开头的被认为是私有变量，过滤
            if (substr($key, 0, 1) == '_') {
                continue;
            }
            
            // 转换键
            $key = Str::snake($key);
            
            $params[$key] = $value;
        }
        
        return $params;
    }
    
    
    public function toJson(int $options = JSON_UNESCAPED_UNICODE) : string
    {
        return json_encode($this->toArray(), $options);
    }
    
    
    public function __toString()
    {
        return $this->toJson();
    }
    
    
    public function offsetExists($offset)
    {
        return isset($this->{Str::camel($offset)});
    }
    
    
    public function offsetGet($offset)
    {
        return $this->{Str::camel($offset)};
    }
    
    
    public function offsetSet($offset, $value)
    {
        $this->{Str::camel($offset)} = $value;
    }
    
    
    public function offsetUnset($offset)
    {
        $this->{Str::camel($offset)} = null;
    }
    
    
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}