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
use BusyPHP\helper\TransHelper;
use BusyPHP\interfaces\FieldObtainDataInterface;
use BusyPHP\interfaces\FieldSetValueInterface;
use BusyPHP\Model;
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
use think\Validate;
use Traversable;

/**
 * 模型字段基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/6 下午3:07 下午 Field.php $
 */
class Field implements Arrayable, Jsonable, ArrayAccess, JsonSerializable, IteratorAggregate, Countable
{
    // +----------------------------------------------------
    // + 注释属性
    // +----------------------------------------------------
    /** @var string 字段注释属性-定义 {@see Model::validate()} 的校验参数，支持多个，支持按"|"分割校验规则 */
    public const ATTR_VALIDATE = 'busy-validate';
    
    /** @var string 字段注释属性-定义真实字段名称，在数据库操作中生效 */
    public const ATTR_FIELD = 'busy-field';
    
    /** @var string 字段注释属性-设置值时，使用的过滤方法，支持多个，支持按逗号分割过滤方法 */
    public const ATTR_FILTER = 'busy-filter';
    
    /** @var string 字段注释属性-设置值时如果是数组类型，定义数组的转换方式 */
    public const ATTR_ARRAY = 'busy-array';
    
    /** @var string 字段注释属性-设置值时是否不按照属性类型强制转换 */
    public const ATTR_NO_CAST = 'busy-no-cast';
    
    /** @var string 字段注释属性-使用 {@see Field::toArray()} 方法输出时，重命名该属性的名称 */
    public const ATTR_RENAME = 'busy-rename';
    
    /** @var string 字段注释属性-是否忽略该属性，使用 {@see Field::toArray()} {@see Field::obtain()} {@see Field::copyData()} 时有效，一般用于内部使用 */
    public const ATTR_IGNORE = 'busy-ignore';
    
    // +----------------------------------------------------
    // + 内部属性
    // +----------------------------------------------------
    /** @var string 属性反射对象 */
    public const DEFINE_PROPERTY = 'property';
    
    /** @var string 属性的权限 */
    public const DEFINE_ACCESS = 'access';
    
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
     * @var array[][]
     */
    private static $propertyAttrs = [];
    
    /**
     * 子类所有属性集合
     * @var string[][]
     */
    private static $propertyList = [];
    
    /**
     * 子类属性=>字段映射
     * @var string[][]
     */
    private static $propertyToFieldMap = [];
    
    /**
     * 字段=>子类属性映射
     * @var string[][]
     */
    private static $fieldToPropertyMap = [];
    
    /**
     * 子类属性=>字段重命名映射
     * @var string[][]
     */
    private static $propertyToRenameMap = [];
    
    /**
     * 重命名=>子类属性映射
     * @var string[][]
     */
    private static $renameToPropertyMap = [];
    
    /**
     * 自定义名称=>子类属性映射
     * @var string[][]
     */
    private static $useNameToPropertyMap = [];
    
    /**
     * 忽略的子类属性集合
     * @var string[][]
     */
    private static $ignorePropertyList = [];
    
    /**
     * 输入名称 => 子类属性名称映射
     * @var string[][]
     */
    public static $propertyNameMap = [];
    
    /**
     * 私有变量前缀
     * @var string
     */
    private static $privateVarPrefix = '__private__';
    
    /**
     * 选项
     * @var array
     */
    private $__private__options = [];
    
    
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
    public static function maker(Closure $maker)
    {
        static::$maker[] = $maker;
    }
    
    
    /**
     * 数据转对象
     * @param array $array 数据
     * @return $this
     */
    public static function parse(array $array) : self
    {
        $obj = static::init();
        foreach ($array as $name => $value) {
            if ($property = self::getPropertyName($name)) {
                self::setPropertyValue($obj, $property, $value);
            } else {
                $obj->{$name} = $value;
            }
        }
        
        // 后置操作
        $obj->onParseAfter();
        
        return $obj;
    }
    
    
    /**
     * 搜索子类属性名称
     * @param string $name
     * @return string|null
     */
    public static function getPropertyName(string $name) : ?string
    {
        if (!isset(self::$propertyNameMap[static::class][$name])) {
            switch (true) {
                // 通过字段名/重命名/自定义 取属性
                case null !== $property = self::getFieldToPropertyMap($name):
                case null !== $property = self::getRenameToPropertyMap($name):
                case null !== $property = self::getUseNameToPropertyMap($name):
                    self::$propertyNameMap[static::class][$name] = $property;
                    
                    return $property;
                
                // 通过属性取字段
                case null !== self::getPropertyToFieldMap($name):
                    self::$propertyNameMap[static::class][$name] = $name;
                    
                    return $name;
                
                default:
                    self::$propertyNameMap[static::class][$name] = true;
                    
                    return null;
            }
        }
        
        return self::$propertyNameMap[static::class][$name] === true ? null : self::$propertyNameMap[static::class][$name];
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
        $attrs = static::getPropertyAttrs($property);
        if (!$attrs) {
            throw new RuntimeException(sprintf('Property "%s" does not exist in class "%s"', $property, get_class($field)));
        }
        
        // 为空，直接设置
        if (is_null($value) || $value instanceof Entity || $value instanceof Raw) {
            goto end;
        }
        
        // 函数过滤
        foreach (ArrayHelper::flat((array) ($attrs[self::ATTR_FILTER] ?? []), ',') as $filter) {
            if (is_callable($filter)) {
                $value = call_user_func($filter, $value);
            }
        }
        
        // 执行处理
        if ($field instanceof FieldSetValueInterface) {
            $value = $field->onSetValue($attrs[self::ATTR_FIELD], $property, $attrs, $value);
        }
        
        // 不强制转换
        if ($attrs[self::ATTR_NO_CAST]) {
            goto end;
        }
        
        // 强制转换
        switch ($varType = $attrs[ClassHelper::ATTR_VAR]) {
            case 'int':
                $value = (int) $value;
            break;
            case 'float':
                $value = (float) $value;
            break;
            case 'bool':
                $value = (bool) $value;
            break;
            case 'array':
                if (!is_array($value)) {
                    $value = (string) $value;
                    $type  = $attrs[self::ATTR_ARRAY];
                    
                    // 切割
                    if (is_array($type)) {
                        [$type, $fill] = $type;
                        if ($fill) {
                            $value = trim($value, $type);
                        }
                        $value = explode($type, $value);
                        $value = array_map('trim', $value);
                        $value = array_filter($value, function($item) {
                            $item = trim($item);
                            if ($item === '') {
                                return false;
                            }
                            
                            return true;
                        });
                    } else {
                        $type = strtolower($type);
                        switch (true) {
                            // json格式自动解析
                            case $type == 'json':
                            case 0 === strpos($value, '[') || 0 == strpos($value, '{'):
                                $value = json_decode($value, true);
                            break;
                            
                            // serialize格式自动解析
                            case $type == 'serialize':
                            case 0 === strpos($value, 'a:'):
                                $value = unserialize($value);
                            break;
                        }
                    }
                    
                    $value = (array) $value;
                }
            break;
            default:
                // 强制转为object
                $isClass     = is_string($varType) && 0 === strpos($varType, '\\') && class_exists($varType);
                $isObject    = $varType === 'object';
                $isSerialize = is_string($value) && 0 === strpos($value, 'O:');
                if ($isSerialize && ($isClass || $isObject)) {
                    $value = unserialize($value) ?: null;
                }
        }
        
        // 设置值
        end:
        if ($attrs[self::DEFINE_ACCESS] == ReflectionProperty::IS_PRIVATE) {
            ClassHelper::setPropertyValue($field, $attrs[self::DEFINE_PROPERTY], $value);
        } else {
            $field->{$property} = $value;
        }
        
        return $value;
    }
    
    
    /**
     * 复制Field为新的Field
     * @param Field         $needField 要复制的Field对象或类名
     * @param Entity|string ...$excludes 排除字段
     * @return static
     */
    public static function copyData(Field $needField, ...$excludes) : self
    {
        if (!$needField instanceof static) {
            throw new ClassNotExtendsException($needField, static::class);
        }
        
        $excludes = array_map(function($item) {
            if ($item instanceof Entity) {
                $item = $item->name();
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
            $needProperty = $needField::getPropertyAttrs($property->getName())[self::DEFINE_PROPERTY] ?? null;
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
    public static function getPropertyAttrs(string $property = null) : ?array
    {
        if (!isset(self::$propertyAttrs[static::class]) || !isset(self::$propertyToFieldMap[static::class])) {
            $class        = ClassHelper::getReflectionClass(static::class);
            $data         = [];
            $testFields   = [];
            $testRenames  = [];
            $names        = [];
            $fields       = [];
            $fieldMap     = [];
            $renames      = [];
            $renameMap    = [];
            $propertyList = [];
            $ignores      = [];
            foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE) as $item) {
                $name = $item->getName();
                if ($item->isStatic() || 0 === strpos($name, self::$privateVarPrefix)) {
                    continue;
                }
                
                $access = ReflectionProperty::IS_PUBLIC;
                if ($item->isProtected()) {
                    $access = ReflectionProperty::IS_PROTECTED;
                } elseif ($item->isPrivate()) {
                    $access = ReflectionProperty::IS_PRIVATE;
                }
                
                $snake   = StringHelper::snake($name);
                $names[] = $name;
                $attr    = ClassHelper::extractDocAttrs($class, $name, null, $item->getDocComment(), [
                    self::ATTR_FIELD    => ClassHelper::CAST_STRING,
                    self::ATTR_FILTER   => ClassHelper::CAST_STRING,
                    self::ATTR_VALIDATE => ClassHelper::CAST_STRING,
                    self::ATTR_IGNORE   => ClassHelper::CAST_BOOL,
                    self::ATTR_NO_CAST  => ClassHelper::CAST_BOOL,
                    self::ATTR_RENAME   => ClassHelper::CAST_STRING,
                    self::ATTR_ARRAY    => ClassHelper::CAST_STRING
                ]);
                
                $attr[self::DEFINE_PROPERTY] = $item;
                $attr[self::DEFINE_ACCESS]   = $access;
                $attr[self::ATTR_FIELD]      = $attr[self::ATTR_FIELD] ?: $snake;
                $attr[self::ATTR_RENAME]     = $attr[self::ATTR_RENAME] ?: $snake;
                
                // 检测rename重复
                if (in_array($attr[self::ATTR_RENAME], $renames)) {
                    throw new RuntimeException(sprintf('The comment "@%s %s" of property "%s" in class "%s" can\'t repeat', self::ATTR_RENAME, $attr[self::ATTR_RENAME], $name, static::class));
                }
                // 检测field重复
                if (in_array($attr[self::ATTR_FIELD], $fields)) {
                    throw new RuntimeException(sprintf('The comment "@%s %s" of property "%s" in class "%s" can\'t repeat', self::ATTR_FIELD, $attr[self::ATTR_FIELD], $name, static::class));
                }
                
                $renames[]        = $attr[self::ATTR_RENAME];
                $fields[]         = $attr[self::ATTR_FIELD];
                $propertyList[]   = $name;
                $fieldMap[$name]  = $attr[self::ATTR_FIELD];
                $renameMap[$name] = $attr[self::ATTR_RENAME];
                
                // 忽略属性
                $attr[self::ATTR_IGNORE] = is_array($attr[self::ATTR_IGNORE]) ? end($attr[self::ATTR_IGNORE]) : $attr[self::ATTR_IGNORE];
                if ($attr[self::ATTR_IGNORE]) {
                    $ignores[] = $name;
                }
                
                // 解析数组转换方式
                $attr[self::ATTR_ARRAY] = is_array($attr[self::ATTR_ARRAY]) ? end($attr[self::ATTR_ARRAY]) : $attr[self::ATTR_ARRAY];
                if ($attr[self::ATTR_ARRAY] && preg_match('/^["\'](.+)["\']\s*(.*)$/i', $attr[self::ATTR_ARRAY], $match)) {
                    $attr[self::ATTR_ARRAY] = [$match[1], TransHelper::toBool($match[2])];
                }
                
                // 指定了字段名称
                if ($attr[self::ATTR_FIELD] != $name) {
                    $testFields[$name] = $attr[self::ATTR_FIELD];
                }
                
                // 指定了重命名
                if ($attr[self::ATTR_RENAME] != $name) {
                    $testRenames[$name] = $attr[self::ATTR_RENAME];
                }
                
                $data[$name] = $attr;
            }
            
            // 指定的字段名称不能和已有的属性一样
            foreach ($testFields as $name => $field) {
                if (in_array($field, $names)) {
                    throw new RuntimeException(sprintf('The comment "@%s %s" of property "%s" in class "%s" cannot overwrite the existing property', self::ATTR_FIELD, $field, $name, static::class));
                }
            }
            
            // 指定的重命名称不能和已有的属性一样
            foreach ($testRenames as $name => $field) {
                if (in_array($field, $names)) {
                    throw new RuntimeException(sprintf('The comment "@%s %s" of property "%s" in class "%s" cannot overwrite the existing property', self::ATTR_RENAME, $field, $name, static::class));
                }
            }
            
            unset($fields, $renames, $testRenames, $testFields);
            self::$propertyToRenameMap[static::class] = $renameMap;
            self::$propertyToFieldMap[static::class]  = $fieldMap;
            self::$propertyList[static::class]        = $propertyList;
            self::$propertyAttrs[static::class]       = $data;
            self::$ignorePropertyList[static::class]  = $ignores;
        }
        
        return ArrayHelper::getValueOrSelf(self::$propertyAttrs[static::class], $property);
    }
    
    
    /**
     * 递归当前类属性的注释属性
     * @param callable(string, ReflectionProperty, array):void $callback
     */
    public static function eachPropertyAttrs(callable $callback)
    {
        foreach (static::getPropertyAttrs() as $item) {
            // 忽略
            if ($item[self::ATTR_IGNORE]) {
                continue;
            }
            
            call_user_func($callback, $item[self::ATTR_FIELD], $item[self::DEFINE_PROPERTY], $item);
        }
    }
    
    
    /**
     * 获取所有属性集合
     * @return string[]
     */
    public static function getPropertyList() : array
    {
        static::getPropertyAttrs();
        
        return self::$propertyList[static::class];
    }
    
    
    /**
     * 获取忽略的属性集合
     * @return string[]
     */
    public static function getIgnorePropertyList() : array
    {
        static::getPropertyAttrs();
        
        return self::$ignorePropertyList[static::class];
    }
    
    
    /**
     * 获取所有字段
     * @param Entity|string|Entity[]|string[] ...$excludes 排除的字段
     * @return string[]
     */
    public static function getFieldList(...$excludes) : array
    {
        $excludes = array_map(function($item) {
            if ($item instanceof Entity) {
                return $item->field();
            }
            
            return $item;
        }, ArrayHelper::flat($excludes));
        
        $data = [];
        foreach (self::getPropertyToFieldMap() as $property => $field) {
            if (in_array($property, self::getIgnorePropertyList()) || in_array($field, $excludes)) {
                continue;
            }
            $data[] = $field;
        }
        
        return $data;
    }
    
    
    /**
     * 获取子类属性 => 字段映射关系
     * @param string|null $property 属性名称
     * @return array|string
     */
    public static function getPropertyToFieldMap(string $property = null)
    {
        static::getPropertyAttrs();
        
        return ArrayHelper::getValueOrSelf(self::$propertyToFieldMap[static::class], $property);
    }
    
    
    /**
     * 获取字段 => 子类属性映射关系
     * @param string|null $field 字段名称
     * @return array|string
     */
    public static function getFieldToPropertyMap(string $field = null)
    {
        if (!isset(self::$fieldToPropertyMap[static::class])) {
            self::$fieldToPropertyMap[static::class] = array_flip(self::getPropertyToFieldMap());
        }
        
        return ArrayHelper::getValueOrSelf(self::$fieldToPropertyMap[static::class], $field);
    }
    
    
    /**
     * 获取子类属性 => 重命名映射关系
     * @param string|null $property 属性名称
     * @return array|string
     */
    public static function getPropertyToRenameMap(string $property = null)
    {
        static::getPropertyAttrs();
        
        return ArrayHelper::getValueOrSelf(self::$propertyToRenameMap[static::class], $property);
    }
    
    
    /**
     * 获取重命名 => 子类属性映射关系
     * @param string|null $rename 重命名
     * @return array|string
     */
    public static function getRenameToPropertyMap(string $rename = null)
    {
        if (!isset(self::$renameToPropertyMap[static::class])) {
            self::$renameToPropertyMap[static::class] = array_flip(self::getPropertyToRenameMap());
        }
        
        return ArrayHelper::getValueOrSelf(self::$renameToPropertyMap[static::class], $rename);
    }
    
    
    /**
     * 注册自定义名称 => 子类属性映射关系
     * @param array $map
     */
    public static function registerUseNameToPropertyMap(array $map) : void
    {
        foreach ($map as $name => $property) {
            if ($property instanceof Entity) {
                $property = $property->name();
            }
            
            $map[$name] = (string) $property;
        }
        
        self::$useNameToPropertyMap[static::class] = $map + (self::$useNameToPropertyMap[static::class] ?? []);
    }
    
    
    /**
     * 获取自定义名称 => 子类属性映射关系
     * @param string|null $useName
     * @return array|string
     */
    public static function getUseNameToPropertyMap(string $useName = null)
    {
        return ArrayHelper::getValueOrSelf(self::$useNameToPropertyMap[static::class] ?? [], $useName);
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
    public static function buildDistanceField($latField, $lngField, float $lat, float $lng, $alias = 'distance') : string
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
    public static function setJoinAlias($alias = null)
    {
        self::$joinAlias[static::class] = $alias;
    }
    
    
    /**
     * 清理join别名
     */
    public static function clearJoinAlias()
    {
        unset(self::$joinAlias[static::class]);
    }
    
    
    /**
     * 获取join查询别名
     * @param string $name 字段名
     * @return string
     */
    public static function getJoinAlias($name = null) : string
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
        $lower  = strtolower($name);
        $length = strlen($lower);
        $prefix = substr($lower, 0, 3);
        
        // set get has
        if (in_array($prefix, ['set', 'get', 'has']) && $length > 3) {
            $property = StringHelper::camel(substr($name, 3));
            $attrs    = static::getPropertyAttrs($property);
            if (!$attrs) {
                throw new RuntimeException(sprintf('The property "%s" of the class "%s" does not exist', $property, static::class));
            }
            
            // setField
            if ($prefix == 'set') {
                // 设置值
                self::setPropertyValue($this, $property, $arguments[0] ?? null);
                
                return $this;
            }
            
            //
            // getField
            elseif ($prefix == 'get') {
                if ($attrs[self::DEFINE_ACCESS] == ReflectionProperty::IS_PRIVATE) {
                    return ClassHelper::getPropertyValue($this, $attrs[self::DEFINE_PROPERTY]);
                } else {
                    return $this->{$property};
                }
            }
            
            //
            // hasField
            else {
                if ($attrs[self::DEFINE_ACCESS] == ReflectionProperty::IS_PRIVATE) {
                    return ClassHelper::getPropertyValue($this, $attrs[self::DEFINE_PROPERTY]) !== null;
                } else {
                    return isset($this->{$property});
                }
            }
        }
        
        // 反射静态实体方法
        if (in_array($name, self::getPropertyList())) {
            return self::__callStatic($name, $arguments);
        }
        
        throw new MethodNotFoundException($this, $name);
    }
    
    
    /**
     * 将数据转为对象后的后置方法
     * @see Field::parse()
     */
    protected function onParseAfter()
    {
    }
    
    
    /**
     * 设置限制的属性
     * @param bool                            $exclude 是否排除
     * @param Entity[]|Entity|string[]|string ...$property 属性，注意非字段
     */
    private function setLimitProperty(bool $exclude, ...$property) : array
    {
        $this->__private__options['limit_exclude']  = $exclude;
        $this->__private__options['limit_property'] = array_map(function($item) {
            if ($item instanceof Entity) {
                return $item->name();
            }
            
            return (string) $item;
        }, ArrayHelper::flat($property));
        
        return $this->__private__options['limit_property'];
    }
    
    
    /**
     * 排除属性，执行 {@see Field::obtain()} {@see Field::toArray()} 时有效，与 {@see Field::retain()} 互斥
     * @param Validate|Entity|string          $property 传入数据校验对象或要排除的属性
     * @param Entity[]|Entity|string[]|string ...$propertyList 要排除的属性，注意非字段
     * @return $this
     */
    public function exclude($property, ...$propertyList) : self
    {
        if (!$property instanceof Validate) {
            $propertyList = array_merge($propertyList, [$property]);
        }
        
        $list = $this->setLimitProperty(true, ...$propertyList);
        if ($list && $property instanceof Validate) {
            foreach ($list as $item) {
                $property->remove($item, true);
            }
        }
        
        return $this;
    }
    
    
    /**
     * 保留属性，执行 {@see Field::obtain()} {@see Field::toArray()} 时有效，与 {@see Field::exclude()} 互斥
     * @param Validate|Entity|string          $property 传入数据校验对象或要排除的属性
     * @param Entity[]|Entity|string[]|string ...$propertyList 要保留的属性，注意非字段
     * @return $this
     */
    public function retain($property, ...$propertyList) : self
    {
        if (!$property instanceof Validate) {
            $propertyList = array_merge($propertyList, [$property]);
        }
        
        $list = $this->setLimitProperty(false, ...$propertyList);
        if ($list && $property instanceof Validate) {
            $property->only(...$list)->sort(...$list);
        }
        
        return $this;
    }
    
    
    /**
     * 使用自定义注释属性输出，以执行 {@see Field::toArray()}
     * @param string $attr 自定义属性，默认为 "@busy-use-safe"
     * @return $this
     */
    public function use(string $attr = 'safe') : self
    {
        $this->__private__options['use'] = $attr ? 'busy-use-' . $attr : '';
        
        return $this;
    }
    
    
    /**
     * 重置由 {@see Field::use()} {@see Field::retain()} {@see Field::exclude()} 设置的条件
     * @return $this
     */
    public function reset() : self
    {
        unset($this->__private__options['limit_property']);
        unset($this->__private__options['limit_exclude']);
        unset($this->__private__options['use']);
    }
    
    
    /**
     * 获取数据，以执行 {@see Db::insert()} {@see Db::update()} {@see Db::save()} {@see Db::data()}
     * @return array
     */
    public function obtain() : array
    {
        $limitProperty = $this->__private__options['limit_property'] ?? [];
        $limitExclude  = $this->__private__options['limit_exclude'] ?? true;
        
        $data = [];
        self::eachPropertyAttrs(function(string $field, ReflectionProperty $property, array $attrs) use (&$data, $limitProperty, $limitExclude) {
            if ($limitExclude) {
                if (in_array($property->getName(), $limitProperty)) {
                    return;
                }
            } else {
                if (!in_array($property->getName(), $limitProperty)) {
                    return;
                }
            }
            
            // 获取值
            if (is_null($value = ClassHelper::getPropertyValue($this, $property))) {
                return;
            }
            
            // 触发获取值接口
            if ($this instanceof FieldObtainDataInterface && is_null($value = $this->onObtainData($field, $property->getName(), $attrs, $value))) {
                return;
            }
            
            switch (true) {
                // bool转int
                case is_bool($value):
                    $value = (int) $value;
                break;
                
                // 是数组且第一个值不是exp参数
                case is_array($value) && ((isset($value[0]) && $value[0] !== 'exp') || !isset($value[0])):
                    // 按数组转换类型转换
                    $type = $attrs[self::ATTR_ARRAY];
                    if (is_array($type)) {
                        [$type, $fill] = $type;
                        $value = implode($type, $value);
                        if ($fill) {
                            $value = $type . $value . $type;
                        }
                    } else {
                        $type = strtolower($type);
                        switch (true) {
                            case $type == 'serialize':
                                $value = serialize($value);
                            break;
                            case $type == 'json':
                            default:
                                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                        }
                    }
                    $value = (string) $value;
                break;
                
                // 是对象
                case is_object($value):
                    // 字段实体
                    if ($value instanceof Entity) {
                        $value = new Raw($value->build() . $value->op() . $value->value());
                    } elseif (!$value instanceof Raw) {
                        $value = serialize($value);
                    }
                break;
                
                // 根据字段类型强制转换
                default:
                    switch ($attrs[ClassHelper::ATTR_VAR]) {
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
        $use           = $this->__private__options['use'] ?? null;
        $limitProperty = $this->__private__options['limit_property'] ?? [];
        $limitExclude  = $this->__private__options['limit_exclude'] ?? true;
        
        $vars = get_object_vars($this);
        $keys = array_keys($vars);
        // 取出私有属性
        foreach (ClassHelper::getReflectionClass($this)->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            $name = $property->getName();
            if ($property->isStatic() || 0 === strpos($name, self::$privateVarPrefix) || in_array($name, $keys)) {
                continue;
            }
            
            $vars[$name] = ClassHelper::getPropertyValue($this, $property);
        }
        
        // 输出指定属性
        $useMap = [];
        if ($use) {
            self::eachPropertyAttrs(function($field, ReflectionProperty $property, array $attrs) use ($use, &$useMap) {
                if (!isset($attrs[$use])) {
                    return;
                }
                
                $name          = $property->getName();
                $attrs[$use]   = is_array($attrs[$use]) ? end($attrs[$use]) : $attrs[$use];
                $useMap[$name] = $attrs[$use] ?: self::getPropertyToRenameMap($name);
            });
        }
        
        // 遍历所有
        $array = [];
        foreach ($vars as $property => $value) {
            if (0 === strpos($property, self::$privateVarPrefix) || in_array($property, self::getIgnorePropertyList())) {
                continue;
            }
            if ($limitExclude) {
                if (in_array($property, $limitProperty)) {
                    continue;
                }
            } else {
                if (!in_array($property, $limitProperty)) {
                    continue;
                }
            }
            
            if ($use) {
                if ($name = $useMap[$property] ?? null) {
                    $array[$name] = $value;
                }
            } else {
                if ($rename = self::getPropertyToRenameMap($property)) {
                    $array[$rename] = $value;
                } else {
                    $array[$property] = $value;
                }
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
        if ($property = self::getPropertyName($offset)) {
            $method = 'has' . ucfirst($property);
            
            return call_user_func([$this, $method]);
        }
        
        return isset($this->{$offset});
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if ($property = self::getPropertyName($offset)) {
            $method = 'get' . ucfirst($property);
            
            return call_user_func([$this, $method]);
        }
        
        return $this->{$offset};
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if ($property = self::getPropertyName($offset)) {
            $method = 'set' . ucfirst($property);
            
            call_user_func([$this, $method], $value);
            
            return;
        }
        
        $this->{$offset} = $value;
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->offsetSet($offset, null);
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