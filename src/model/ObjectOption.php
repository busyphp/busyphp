<?php
declare(strict_types = 1);

namespace BusyPHP\model;

use ArrayObject;
use Countable;
use Exception;
use IteratorAggregate;
use JsonSerializable;
use think\contract\Arrayable;
use think\contract\Jsonable;
use Traversable;

/**
 * 键值对选项类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/27 下午下午9:47 ObjectOption.php $
 */
class ObjectOption implements Countable, Arrayable, Jsonable, JsonSerializable, IteratorAggregate
{
    public function toArray() : array
    {
        return get_object_vars($this);
    }
    
    
    public function toJson(int $options = JSON_UNESCAPED_UNICODE) : string
    {
        return json_encode($this->toArray(), $options);
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
        return $this->toArray();
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
        return count($this->toArray());
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
        return new ArrayObject($this->toArray());
    }
    
    
    public function __toString()
    {
        return $this->toJson();
    }
    
}