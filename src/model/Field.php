<?php

namespace BusyPHP\model;

use BusyPHP\exception\AppException;

/**
 * 模型字段基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/6 下午3:07 下午 Field.php $
 */
abstract class Field
{
    /** @var string where字符串查询字段 */
    public $_string = null;
    
    
    /**
     * 快速实例化
     * @return $this
     */
    public static function init()
    {
        $className = get_called_class();
        
        return new $className();
    }
    
    
    /**
     * 解析器
     * @param array $array 数据
     * @param bool  $isParseName 是否将下划线名称转成驼峰格式
     * @return $this
     */
    public static function parse($array, $isParseName = true)
    {
        $obj = static::init();
        foreach ($array as $key => $value) {
            $name       = $isParseName ? lcfirst(parse_name($key, 1)) : $key;
            $obj->$name = $value;
        }
        
        // 后置操作
        $obj->_parseAfter();
        
        return $obj;
    }
    
    
    public function __get($name)
    {
        return $this->$name;
    }
    
    
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
    
    
    /**
     * @param $name
     * @param $arguments
     * @return $this
     * @throws AppException
     */
    public function __call($name, $arguments)
    {
        if (substr($name, 0, 3) == 'set') {
            $attr        = lcfirst(substr($name, 3));
            $this->$attr = $arguments[0];
            
            return $this;
        } elseif (substr($name, 0, 3) == 'get') {
            $attr = lcfirst(substr($name, 3));
            
            return $this->$attr;
        }
        
        throw new AppException("method {$name} is not Found.");
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
            
            $key = parse_name($key);
            
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
            
            // 转换键
            if ($key !== '_string') {
                $key = parse_name($key);
            }
            
            // 布尔类型转 1 0
            $value        = is_bool($value) ? ($value ? 1 : 0) : $value;
            $params[$key] = $value;
        }
        
        return $params;
    }
    
    
    /**
     * 获取数据
     * @return array
     */
    public function getData()
    {
        $vars   = get_object_vars($this);
        $params = [];
        foreach ($vars as $key => $value) {
            // 下划线开头的被认为是私有变量，过滤
            // 为NULL类型则过滤
            if (substr($key, 0, 1) == '_' || is_null($value)) {
                continue;
            }
            
            // 转换键
            $key = parse_name($key);
            
            $params[$key] = $value;
        }
        
        return $params;
    }
    
    
    /**
     * 解析变量的后置操作
     */
    protected function _parseAfter()
    {
    }
}