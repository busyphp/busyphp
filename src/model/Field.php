<?php
declare (strict_types = 1);

namespace BusyPHP\model;

use ArrayAccess;
use ArrayIterator;
use BusyPHP\exception\MethodNotFoundException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\interfaces\FieldGetModelDataInterface;
use BusyPHP\interfaces\FieldSetValueInterface;
use BusyPHP\Model;
use BusyPHP\model\annotation\field\AutoTimestamp;
use BusyPHP\model\annotation\field\BindModel;
use BusyPHP\model\annotation\field\Column;
use BusyPHP\model\annotation\field\Export;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Format;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\Import;
use BusyPHP\model\annotation\field\SoftDelete;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\ToArrayHidden;
use BusyPHP\model\annotation\field\ToArrayRename;
use BusyPHP\model\annotation\field\Validator;
use BusyPHP\model\annotation\field\ValueBindField;
use BusyPHP\model\annotation\relation\Relation;
use Closure;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use RuntimeException;
use think\contract\Arrayable;
use think\contract\Jsonable;
use think\db\Raw;
use think\Validate;
use think\validate\ValidateRule;

/**
 * 模型字段基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/6 下午3:07 下午 Field.php $
 */
class Field implements Arrayable, Jsonable, ArrayAccess, JsonSerializable, IteratorAggregate, Countable
{
    /** @var string 属性反射对象 */
    private const ATTR_PROPERTY = 'property';
    
    /** @var string 属性的权限 */
    private const ATTR_ACCESS = 'access';
    
    /** @var string 属性的类型 */
    private const ATTR_TYPES = 'types';
    
    /** @var string 属性的第一个类型 */
    private const ATTR_VAR_TYPE = 'var_type';
    
    /** @var string 属性的过滤方法 */
    private const ATTR_FILTER = 'filter';
    
    /** @var string 属性的格式化 */
    private const ATTR_FORMAT = 'format';
    
    /** @var string 属性的说明 */
    private const ATTR_TITLE = 'title';
    
    /** @var string 属性的字段名称 */
    private const ATTR_FIELD = 'field';
    
    /** @var string 属性的字段类型 */
    private const ATTR_FIELD_TYPE = 'field_type';
    
    /** @var string 属性名称 */
    private const ATTR_NAME = 'name';
    
    /** @var string 属性验证规则 */
    private const ATTR_VALIDATE = 'validate';
    
    /** @var string 导出字段 */
    private const ATTR_EXPORT = 'export';
    
    /** @var string 导入字段 */
    private const ATTR_IMPORT = 'import';
    
    /** @var string 输出属性格式注解 */
    private const MAP_TO_ARRAY_FORMAT = 'to_array_format';
    
    /** @var string 输出隐藏集合 */
    private const MAP_TO_ARRAY_HIDDEN = 'to_array_hidden';
    
    /** @var string 输出重命名，属性=>重命名映射 */
    private const MAP_TO_ARRAY_RENAME = 'to_array_rename';
    
    /** @var string 属性集合 */
    private const MAP_PROPERTY_LIST = 'property_list';
    
    /** @var string 属性参数集合 */
    private const MAP_PROPERTY_ATTR = 'property_attr';
    
    /** @var string 属性=>字段映射 */
    private const MAP_PROPERTY_FIELD = 'property_field';
    
    /** @var string snake属性映射=>属性映射 */
    private const MAP_PROPERTY_SNAKE = 'property_snake';
    
    /** @var string 属性绑定属性关系映射 */
    private const MAP_VALUE_BIND_FIELD = 'property_bind';
    
    /** @var string 模型相关参数 */
    private const MAP_MODEL_PARAMS = 'model_params';
    
    /** @var string 私有变量前缀 */
    private const PRIVATE_VAR_PREFIX = '__private__';
    
    /**
     * 服务注入
     * @var Closure[]
     */
    protected static array $maker = [];
    
    /**
     * 字段=>子类属性映射
     * @var string[][]
     */
    private static array $fieldToPropertyMap = [];
    
    /**
     * 属性名称=>子类属性名称映射
     * @var string[][]
     */
    private static array $propertyNameMap = [];
    
    /**
     * 属性结构
     * @var array
     */
    private static array $propertyMap = [];
    
    /**
     * 选项
     * @var array
     */
    private array $__private__options = [];
    
    
    /**
     * 快速实例化
     * @param array $data 初始数据
     * @return static
     */
    public static function init(array $data = []) : self
    {
        return new static($data);
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
     * @param array|static $data 数据
     * @return static
     */
    public static function parse(array|Field $data) : static
    {
        if ($data instanceof static) {
            $data->onParseAfter();
            
            return $data;
        }
        
        $obj = static::init($data);
        
        // 绑定值
        foreach (self::getValueBindFieldMap() as $name => $bind) {
            if (isset($data[$bind->getField()])) {
                self::setPropertyValue($obj, $name, $data[$bind->getField()]);
            }
        }
        
        // 后置操作
        $obj->onParseAfter();
        
        return $obj;
    }
    
    
    /**
     * 解析数据集
     * @param Model    $model 当前模型
     * @param static[] $list 数据集
     * @param bool     $extend 是否解析关联信息
     */
    public static function onParseList(Model $model, array $list, bool $extend)
    {
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
                // 通过字段取属性
                case null !== $property = self::getSnakeToPropertyMap($name):
                case null !== $property = self::getFieldToPropertyMap($name):
                    self::$propertyNameMap[static::class][$name] = $property;
                break;
                
                // 通过属性取属性
                case in_array($name, self::getPropertyList()):
                    self::$propertyNameMap[static::class][$name] = $name;
                break;
                
                default:
                    self::$propertyNameMap[static::class][$name] = null;
            }
        }
        
        return self::$propertyNameMap[static::class][$name];
    }
    
    
    /**
     * 通过属性名称强制转换值
     * @param Field  $field Field对象
     * @param string $property 属性
     * @param mixed  $value 值
     * @return mixed
     */
    protected static function setPropertyValue(Field $field, string $property, mixed $value) : mixed
    {
        if (!isset(self::$propertyMap[static::class])) {
            self::getPropertyAttrs();
        }
        
        if (!isset(self::$propertyMap[static::class][self::MAP_PROPERTY_ATTR][$property])) {
            throw new RuntimeException(sprintf('Property "%s" does not exist in class "%s"', $property, get_class($field)));
        }
        $attrs = self::$propertyMap[static::class][self::MAP_PROPERTY_ATTR][$property];
        
        // 直接设置
        if (null === $value || $value instanceof Entity || $value instanceof Raw) {
            goto end;
        }
        
        // 函数过滤
        /** @var Filter $vo */
        foreach ($attrs[self::ATTR_FILTER] as $vo) {
            $args = $vo->getArgs();
            array_unshift($args, $value);
            $value = call_user_func($vo->getFilter(), ...$args);
        }
        
        // 执行处理
        if ($field instanceof FieldSetValueInterface) {
            $value = $field->onSetValue($attrs[self::ATTR_FIELD], $property, $attrs, $value);
        }
        
        // 强制转换
        $format  = $attrs[self::ATTR_FORMAT];
        $varType = $attrs[self::ATTR_VAR_TYPE];
        switch ($varType) {
            // 数字
            case 'int':
                $value = (int) $value;
            break;
            // 浮点
            case 'float':
                $value = (float) $value;
            break;
            // 布尔
            case 'bool':
                $value = (bool) $value;
            break;
            // 数组
            case 'array':
                if (!is_array($value)) {
                    $value = (string) $value;
                    switch (true) {
                        case $format instanceof Format:
                            $value = $format->decode($value);
                        break;
                        case str_starts_with($value, '[') || str_starts_with($value, '{'):
                            $value = json_decode($value, true);
                        break;
                        case str_starts_with($value, 'a:'):
                            $value = unserialize($value);
                        break;
                    }
                    $value = (array) $value;
                }
            break;
            // 其它
            default:
                // 反序列化
                $isClass  = is_string($varType) && str_contains($varType, '\\') && class_exists($varType);
                $isObject = $varType === 'object';
                if (is_string($value)) {
                    if (str_starts_with($value, 'O:') && ($isClass || $isObject)) {
                        $value = unserialize($value) ?: null;
                    } elseif ($format instanceof Format) {
                        $value = $format->decode($value);
                    }
                }
        }
        
        // 设置值
        end:
        if ($attrs[self::ATTR_ACCESS] == ReflectionProperty::IS_PRIVATE) {
            ClassHelper::setPropertyValue($field, $attrs[self::ATTR_PROPERTY], $value);
        } else {
            $field->{$property} = $value;
        }
        
        return $value;
    }
    
    
    /**
     * 获取当前类属性的注释属性
     * @param string|null $property
     * @return array[]|array{title: string, name: string, field: string|false, type: array<ReflectionNamedType>, filter: array<callable>, ignore: array, property: ReflectionProperty}
     */
    public static function getPropertyAttrs(string $property = null) : ?array
    {
        if (!isset(self::$propertyMap[static::class])) {
            $class             = ClassHelper::getReflectionClass(static::class);
            $data              = [];
            $names             = [];
            $titleMap          = [];
            $renames           = [];
            $fieldMap          = [];
            $filterMap         = [];
            $formatMap         = [];
            $validateMap       = [];
            $toRenames         = [];
            $toRenameMap       = [];
            $toHiddenList      = [];
            $testFields        = [];
            $testToRenames     = [];
            $snakeMap          = [];
            $modelRelationMap  = [];
            $valueBindFieldMap = [];
            $fieldTypeMap      = [];
            $readonlyList      = [];
            $createTimeField   = '';
            $updateTimeField   = '';
            $softDeleteField   = '';
            $primaryField      = 'id';
            
            // 输出格式化注解
            $toFormat = null;
            if ($attributes = $class->getAttributes(ToArrayFormat::class)) {
                $toFormat = $attributes[count($attributes) - 1]->newInstance();
            }
            
            // 自动填充时间
            $autoTimestamp = false;
            $dateFormat    = '';
            if ($attributes = $class->getAttributes(AutoTimestamp::class)) {
                /** @var AutoTimestamp $instance */
                $instance      = $attributes[count($attributes) - 1]->newInstance();
                $dateFormat    = $instance->getFormat();
                $autoTimestamp = $instance->getType();
            }
            
            // 启用软删除
            $softDeleteDefault = 0;
            $softDelete        = false;
            if ($attributes = $class->getAttributes(SoftDelete::class)) {
                /** @var SoftDelete $instance */
                $instance          = $attributes[count($attributes) - 1]->newInstance();
                $softDeleteDefault = $instance->getDefault();
                $softDelete        = true;
            }
            
            // 绑定模型
            $model = '';
            $alias = '';
            if ($attributes = $class->getAttributes(BindModel::class)) {
                /** @var BindModel $instance */
                $instance = $attributes[count($attributes) - 1]->newInstance();
                $model    = $instance->getModel();
                $alias    = $instance->getAlias();
            }
            if (!$alias) {
                if ($model) {
                    $alias = basename(str_replace('\\', '/', $model));
                } else {
                    $alias = basename(str_replace('\\', '/', static::class));
                    if (strtolower(substr($alias, -5)) === 'field') {
                        $alias = substr($alias, 0, -5);
                    }
                }
            }
            
            foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE) as $item) {
                $name = $item->getName();
                if ($item->isStatic() || str_starts_with($name, self::PRIVATE_VAR_PREFIX)) {
                    continue;
                }
                
                $access = ReflectionProperty::IS_PUBLIC;
                if ($item->isProtected()) {
                    $access = ReflectionProperty::IS_PROTECTED;
                } elseif ($item->isPrivate()) {
                    $access = ReflectionProperty::IS_PRIVATE;
                }
                
                $snake     = StringHelper::snake($name);
                $names[]   = $name;
                $field     = '';
                $ignore    = false;
                $attr      = ClassHelper::extractDocAttrs($class, $name, null, $item->getDocComment());
                $type      = $item->getType();
                $primary   = $name === 'id';
                $readonly  = false;
                $fieldType = '';
                $feature   = 0;
                $export    = null;
                $import    = null;
                $types     = [];
                if ($type instanceof ReflectionNamedType) {
                    $types[] = [
                        'name'    => $type->getName(),
                        'builtin' => $type->isBuiltin()
                    ];
                } elseif ($type instanceof ReflectionUnionType) {
                    $types = $type->getTypes();
                    foreach ($type->getTypes() as $namedType) {
                        $types[] = [
                            'name'    => $namedType->getName(),
                            'builtin' => $namedType->isBuiltin()
                        ];
                    }
                } else {
                    /** @var \BusyPHP\helper\ReflectionNamedType $namedType */
                    foreach ($attr[ClassHelper::ATTR_VAR] as $namedType) {
                        $types[] = [
                            'name'    => $namedType->getName(),
                            'builtin' => $namedType->isBuiltin()
                        ];
                    }
                }
                
                foreach ($item->getAttributes() as $attribute) {
                    $attributeName = $attribute->getName();
                    switch (true) {
                        // 字段属性注解
                        case $attributeName === Column::class:
                            /** @var Column $instance */
                            $instance  = $attribute->newInstance();
                            $fieldType = $instance->getType();
                            $primary   = $instance->isPrimary();
                            $readonly  = $instance->isReadonly();
                            $feature   = $instance->getFeature();
                            
                            // 真实字段名称
                            if ($rename = $instance->getField()) {
                                if (in_array($rename, $renames)) {
                                    throw new RuntimeException(sprintf('The annotation "#[%s(field: \'%s\')]" of property "%s" in class "%s" can\'t repeat', Column::class, $rename, $name, static::class));
                                }
                                
                                $field     = $rename;
                                $renames[] = $rename;
                                if ($rename !== $name) {
                                    $testFields[$name] = $rename;
                                }
                            }
                            
                            // 字段标题
                            if ('' !== $getTitle = $instance->getTitle()) {
                                $titleMap[$name] = $getTitle;
                            }
                        break;
                        
                        // 忽略属性解析为字段
                        case $attributeName === Ignore::class:
                            $ignore = true;
                        break;
                        
                        // 过滤注解
                        case $attributeName === Filter::class:
                            $filterMap[$name][] = $attribute->newInstance();
                        break;
                        
                        // 输出隐藏注解
                        case $attributeName === ToArrayHidden::class:
                            /** @var ToArrayHidden $instance */
                            $instance    = $attribute->newInstance();
                            $hiddenValue = $instance->getScene() . '.' . $name;
                            if (in_array($hiddenValue, $toHiddenList, true)) {
                                break;
                            }
                            
                            $toHiddenList[] = $hiddenValue;
                        break;
                        
                        // 输出重命名注解
                        case $attributeName === ToArrayRename::class:
                            /** @var ToArrayRename $instance */
                            $instance    = $attribute->newInstance();
                            $scene       = $instance->getScene();
                            $renameValue = $instance->getName();
                            if ($renameValue === '') {
                                break;
                            }
                            
                            $toRenames[$scene] = $toRenames[$scene] ?? [];
                            if (in_array($renameValue, $toRenames[$scene])) {
                                throw new RuntimeException(sprintf('The annotation "#[%s(\'%s\', \'%s\')]" of property "%s" in class "%s" can\'t repeat', ToArrayRename::class, $renameValue, $scene, $name, static::class));
                            }
                            
                            $toRenames[$scene][]               = $renameValue;
                            $toRenameMap[$scene . '.' . $name] = $renameValue;
                            if ($renameValue !== $name) {
                                $testToRenames[$scene][$name] = $renameValue;
                            }
                        break;
                        
                        // 数据格式化注解
                        case is_a($attributeName, Format::class, true):
                            $formatMap[$name] = $attribute->newInstance();
                        break;
                        
                        // 验证规则注解
                        case $attributeName === Validator::class:
                            /** @var Validator $instance */
                            $instance             = $attribute->newInstance();
                            $validateMap[$name][] = [
                                $instance->getName(),
                                $instance->getRule(),
                                $instance->getMsg()
                            ];
                        break;
                        
                        // 关联
                        case is_a($attributeName, Relation::class, true):
                            /** @var Relation $relation */
                            $relation                = $attribute->newInstance();
                            $modelRelationMap[$name] = $relation($item);
                        break;
                        
                        // 值绑定
                        case $attributeName === ValueBindField::class:
                            $valueBindFieldMap[$name] = $attribute->newInstance();
                        break;
                        
                        // 导出
                        case $attributeName === Export::class:
                            /** @var Export $export */
                            $export = $attribute->newInstance();
                        break;
                        
                        // 导入
                        case $attributeName === Import::class:
                            $import = $attribute->newInstance();
                        break;
                    }
                }
                
                // 字段标题
                $title = $titleMap[$name] ?? '';
                $title = $title === '' ? $attr[ClassHelper::ATTR_NAME] : $title;
                $title = $title === '' ? $name : $title;
                
                // 导出名称
                $export?->setName($title);
                
                // 属性类型
                $varType = 'mixed';
                if ($type = ($types[0] ?? null)) {
                    $varType = $type['name'];
                }
                
                // 字段
                $snakeMap[$snake] = $name;
                if (!$ignore) {
                    $fieldMap[$name] = $field ?: $snake;
                    
                    // 真实字段类型解析
                    if ($fieldType === Column::TYPE_DEFAULT || !$fieldType) {
                        if (in_array($varType, [Column::TYPE_INT, Column::TYPE_FLOAT, Column::TYPE_BOOL])) {
                            $fieldType = $varType;
                        } else {
                            $fieldType = Column::TYPE_STRING;
                        }
                    }
                    
                    $fieldTypeMap[$fieldMap[$name]] = $fieldType;
                    
                    // 主键
                    if ($primary) {
                        $primaryField = $fieldMap[$name];
                    }
                    
                    // 只读
                    if ($readonly) {
                        $readonlyList[] = $fieldMap[$name];
                    }
                    
                    switch ($feature) {
                        // 自动创建时间
                        case Column::FEATURE_CREATE_TIME:
                            $createTimeField = $fieldMap[$name];
                        break;
                        // 自动更新时间
                        case Column::FEATURE_UPDATE_TIME:
                            $updateTimeField = $fieldMap[$name];
                        break;
                        // 软删除字段
                        case Column::FEATURE_SOFT_DELETE:
                            $softDeleteField = $fieldMap[$name];
                        break;
                    }
                }
                
                $data[$name] = [
                    self::ATTR_TITLE      => $title,
                    self::ATTR_NAME       => $name,
                    self::ATTR_FIELD      => $fieldMap[$name] ?? '',
                    self::ATTR_TYPES      => $types,
                    self::ATTR_VAR_TYPE   => $varType,
                    self::ATTR_FIELD_TYPE => $fieldType,
                    self::ATTR_FILTER     => $filterMap[$name] ?? [],
                    self::ATTR_FORMAT     => $formatMap[$name] ?? null,
                    self::ATTR_VALIDATE   => $validateMap[$name] ?? [],
                    self::ATTR_ACCESS     => $access,
                    self::ATTR_PROPERTY   => $item,
                    self::ATTR_EXPORT     => $export,
                    self::ATTR_IMPORT     => $import,
                ];
            }
            
            // 指定的字段名称不能和已有的属性一样
            foreach ($testFields as $name => $rename) {
                if (in_array($rename, $names)) {
                    throw new RuntimeException(sprintf('The annotation "#[%s(field: \'%s\')]" of property "%s" in class "%s" cannot overwrite the existing property', Column::class, $rename, $name, static::class));
                }
            }
            
            // 指定的重命名不能和已有的属性一样
            foreach ($testToRenames as $scene => $values) {
                foreach ($values as $f => $value) {
                    if (in_array($value, $names)) {
                        throw new RuntimeException(sprintf('The annotation "#[%s(\'%s\', \'%s\')]" of property "%s" in class "%s" cannot overwrite the existing property', ToArrayRename::class, $value, $scene, $f, static::class));
                    }
                }
            }
            
            self::$propertyMap[static::class] = [
                self::MAP_TO_ARRAY_FORMAT  => $toFormat,
                self::MAP_TO_ARRAY_RENAME  => $toRenameMap,
                self::MAP_TO_ARRAY_HIDDEN  => $toHiddenList,
                self::MAP_PROPERTY_LIST    => $names,
                self::MAP_PROPERTY_ATTR    => $data,
                self::MAP_PROPERTY_FIELD   => $fieldMap,
                self::MAP_PROPERTY_SNAKE   => $snakeMap,
                self::MAP_VALUE_BIND_FIELD => $valueBindFieldMap,
                self::MAP_MODEL_PARAMS     => [
                    'type'                => $fieldTypeMap,
                    'readonly'            => $readonlyList,
                    'pk'                  => $primaryField,
                    'auto_timestamp'      => $autoTimestamp,
                    'date_format'         => $dateFormat,
                    'create_time_field'   => $createTimeField,
                    'update_time_field'   => $updateTimeField,
                    'soft_delete'         => $softDelete,
                    'soft_delete_field'   => $softDeleteField,
                    'soft_delete_default' => $softDeleteDefault,
                    'relation'            => $modelRelationMap,
                    'alias'               => $alias,
                    'model'               => $model
                ]
            ];
            
            unset($types, $toRenames, $titleMap, $testToRenames, $testFields, $names, $data, $toRenameMap, $toHiddenList, $formatMap, $validateMap, $fieldMap, $snakeMap, $modelRelationMap, $valueBindFieldMap, $fieldTypeMap, $readonlyList);
        }
        
        $map = self::$propertyMap[static::class][self::MAP_PROPERTY_ATTR];
        if (null === $property) {
            return $map;
        }
        
        return $map[$property] ?? null;
    }
    
    
    /**
     * 递归当前类属性的注释属性
     * @param callable(string, ReflectionProperty, array):void $callback
     */
    protected static function eachPropertyAttrs(callable $callback)
    {
        foreach (self::getPropertyAttrs() as $item) {
            call_user_func($callback, $item[self::ATTR_FIELD], $item[self::ATTR_PROPERTY], $item);
        }
    }
    
    
    /**
     * 获取忽略的属性集合
     * @return string[]
     */
    protected static function getIgnorePropertyList() : array
    {
        if (!isset(self::$propertyMap[static::class])) {
            self::getPropertyAttrs();
        }
        
        return self::$propertyMap[static::class][self::MAP_TO_ARRAY_HIDDEN];
    }
    
    
    /**
     * 获取子类属性 => 字段映射关系
     * @param string|null $property 属性名称
     * @return array|string|null
     */
    protected static function getPropertyToFieldMap(string $property = null) : array|string|null
    {
        if (!isset(self::$propertyMap[static::class])) {
            self::getPropertyAttrs();
        }
        
        $map = self::$propertyMap[static::class][self::MAP_PROPERTY_FIELD];
        if (null === $property) {
            return $map;
        }
        
        return $map[$property] ?? null;
    }
    
    
    /**
     * 获取snake子类属性 => 真实子类属性关系
     * @param string|null $snake
     * @return array|string|null
     */
    protected static function getSnakeToPropertyMap(string $snake = null) : array|string|null
    {
        if (!isset(self::$propertyMap[static::class])) {
            self::getPropertyAttrs();
        }
        
        $map = self::$propertyMap[static::class][self::MAP_PROPERTY_SNAKE];
        if (null === $snake) {
            return $map;
        }
        
        return $map[$snake] ?? null;
    }
    
    
    /**
     * 获取字段 => 子类属性映射关系
     * @param string|null $field 字段名称
     * @return array|string|null
     */
    protected static function getFieldToPropertyMap(string $field = null) : array|string|null
    {
        if (!isset(self::$fieldToPropertyMap[static::class])) {
            self::$fieldToPropertyMap[static::class] = array_flip(self::getPropertyToFieldMap());
        }
        
        $map = self::$fieldToPropertyMap[static::class];
        if (null === $field) {
            return $map;
        }
        
        return $map[$field] ?? null;
    }
    
    
    /**
     * 获取子类属性 => 重命名映射关系
     * @param string|null $property 属性名称
     * @return array|string|null
     */
    protected static function getPropertyToRenameMap(string $property = null) : array|string|null
    {
        if (!isset(self::$propertyMap[static::class])) {
            self::getPropertyAttrs();
        }
        
        $map = self::$propertyMap[static::class][self::MAP_TO_ARRAY_RENAME];
        if (null === $property) {
            return $map;
        }
        
        return $map[$property] ?? null;
    }
    
    
    /**
     * 获取输出格式注解
     * @return ToArrayFormat|null
     */
    protected static function getToFormatAnnotation() : ?ToArrayFormat
    {
        if (!isset(self::$propertyMap[static::class])) {
            self::getPropertyAttrs();
        }
        
        return self::$propertyMap[static::class][self::MAP_TO_ARRAY_FORMAT];
    }
    
    
    /**
     * 获取值绑定字段关系映射
     * @return array<string,ValueBindField>
     * @internal
     */
    protected static function getValueBindFieldMap() : array
    {
        if (!isset(self::$propertyMap[static::class])) {
            self::getPropertyAttrs();
        }
        
        return self::$propertyMap[static::class][self::MAP_VALUE_BIND_FIELD];
    }
    
    
    /**
     * 获取所有属性集合
     * @param Entity|string|Entity[]|string[] ...$excludes 排除的属性
     * @return string[]
     */
    public static function getPropertyList(...$excludes) : array
    {
        if (!isset(self::$propertyMap[static::class])) {
            self::getPropertyAttrs();
        }
        $list = self::$propertyMap[static::class][self::MAP_PROPERTY_LIST];
        if (!$excludes) {
            return $list;
        }
        
        $excludes = array_map(function($item) {
            if ($item instanceof Entity) {
                return $item();
            }
            
            return $item;
        }, ArrayHelper::flat($excludes));
        
        $data = [];
        foreach ($list as $property) {
            if (in_array($property, $excludes)) {
                continue;
            }
            $data[] = $property;
        }
        
        return $data;
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
        foreach (self::getPropertyToFieldMap() as $field) {
            if (in_array($field, $excludes)) {
                continue;
            }
            $data[] = $field;
        }
        
        return $data;
    }
    
    
    /**
     * 获取模型参数
     * @return array{type: array<string,string>, readonly: array<string>, pk: string, create_time_field: string, update_time_field: string, soft_delete_field: string, soft_delete_default: string, soft_delete: bool, relation: array<string,Relation>, auto_timestamp: string|bool, date_format: string, alias: string, model: class-string<Model>}
     */
    public static function getModelParams() : array
    {
        if (!isset(self::$propertyMap[static::class])) {
            self::getPropertyAttrs();
        }
        
        return self::$propertyMap[static::class][self::MAP_MODEL_PARAMS];
    }
    
    
    /**
     * 生成包含JOIN别名的 "*" 字段
     * @return string
     */
    public static function makeAsteriskField() : string
    {
        return self::getModelParams()['alias'] . '.*';
    }
    
    
    /**
     * 生成距离查询字段，计算出来距离单位为米
     * @param string|Entity $latField 纬度字段
     * @param string|Entity $lngField 经度字段
     * @param float         $lat 纬度值
     * @param float         $lng 经度值
     * @param string|Entity $alias 别名
     * @return string
     */
    public static function makeDistanceField(string|Entity $latField, string|Entity $lngField, float $lat, float $lng, string|Entity $alias = 'distance') : string
    {
        $latField = (string) $latField;
        $lngField = (string) $lngField;
        $alias    = (string) $alias;
        
        return sprintf("round( 6378.138 * 2 * ASIN( SQRT( POW( SIN( ( %s * PI() / 180 - %s * PI() / 180 ) / 2 ), 2 ) + COS(%s * PI() / 180) * COS(%s * PI() / 180) * POW( SIN( ( %s * PI() / 180 - %s * PI() / 180 ) / 2 ), 2 ) ) ) * 1000) as %s", $lat, $latField, $lat, $latField, $lng, $lngField, $alias);
    }
    
    
    /**
     * 获取数据验证器
     * @return Validate
     */
    public static function getValidate() : Validate
    {
        $names    = [];
        $messages = [];
        $validate = new Validate();
        foreach (self::getPropertyAttrs() as $property => $attr) {
            $names[$property] = $attr[self::ATTR_TITLE];
            if (!$attr[self::ATTR_VALIDATE]) {
                continue;
            }
            
            $rule = ValidateRule::init()->title($attr[self::ATTR_TITLE]);
            
            foreach ($attr[self::ATTR_VALIDATE] as $item) {
                $msg  = $item[2];
                $name = $item[0];
                $rule->addItem($name, $item[1], $msg);
                if ($msg !== '') {
                    $messages[$property . '.' . $name] = $msg;
                }
            }
            
            $validate->rule($property, $rule);
        }
        
        $validate->rule([], $names);
        $validate->message($messages);
        
        return $validate;
    }
    
    
    public static function __callStatic($name, $arguments)
    {
        // 静态方法名称存在属性中，则返回属性实体
        $field   = self::getPropertyToFieldMap($name);
        $virtual = false;
        if (!$field && in_array($name, self::getPropertyList())) {
            $field   = $name;
            $virtual = true;
        }
        
        if ($field) {
            $entity = new Entity($name, $field, self::getModelParams()['alias'], static::class, $virtual);
            
            // 设置条件
            switch (count($arguments)) {
                case 1:
                    $entity->op('=');
                    $entity->value($arguments[0]);
                break;
                case 2:
                    // null
                    if (is_bool($arguments[0]) && null === $arguments[1]) {
                        $arguments[0] = $arguments[0] ? 'NULL' : 'NOTNULL';
                        $arguments[1] = null;
                    }
                    
                    $entity->op($arguments[0] ?: '=');
                    $entity->value($arguments[1]);
                break;
            }
            
            return $entity;
        }
        
        throw new MethodNotFoundException(static::class, $name, true);
    }
    
    
    /**
     * 构造函数
     * @param array $data 初始数据
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $name => $value) {
            if (is_numeric($name)) {
                continue;
            }
            
            if ($property = self::getPropertyName($name)) {
                self::setPropertyValue($this, $property, $value);
            } else {
                $this->{$name} = $value;
            }
        }
        
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
            if (!isset(self::$propertyMap[static::class])) {
                self::getPropertyAttrs();
            }
            
            // 排除引起歧义的属性，如属性名称为：hash，setting, getting 等包含特殊前缀的，直接返回
            $map = self::$propertyMap[static::class][self::MAP_PROPERTY_ATTR];
            if (isset($map[$name])) {
                return static::__callStatic($name, $arguments);
            }
            
            $property = StringHelper::camel(substr($name, 3));
            if (!isset($map[$property])) {
                throw new RuntimeException(sprintf('The property "%s" of the class "%s" does not exist', $property, static::class));
            }
            
            $attrs = $map[$property];
            
            // setField
            if ($prefix == 'set') {
                // 设置值
                self::setPropertyValue($this, $property, $arguments[0] ?? null);
                
                return $this;
            }
            
            //
            // getField
            elseif ($prefix == 'get') {
                if (ReflectionProperty::IS_PRIVATE === $attrs[self::ATTR_ACCESS]) {
                    return ClassHelper::getPropertyValue($this, $attrs[self::ATTR_PROPERTY]);
                } else {
                    return $this->{$property};
                }
            }
            
            //
            // hasField
            else {
                if (ReflectionProperty::IS_PRIVATE === $attrs[self::ATTR_ACCESS]) {
                    return ClassHelper::getPropertyValue($this, $attrs[self::ATTR_PROPERTY]) !== null;
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
     * 排除属性，执行 {@see Field::getModelData()} {@see Field::toArray()} 时有效，与 {@see Field::retain()} 互斥
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
     * 保留属性，执行 {@see Field::getModelData()} {@see Field::toArray()} 时有效，与 {@see Field::exclude()} 互斥
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
     * 使用场景输出，以执行 {@see Field::toArray()}
     * @param string $scene 指定场景名称输出
     * @return $this
     */
    public function scene(string $scene = '') : self
    {
        $this->__private__options['scene'] = $scene;
        
        return $this;
    }
    
    
    /**
     * 重置由 {@see Field::scene()} {@see Field::retain()} {@see Field::exclude()} 设置的条件
     * @return $this
     */
    public function reset() : self
    {
        $this->__private__options = [];
        
        return $this;
    }
    
    
    /**
     * 获取支持 {@see Model} 的数据
     * @return array
     */
    public function getModelData() : array
    {
        $limitProperty = $this->__private__options['limit_property'] ?? [];
        $limitExclude  = $this->__private__options['limit_exclude'] ?? true;
        $data          = [];
        
        self::eachPropertyAttrs(function(string $field, ReflectionProperty $property, array $attrs) use (&$data, $limitProperty, $limitExclude) {
            if (!$field) {
                return;
            }
            
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
            if (null === $value = ClassHelper::getPropertyValue($this, $property)) {
                return;
            }
            
            // 触发获取值接口
            if ($this instanceof FieldGetModelDataInterface && null === $value = $this->onGetModelData($field, $property->getName(), $attrs, $value)) {
                return;
            }
            
            // 直接赋值:
            // 1. 自定义 exp 语法
            // 2. Raw对象
            if ((is_array($value) && isset($value[0]) && $value[0] === 'exp') || $value instanceof Raw) {
                $data[$field] = $value;
            } elseif ($value instanceof Entity) {
                $data[$field] = new Raw($value->field() . $value->getOp() . $value->getValue());
            } else {
                $varType = $attrs[self::ATTR_VAR_TYPE];
                switch (true) {
                    // 转int
                    case $varType === 'int':
                    case $varType === 'bool':
                    case is_bool($value):
                        $value = (int) $value;
                    break;
                    
                    // 转float
                    case $varType === 'float':
                        $value = (float) $value;
                    break;
                    
                    // 字符串
                    default:
                        $format = $attrs[self::ATTR_FORMAT];
                        if ($format instanceof Format) {
                            $value = $format->encode($value);
                        } elseif (is_array($value)) {
                            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                        } elseif (is_object($value)) {
                            $value = serialize($value);
                        } else {
                            $value = (string) $value;
                        }
                }
                
                $data[$field] = $value;
            }
        });
        
        return $data;
    }
    
    
    public function toArray() : array
    {
        if (!isset(self::$propertyMap[static::class])) {
            self::getPropertyAttrs();
        }
        
        $map           = self::$propertyMap[static::class][self::MAP_PROPERTY_ATTR];
        $scene         = $this->__private__options['scene'] ?? '';
        $limitProperty = $this->__private__options['limit_property'] ?? [];
        $limitExclude  = $this->__private__options['limit_exclude'] ?? true;
        
        $vars = get_object_vars($this);
        $keys = array_keys($vars);
        // 取出私有属性
        foreach (ClassHelper::getReflectionClass($this)->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            $name = $property->getName();
            if ($property->isStatic() || str_starts_with($name, self::PRIVATE_VAR_PREFIX) || in_array($name, $keys)) {
                continue;
            }
            
            $vars[$name] = ClassHelper::getPropertyValue($this, $property);
        }
        
        // 遍历所有
        $array   = [];
        $ignores = self::getIgnorePropertyList();
        foreach ($vars as $property => $value) {
            if (str_starts_with($property, self::PRIVATE_VAR_PREFIX) || in_array($scene . '.' . $property, $ignores) || in_array('.' . $property, $ignores)) {
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
            
            // 按重命名输出
            if ($rename = self::getPropertyToRenameMap($scene . '.' . $property)) {
                $array[$rename] = $value;
            } elseif ($rename = self::getPropertyToRenameMap('.' . $property)) {
                $array[$rename] = $value;
            } else {
                $key = $property;
                if ($toFormat = self::getToFormatAnnotation()) {
                    $key = $toFormat->build($property, $map[self::ATTR_FIELD] ?? '');
                }
                $array[$key] = $value;
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
    
    
    #[\ReturnTypeWillChange]
    public function getIterator() : iterable
    {
        return new ArrayIterator($this->toArray());
    }
    
    
    #[\ReturnTypeWillChange]
    public function count() : int
    {
        return count($this->toArray());
    }
}