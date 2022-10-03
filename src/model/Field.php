<?php
declare (strict_types = 1);

namespace BusyPHP\model;

use ArrayAccess;
use ArrayIterator;
use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\exception\MethodNotFoundException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\helper\StringHelper;
use Closure;
use Countable;
use Exception;
use IteratorAggregate;
use JsonSerializable;
use ReflectionProperty;
use RuntimeException;
use think\contract\Arrayable;
use think\contract\Jsonable;
use think\Db;
use think\db\Raw;
use Traversable;

/**
 * 模型字段基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/6 下午3:07 下午 Field.php $
 * @method void onParseAfter() 将数据转为对象后的后置方法
 */
class Field implements Arrayable, Jsonable, ArrayAccess, JsonSerializable, IteratorAggregate, Countable
{
    /** @var string 是否忽略字段 */
    public const ATTR_IGNORE = 'ignore';
    
    /** @var string 验证规则 */
    public const ATTR_VERIFY = 'verify';
    
    /** @var string 真实字段名称 */
    public const ATTR_FIELD = 'field';
    
    /** @var string 过滤方法 */
    public const ATTR_FILTER = 'filter';
    
    /** @var string 属性反射对象 */
    public const ATTR_PROPERTY = 'property';
    
    /** @var string toArray重命名 */
    public const ATTR_RENAME = 'rename';
    
    /**
     * 服务注入
     * @var Closure[]
     */
    protected static $maker = [];
    
    /**
     * Join别名
     * @var array
     */
    private static $joinAlias = [];
    
    /**
     * 子类属性的注释属性集合
     * @var array
     */
    private static $propertyAttrs = [];
    
    /**
     * 子类所有字段集合
     * @var array[][]
     */
    private static $fieldList = [];
    
    /**
     * 子类属性=>字段映射
     * @var array[][]
     */
    private static $propertyToFieldMap = [];
    
    /**
     * 子类属性=>字段重命名映射
     * @var array
     */
    private static $propertyToRenameMap = [];
    
    
    /**
     * 快速实例化
     * @return $this
     */
    public static function init() : self
    {
        return new static();
    }
    
    
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
     * 数据转对象
     * @param array $array 数据
     * @return $this
     */
    final public static function parse(array $array) : self
    {
        $obj = static::init();
        $map = array_flip($obj::getPropertyToFieldMap());
        foreach ($array as $field => $item) {
            // 通过字段获取属性，不存在则添加这个属性
            if (!isset($map[$field])) {
                $obj->{$field} = $item;
                continue;
            }
            
            self::setPropertyValue($obj, $map[$field], $item);
        }
        
        // 后置操作
        if (method_exists($obj, 'onParseAfter')) {
            $obj->onParseAfter();
        }
        
        return $obj;
    }
    
    
    /**
     * 通过属性名称强制转换值
     * @param Field  $field Field对象
     * @param string $property 属性
     * @param mixed  $value 值
     * @return mixed
     */
    public static function setPropertyValue(Field $field, string $property, $value)
    {
        // 函数过滤
        $attrs = static::getPropertyAttrs($property);
        if (!$attrs) {
            return $value;
        }
        
        $filters = (array) ($attrs[self::ATTR_FILTER] ?? []);
        foreach ($filters as $filter) {
            if (is_callable($filter)) {
                $value = call_user_func($filter, $value);
            }
        }
        
        // 强制转换
        switch ($attrs[ClassHelper::ATTR_VAR]) {
            case 'int':
                $value = (int) $value;
            break;
            case 'float':
                $value = (float) $value;
            break;
            case 'bool':
                $value = (bool) $value;
            break;
        }
        
        // 设置值
        ClassHelper::setPropertyValue($field, $attrs[self::ATTR_PROPERTY], $value);
        
        return $value;
    }
    
    
    /**
     * 复制Field为新的Field
     * @param Field         $needField 要复制的Field对象或子对象
     * @param Entity|string ...$excludes 排除字段
     * @return static
     */
    final public static function copyDBData(Field $needField, ...$excludes) : self
    {
        if (!$needField instanceof static) {
            throw new ClassNotExtendsException($needField, static::class);
        }
        
        $excludes = array_map(function($item) {
            if ($item instanceof Entity) {
                $item = $item->property();
            }
            
            return $item;
        }, ArrayHelper::flat($excludes));
        
        $data = static::init();
        $data::eachPropertyAttrs(function(string $field, ReflectionProperty $property) use ($data, $needField, $excludes) {
            // 排除属性
            if (in_array($property->getName(), $excludes)) {
                return;
            }
            
            // 获取值
            /** @var ReflectionProperty $needProperty */
            $needProperty = $needField::getPropertyAttrs($property->getName())[self::ATTR_PROPERTY] ?? null;
            if (!$needProperty) {
                return;
            }
            
            if (is_null($value = ClassHelper::getPropertyValue($needField, $needProperty))) {
                return;
            }
            
            // 设置值
            self::setPropertyValue($data, $property->getName(), $value);
        });
        
        return $data;
    }
    
    
    /**
     * 获取当前类属性的注释属性
     * @param string|null $property
     * @return array[]|array{var: string|mixed, name: string, field: string, verify: string|array, filter: string|array, ignore: bool, property: ReflectionProperty}|null
     */
    final public static function getPropertyAttrs(string $property = null) : ?array
    {
        if (!isset(self::$propertyAttrs[static::class]) || !isset(self::$propertyToFieldMap[static::class]) || !isset(self::$fieldList[static::class])) {
            $class     = ClassHelper::getReflectionClass(static::class);
            $data      = [];
            $useFields = [];
            $useNames  = [];
            $names     = [];
            $fieldList = [];
            $fieldMap  = [];
            $renameMap = [];
            foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE) as $item) {
                if ($item->isStatic()) {
                    continue;
                }
                
                $name    = $item->getName();
                $snake   = StringHelper::snake($name);
                $names[] = $name;
                $attr    = ClassHelper::extractDocAttrs($class, $name, null, $item->getDocComment(), [
                    self::ATTR_FIELD  => ClassHelper::CAST_STRING,
                    self::ATTR_FILTER => ClassHelper::CAST_STRING,
                    self::ATTR_VERIFY => ClassHelper::CAST_STRING,
                    self::ATTR_IGNORE => ClassHelper::CAST_BOOL,
                    self::ATTR_RENAME => ClassHelper::CAST_STRING
                ]);
                
                $attr[self::ATTR_PROPERTY] = $item;
                $attr[self::ATTR_FIELD]    = $attr[self::ATTR_FIELD] ?: $snake;
                $attr[self::ATTR_RENAME]   = $attr[self::ATTR_RENAME] ?: $snake;
                
                // 不忽略
                if (!$attr[self::ATTR_IGNORE]) {
                    $fieldList[]      = $attr[self::ATTR_FIELD];
                    $fieldMap[$name]  = $attr[self::ATTR_FIELD];
                    $renameMap[$name] = $attr[self::ATTR_RENAME];
                }
                
                // 指定了字段名称
                if ($attr[self::ATTR_FIELD] != $name) {
                    $useFields[$name] = $attr[self::ATTR_FIELD];
                }
                
                // 指定了重命名
                if ($attr[self::ATTR_RENAME] != $name) {
                    $useNames[$name] = $attr[self::ATTR_RENAME];
                }
                
                $data[$name] = $attr;
            }
            
            // 指定的字段名称不能和已有的属性一样
            foreach ($useFields as $name => $field) {
                if (in_array($field, $names)) {
                    throw new RuntimeException(sprintf('The comment "@field %s" of property "%s" in class "%s" cannot overwrite the existing property', $field, $name, static::class));
                }
            }
            
            // 指定的重命名称不能和已有的属性一样
            foreach ($useNames as $name => $field) {
                if (in_array($field, $names)) {
                    throw new RuntimeException(sprintf('The comment "@rename %s" of property "%s" in class "%s" cannot overwrite the existing property', $field, $name, static::class));
                }
            }
            
            self::$propertyToRenameMap[static::class] = $renameMap;
            self::$propertyToFieldMap[static::class]  = $fieldMap;
            self::$fieldList[static::class]           = $fieldList;
            self::$propertyAttrs[static::class]       = $data;
        }
        
        return ArrayHelper::getValueOrSelf(self::$propertyAttrs[static::class], $property);
    }
    
    
    /**
     * 递归当前类属性的注释属性
     * @param callable(string, ReflectionProperty, array):void $callback
     */
    final public static function eachPropertyAttrs(callable $callback)
    {
        foreach (static::getPropertyAttrs() as $item) {
            // 忽略
            if ($item[self::ATTR_IGNORE]) {
                continue;
            }
            
            call_user_func($callback, $item[self::ATTR_FIELD], $item[self::ATTR_PROPERTY], $item);
        }
    }
    
    
    /**
     * 获取所有字段
     * @param Entity|string|Entity[]|string[] ...$excludes 排除的字段
     * @return string[]
     */
    final public static function getFieldList(...$excludes) : array
    {
        static::getPropertyAttrs();
        
        $list = self::$fieldList[static::class];
        if (!$excludes) {
            return $list;
        }
        
        $excludes = array_map(function($item) {
            if ($item instanceof Entity) {
                return $item->field();
            }
            
            return $item;
        }, ArrayHelper::flat($excludes));
        
        $data = [];
        foreach ($list as $item) {
            if (in_array($item, $excludes)) {
                continue;
            }
            $data[] = $item;
        }
        
        return $data;
    }
    
    
    /**
     * 获取属性 => 字段映射关系或通过类属性名获取字段
     * @param string|null $property 属性名称
     * @return array|string
     */
    final public static function getPropertyToFieldMap(string $property = null)
    {
        static::getPropertyAttrs();
        
        return ArrayHelper::getValueOrSelf(self::$propertyToFieldMap[static::class], $property);
    }
    
    
    /**
     * 获取属性 => 重命名映射关系或通过类属性名获取重命名
     * @param string|null $property 属性名称
     * @return array|string
     */
    final public static function getPropertyToRenameMap(string $property = null)
    {
        static::getPropertyAttrs();
        
        return ArrayHelper::getValueOrSelf(self::$propertyToRenameMap[static::class], $property);
    }
    
    
    /**
     * 构建距离查询字段，计算出来距离单位为米
     * @param string|Entity $latField 纬度字段
     * @param string|Entity $lngField 经度字段
     * @param float         $lat 纬度值
     * @param float         $lng 经度值
     * @param string|Entity $alias 别名
     * @return string
     */
    final public static function buildDistanceField($latField, $lngField, float $lat, float $lng, $alias = 'distance') : string
    {
        $latField = (string) $latField;
        $lngField = (string) $lngField;
        $alias    = (string) $alias;
        
        return sprintf("round( 6378.138 * 2 * ASIN( SQRT( POW( SIN( ( %s * PI() / 180 - %s * PI() / 180 ) / 2 ), 2 ) + COS(%s * PI() / 180) * COS(%s * PI() / 180) * POW( SIN( ( %s * PI() / 180 - %s * PI() / 180 ) / 2 ), 2 ) ) ) * 1000) as %s", $lat, $latField, $lat, $latField, $lng, $lngField, $alias);
    }
    
    
    /**
     * 设置join别名，用完一定要清理 {@see Field::clearJoinAlias()}
     * @param string $alias
     */
    final public static function setJoinAlias($alias = null)
    {
        self::$joinAlias[static::class] = $alias;
    }
    
    
    /**
     * 清理join别名
     */
    final public static function clearJoinAlias()
    {
        unset(self::$joinAlias[static::class]);
    }
    
    
    /**
     * 获取join查询别名
     * @param string $name 字段名
     * @return string
     */
    final public static function getJoinAlias($name = null) : string
    {
        $alias = self::$joinAlias[static::class] ?? '';
        if ($name) {
            if ($alias) {
                return "$alias.$name";
            }
            
            return $name;
        }
        
        return $alias;
    }
    
    
    public static function __callStatic($name, $arguments)
    {
        // 静态方法名称存在属性中，则返回属性实体
        if ($field = self::getPropertyToFieldMap($name)) {
            $entity = new Entity($name, $field);
            
            // 取别名
            $alias = self::$joinAlias[static::class] ?? false;
            if ($alias) {
                if ($alias === true) {
                    $alias = basename(str_replace('\\', '/', static::class));
                }
                $entity->table($alias);
            }
            
            // 设置条件
            switch (count($arguments)) {
                case 1:
                    $entity->op('=');
                    $entity->value($arguments[0]);
                break;
                case 2:
                    $entity->op($arguments[0] ?: '=');
                    $entity->value($arguments[1]);
                break;
            }
            
            return $entity;
        }
        
        throw new MethodNotFoundException(static::class, $name, 'static');
    }
    
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        if (!empty(static::$maker)) {
            foreach (static::$maker as $maker) {
                call_user_func($maker, $this);
            }
        }
    }
    
    
    public function __call($name, $arguments)
    {
        $lower = strtolower($name);
        
        switch (true) {
            // setField
            case substr($lower, 0, 3) == 'set':
                $property = StringHelper::camel(substr($name, 3));
                $value    = $arguments[0] ?? null;
                $attrs    = static::getPropertyAttrs($property);
                if (!$attrs) {
                    throw new RuntimeException(sprintf('The property "%s" of the class "%s" does not exist', $property, static::class));
                }
                
                // 设置值
                self::setPropertyValue($this, $property, $value);
                
                return $this;
            
            // getField
            case substr($name, 0, 3) == 'get':
                return $this->{StringHelper::camel(substr($name, 3))};
        }
        
        throw new MethodNotFoundException($this, $name);
    }
    
    
    /**
     * 获取可以执行 {@see Db::insert()} {@see Db::update()} {@see Db::save()} {@see Db::data()} 的数据
     * @return array
     */
    public function getDBData() : array
    {
        $data = [];
        self::eachPropertyAttrs(function(string $field, ReflectionProperty $property, array $item) use (&$data) {
            // 过滤null
            if (is_null($value = ClassHelper::getPropertyValue($this, $property))) {
                return;
            }
            
            switch (true) {
                // bool转int
                case is_bool($value):
                    $value = (int) $value;
                break;
                
                // 是数组且不含exp参数
                case is_array($value) && ((isset($value[0]) && $value[0] !== 'exp') || !isset($value[0])):
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                break;
                
                // 是对象
                case is_object($value):
                    if ($value instanceof Entity) {
                        $value = new Raw($value->build() . $value->op() . $value->value());
                    } elseif ($value instanceof JsonSerializable) {
                        $value = json_encode($value->jsonSerialize(), JSON_UNESCAPED_UNICODE);
                    } elseif ($value instanceof Jsonable) {
                        $value = $value->toJson(JSON_UNESCAPED_UNICODE);
                    } elseif ($value instanceof Arrayable) {
                        $value = json_encode($value->toArray(), JSON_UNESCAPED_UNICODE);
                    } elseif ($value instanceof ArrayAccess) {
                        $value = json_encode($value);
                    } else {
                        if (!$value instanceof Raw) {
                            $value = serialize($value);
                        }
                    }
                break;
                
                // 根据字段类型强制转换
                default:
                    switch ($item[ClassHelper::ATTR_VAR]) {
                        case 'int':
                            $value = (int) $value;
                        break;
                        case 'float':
                            $value = (float) $value;
                        break;
                        default:
                            $value = (string) $value;
                    }
            }
            
            $data[$field] = $value;
        });
        
        return $data;
    }
    
    
    public function toArray() : array
    {
        $vars = get_object_vars($this);
        foreach (ClassHelper::getReflectionClass($this)->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            if ($property->isStatic()) {
                continue;
            }
            
            $vars[$property->getName()] = ClassHelper::getPropertyValue($this, $property);
        }
        
        $array = [];
        foreach ($vars as $property => $item) {
            if ($rename = self::getPropertyToRenameMap($property)) {
                $array[$rename] = $item;
            } else {
                $array[$property] = $item;
            }
        }
        
        return $array;
    }
    
    
    public function toJson(int $options = JSON_UNESCAPED_UNICODE) : string
    {
        return json_encode($this->toArray(), $options);
    }
    
    
    public function __toString()
    {
        return $this->toJson();
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetExists($offset) : bool
    {
        return isset($this->{StringHelper::camel($offset)});
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->{StringHelper::camel($offset)};
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->{StringHelper::camel($offset)} = $value;
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->{StringHelper::camel($offset)} = null;
    }
    
    
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
        return count($this->toArray());
    }
}