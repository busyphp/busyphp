<?php
declare(strict_types = 1);

namespace BusyPHP\model;

use ArrayAccess;
use ArrayIterator;
use BusyPHP\helper\ArrayHelper;
use Closure;
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
     * 服务注入
     * @var Closure[]
     */
    protected static $maker = [];
    
    /**
     * @var array
     */
    private $options;
    
    
    /**
     * 实例化
     * @param array $options
     * @return static
     */
    public static function init(array $options = []) : self
    {
        return new static($options);
    }
    
    
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
     * ArrayOption constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
        
        if (!empty(static::$maker)) {
            foreach (static::$maker as $maker) {
                call_user_func($maker, $this);
            }
        }
    }
    
    
    /**
     * 获取值
     * @param string                     $key 键，支持.下级访问
     * @param mixed                      $default 默认值
     * @param callable|string|callable[] $filter 数据过滤方法
     * @return mixed
     */
    public function get(string $key, $default = null, $filter = null)
    {
        $value = ArrayHelper::get($this->options, $key, $default);
        
        if ($filter) {
            $filter = is_callable($filter) ? [$filter] : $filter;
            $filter = is_string($filter) ? explode(',', $filter) : $filter;
            foreach ($filter as $item) {
                if (is_callable($item)) {
                    $value = call_user_func($item, $value);
                }
            }
        }
        
        return $value;
    }
    
    
    /**
     * 获取值并删除
     * @param string                     $key 键，支持.下级访问
     * @param mixed                      $default 默认值
     * @param callable|string|callable[] $filter 数据过滤方法
     * @return mixed
     */
    public function pull(string $key, $default = null, $filter = null)
    {
        $value = $this->get($key, $default, $filter);
        
        $this->delete($key);
        
        return $value;
    }
    
    
    /**
     * 删除键
     * @param ...$key
     * @return $this
     */
    public function delete(...$key) : self
    {
        ArrayHelper::forget($this->options, $key);
        
        return $this;
    }
    
    
    /**
     * 设置值
     * @param string $key 键，支持.下级访问
     * @param mixed  $value 值
     * @return $this
     */
    public function set(string $key, $value) : self
    {
        ArrayHelper::set($this->options, $key, $value);
        
        return $this;
    }
    
    
    /**
     * 检测指定键是否存在
     * @param string $key 键，支持.下级访问
     * @return bool
     */
    public function has(string $key) : bool
    {
        return ArrayHelper::has($this->options, $key);
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
    #[\ReturnTypeWillChange]
    public function offsetExists($offset) : bool
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
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
    public function count() : int
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
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
    public function getIterator() : iterable
    {
        return new ArrayIterator($this->options);
    }
    
    
    /**
     * 生成 HTTP Query
     * @param int $encodingType
     * @return string
     */
    public function toHttpQuery(int $encodingType = PHP_QUERY_RFC3986) : string
    {
        return http_build_query($this->options, '', '&', $encodingType);
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