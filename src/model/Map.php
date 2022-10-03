<?php
declare (strict_types = 1);

namespace BusyPHP\model;

use ArrayAccess;
use ArrayIterator;
use BusyPHP\helper\StringHelper;
use Closure;
use Exception;
use IteratorAggregate;
use JsonSerializable;
use think\contract\Arrayable;
use think\contract\Jsonable;
use Traversable;

/**
 * Map
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/8 下午上午10:18 Map.php $
 */
class Map implements Arrayable, Jsonable, ArrayAccess, JsonSerializable, IteratorAggregate
{
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
    final  public static function maker(Closure $maker)
    {
        static::$maker[] = $maker;
    }
    
    
    /**
     * 快速获取实例
     * @param array $data 要装入的数据
     * @return static
     */
    public static function init(array $data = []) : self
    {
        return new static($data);
    }
    
    
    /**
     * 构造函数
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->{StringHelper::camel($key)} = $value;
        }
        
        if (!empty(static::$maker)) {
            foreach (static::$maker as $maker) {
                call_user_func($maker, $this);
            }
        }
    }
    
    
    public function __get($name)
    {
        return $this->{StringHelper::camel($name)} ?? null;
    }
    
    
    public function __set($name, $value)
    {
        $this->{StringHelper::camel($name)} = $value;
    }
    
    
    /**
     * 获取值
     * @param string                     $key 键名
     * @param mixed                      $default 默认值
     * @param callable|callable[]|string $filter 过滤方式
     * @return mixed
     */
    public function get(string $key, $default = null, $filter = null)
    {
        $value  = $this->{StringHelper::camel($key)} ?? $default;
        $filter = !is_array($filter) ? explode(',', (string) $filter) : $filter;
        foreach ($filter as $item) {
            if (!$item || !function_exists($item)) {
                continue;
            }
            
            $value = call_user_func($item, $value);
        }
        
        return $value;
    }
    
    
    /**
     * 设置值
     * @param string $key 键名
     * @param mixed  $value 键值
     */
    public function set(string $key, $value)
    {
        $this->{StringHelper::camel($key)} = $value;
    }
    
    
    /**
     * 删除键
     * @param string ...$keys
     */
    public function remove(...$keys)
    {
        foreach ($keys as $key) {
            unset($this->{StringHelper::camel($key)});
        }
    }
    
    
    /**
     * 检测是否存在
     * @param string $key
     * @return bool
     */
    public function has(string $key) : bool
    {
        return isset($this->{StringHelper::camel($key)});
    }
    
    
    public function toArray() : array
    {
        return get_object_vars($this);
    }
    
    
    public function toJson(int $options = JSON_UNESCAPED_UNICODE) : string
    {
        return json_encode($this->toArray(), $options);
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
        return isset($this->{StringHelper::camel($offset)});
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
        return $this->{StringHelper::camel($offset)};
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
        $this->{StringHelper::camel($offset)} = $value;
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
        unset($this->{StringHelper::camel($offset)});
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
        return $this->toArray();
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
        return new ArrayIterator($this->toArray());
    }
    
    
    public function __toString() : string
    {
        return $this->toJson(JSON_UNESCAPED_UNICODE);
    }
}