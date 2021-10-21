<?php

namespace BusyPHP\model;

use BusyPHP\helper\ArrayHelper;

/**
 * 选项对象
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/19 下午上午11:17 Options.php $
 */
class Options implements \ArrayAccess
{
    /**
     * @var array
     */
    private $options;
    
    
    /**
     * 实例化
     * @param array $options
     * @return static
     */
    public static function init(array $options) : Options
    {
        return new static($options);
    }
    
    
    public function __construct(array $options)
    {
        $this->options = $options;
    }
    
    
    /**
     * 获取值
     * @param string $key 键，支持.下级访问
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return ArrayHelper::get($this->options, $key, $default);
    }
    
    
    /**
     * 设置值
     * @param string $key 键，支持.下级访问
     * @param mixed  $value 值
     * @return array
     */
    public function set(string $key, $value) : array
    {
        return ArrayHelper::set($this->options, $key, $value);
    }
    
    
    public function __get($name)
    {
        return $this->options[$name] ?? null;
    }
    
    
    public function __set($name, $value)
    {
        $this->options[$name] = $value;
    }
    
    
    /**
     * 获取配置
     * @return array
     */
    public function options() : array
    {
        return $this->options;
    }
    
    
    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->options[$offset]);
    }
    
    
    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->options[$offset] ?? null;
    }
    
    
    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->options[$offset] = $value;
    }
    
    
    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->options[$offset]);
    }
}