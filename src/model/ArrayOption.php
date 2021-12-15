<?php

namespace BusyPHP\model;

use ArrayAccess;
use ArrayIterator;
use BusyPHP\helper\ArrayHelper;
use Countable;
use Exception;
use IteratorAggregate;
use JsonSerializable;
use think\contract\Arrayable;
use think\contract\Jsonable;
use Traversable;

/**
 * 数组选项对象
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/19 下午上午11:17 ArrayOption.php $
 */
class ArrayOption implements ArrayAccess, Countable, Jsonable, JsonSerializable, IteratorAggregate, Arrayable
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
    public static function init(array $options) : self
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
    
    
    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->options);
    }
    
    
    public function toJson(int $options = JSON_UNESCAPED_UNICODE) : string
    {
        return json_encode($this->options, $options);
    }
    
    
    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4
     */
    public function jsonSerialize()
    {
        return $this->options;
    }
    
    
    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @throws Exception on failure.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->options);
    }
    
    
    public function __toString()
    {
        return $this->toJson();
    }
    
    
    public function toArray() : array
    {
        return $this->options;
    }
}