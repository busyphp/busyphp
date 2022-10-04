<?php
declare(strict_types = 1);

namespace BusyPHP\helper;

use BusyPHP\exception\MethodNotFoundException;
use PhpDocReader\PhpParser\UseStatementParser;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;
use RuntimeException;
use Throwable;

/**
 * 类辅助
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/10/1 9:00 PM ClassHelper.php $
 */
class ClassHelper
{
    /** @var string[] 类型映射 */
    public const PRIMITIVE_TYPES = [
        'bool'     => 'bool',
        'boolean'  => 'bool',
        'string'   => 'string',
        'int'      => 'int',
        'integer'  => 'int',
        'float'    => 'float',
        'double'   => 'float',
        'array'    => 'array',
        'object'   => 'object',
        'callable' => 'callable',
        'resource' => 'resource',
        'mixed'    => 'mixed',
        'iterable' => 'iterable',
    ];
    
    /** @var string const名称 */
    public const ATTR_NAME = 'name';
    
    /** @var string const类型 */
    public const ATTR_VAR = 'var';
    
    /** @var string const定义名称 */
    public const ATTR_KEY = 'key';
    
    /** @var string const值 */
    public const ATTR_VALUE = 'value';
    
    /** @var string 转为整数类型 */
    public const CAST_INT = 'int';
    
    /** @var string 转为浮点类型 */
    public const CAST_FLOAT = 'float';
    
    /** @var string 转为字符串 */
    public const CAST_STRING = 'string';
    
    /** @var string JSON字符串转数组 */
    public const CAST_JSON = 'json';
    
    /** @var string 转为布尔类型 */
    public const CAST_BOOL = 'bool';
    
    /** @var string 转为类名 */
    public const CAST_CLASS = 'class';
    
    /** @var UseStatementParser */
    protected static $useStatementParser;
    
    /** @var ReflectionClass[] */
    private static $reflectionClass = [];
    
    /** @var array[] */
    private static $constAttrs = [];
    
    
    /**
     * 编码注释中的特殊字符
     * @param string $content
     * @return string
     */
    public static function encodeDocSpecialStr(string $content) : string
    {
        $content = str_replace('\*', '<!Kg!>', $content);
        
        return str_replace('\@', '<!QA!>', $content);
    }
    
    
    /**
     * 解码注释中的特殊字符
     * @param string $content
     * @return string
     */
    public static function decodeDocSpecialStr(string $content) : string
    {
        $content = str_replace('<!Kg!>', '*', $content);
        
        return str_replace('<!QA!>', '@', $content);
    }
    
    
    /**
     * 替换 @mehotd 中的特殊字符
     * @param string $content
     * @return string
     */
    public static function replaceMethodDocSpecialStr(string $content) : string
    {
        $content = str_replace('(', '[', $content);
        $content = str_replace(')', ']', $content);
        
        return str_replace(';', '/', $content);
    }
    
    
    /**
     * 解析类常量
     * @param string|object                     $objectOrClass 类
     * @param string                            $prefix 常量前缀
     * @param array|string                      $attrsOrMapKey 其它属性或数据映射
     * @param string|callable(array):mixed|null $mapKey 指定某个属性的值作为值
     * @param string                            $memoryCacheKey 内存缓存标识
     * @return array
     */
    public static function getConstAttrs($objectOrClass, string $prefix = '', $attrsOrMapKey = [], $mapKey = null, string $memoryCacheKey = '') : array
    {
        $classname = md5(
            self::getAbsoluteClassname($objectOrClass) .
            serialize($attrsOrMapKey) .
            $memoryCacheKey
        );
        
        if (isset(self::$constAttrs[$classname])) {
            return self::$constAttrs[$classname];
        }
        
        if (is_string($attrsOrMapKey) && $attrsOrMapKey) {
            $mapKey        = $attrsOrMapKey;
            $attrsOrMapKey = [];
        }
        
        $list  = [];
        $class = self::getReflectionClass($objectOrClass);
        foreach ($class->getReflectionConstants() as $constant) {
            $constantClass = self::getAbsoluteClassname($constant->getDeclaringClass()->getName());
            if ($constantClass != $classname) {
                continue;
            }
            
            $key = $constant->getName();
            if ($prefix && 0 !== strpos($key, $prefix)) {
                continue;
            }
            
            $value        = $constant->getValue();
            $list[$value] = static::extractDocAttrs($class, $key, $value, $constant->getDocComment(), $attrsOrMapKey, $mapKey);
        }
        
        return self::$constAttrs[$classname] = $list;
    }
    
    
    /**
     * 提取文档中的 @属性
     * @param ReflectionClass $class ReflectionClass
     * @param string          $key 属性名称
     * @param mixed           $value 属性值
     * @param string|null     $doc 属性文档
     * @param array|string    $attrsOrMapKey 其它属性或数据映射
     * @param null            $mapKey 指定某个属性的值作为值
     * @return mixed
     */
    public static function extractDocAttrs(ReflectionClass $class, string $key, $value, $doc = null, $attrsOrMapKey = [], $mapKey = null)
    {
        $doc  = static::encodeDocSpecialStr($doc ?: '');
        $name = '';
        $item = [];
        if (false === strpos($doc, PHP_EOL)) {
            if (preg_match('/\*\*\s@var(.+)\s(.+)\*\//i', $doc, $match)) {
                $type = trim($match[1]);
                $name = trim($match[2]);
            } else {
                $type = 'mixed';
            }
            $item[self::ATTR_VAR] = $type;
        } else {
            // 取出名称
            if (preg_match('/\/\*\*(.*?)(@.*?)\*\//is', $doc, $match)) {
                $name = trim(preg_replace('/\s?\*/', '', $match[1]));
                
                // 取出 @name
                foreach (preg_split('/\*\s+@/', $match[2]) as $vo) {
                    $vo = preg_replace('/\s?\*|@/', '', $vo);
                    
                    // 匹配 "type value"
                    if (preg_match('/(.*?)\s(.*)/is', $vo, $voMatch)) {
                        $k = trim($voMatch[1]);
                        $v = trim(preg_replace('/\n\s*/is', PHP_EOL, $voMatch[2]));
                        if (isset($item[$k]) && strtolower($k) !== self::ATTR_VAR) {
                            if (!is_array($item[$k])) {
                                $item[$k] = [$item[$k]];
                            }
                            $item[$k][] = $v;
                        } else {
                            $item[$k] = $v;
                        }
                    }
                }
            }
        }
        
        // 解析属性
        foreach ($attrsOrMapKey as $attr => $type) {
            if (is_numeric($attr)) {
                $attr = $type;
                $type = '';
            }
            
            if (strtolower($attr) === self::ATTR_VAR) {
                continue;
            }
            
            $item[$attr] = static::parseValue($class, $item[$attr] ?? null, $type);
        }
        
        // 解析类型
        $var = $item[self::ATTR_VAR] ?? 'mixed';
        if (!isset(self::PRIMITIVE_TYPES[$var])) {
            if (substr($var, 0, 1) !== '\\') {
                $var = self::parseValue($class, $var, self::CAST_CLASS);
            }
        }
        
        $item[self::ATTR_VAR]   = $var;
        $item[self::ATTR_NAME]  = static::decodeDocSpecialStr($name);
        $item[self::ATTR_KEY]   = $key;
        $item[self::ATTR_VALUE] = $value;
        
        if ($mapKey) {
            if (is_string($mapKey)) {
                $item = $item[$mapKey] ?? null;
            } elseif (is_callable($mapKey)) {
                $item = call_user_func_array($mapKey, [$item]);
            }
        }
        
        return $item;
    }
    
    
    /**
     * 解析内容
     * @param ReflectionClass $class ReflectionClass
     * @param mixed           $value 内容
     * @param string          $type 要解析的类型
     * @return mixed
     */
    public static function parseValue(ReflectionClass $class, $value, string $type = self::CAST_STRING)
    {
        if (is_array($value)) {
            foreach ($value as $i => $v) {
                $value[$i] = static::parseValue($class, $v, $type);
            }
            
            return $value;
        }
        
        switch (strtolower($type)) {
            case self::CAST_JSON:
                return json_decode($value, true) ?: [];
            case self::CAST_INT:
                return (int) $value;
            case self::CAST_FLOAT:
                return (float) $value;
            case self::CAST_BOOL:
                return TransHelper::toBool($value);
            case self::CAST_CLASS:
                $value = (string) $value;
                if (substr($value, 0, 1) !== '\\') {
                    $value = static::extractClassname($value, $class);
                }
                
                return $value;
            case self::CAST_STRING:
            default:
                return (string) $value;
        }
    }
    
    
    /**
     * 文件中的use声明解析器
     * @return UseStatementParser
     */
    public static function useStatementParser() : UseStatementParser
    {
        if (!static::$useStatementParser) {
            static::$useStatementParser = new UseStatementParser();
        }
        
        return static::$useStatementParser;
    }
    
    
    /**
     * 尝试基于类和成员上下文解析所提供类
     * @param string          $classname 类名
     * @param ReflectionClass $class ReflectionClass
     * @param Reflector|null  $member Reflector
     * @return string|null 类型的完全名，如果无法解析则为null
     */
    public static function extractClassname(string $classname, ReflectionClass $class, ?Reflector $member = null) : ?string
    {
        $alias        = ($pos = strpos($classname, '\\')) === false ? $classname : substr($classname, 0, $pos);
        $loweredAlias = strtolower($alias);
        $uses         = static::useStatementParser()->parseUseStatements($class);
        $name         = null;
        
        if (isset($uses[$loweredAlias])) {
            if ($pos !== false) {
                $name = $uses[$loweredAlias] . substr($classname, $pos);
            } else {
                $name = $uses[$loweredAlias];
            }
        } elseif (static::classExists($class->getNamespaceName() . '\\' . $classname)) {
            $name = $class->getNamespaceName() . '\\' . $classname;
        } elseif (isset($uses['__NAMESPACE__']) && static::classExists($uses['__NAMESPACE__'] . '\\' . $classname)) {
            $name = $uses['__NAMESPACE__'] . '\\' . $classname;
        } elseif (static::classExists($classname)) {
            $name = $classname;
        } elseif ($member) {
            $name = static::extractClassnameByTraits($classname, $class, $member);
        }
        
        return self::getAbsoluteClassname($name);
    }
    
    
    /**
     * 搜索的特征解析所提供类
     * @param string          $classname 类名
     * @param ReflectionClass $class ReflectionClass
     * @param Reflector       $member Reflector
     * @return string|null 类型的全名，如果无法解析则为null
     */
    public static function extractClassnameByTraits(string $classname, ReflectionClass $class, Reflector $member) : ?string
    {
        /** @var ReflectionClass[] $traits */
        $traits = [];
        
        // 获取类及其父类的特征
        while ($class) {
            $traits = array_merge($traits, $class->getTraits());
            $class  = $class->getParentClass();
        }
        
        foreach ($traits as $trait) {
            if ($member instanceof ReflectionProperty && !$trait->hasProperty($member->name)) {
                continue;
            }
            if ($member instanceof ReflectionMethod && !$trait->hasMethod($member->name)) {
                continue;
            }
            if ($member instanceof ReflectionParameter && !$trait->hasMethod($member->getDeclaringFunction()->name)) {
                continue;
            }
            
            $resolvedType = static::extractClassname($classname, $trait, $member);
            
            if ($resolvedType) {
                return $resolvedType;
            }
        }
        
        return null;
    }
    
    
    /**
     * 判断类是否存在
     * @param string $class
     * @return bool
     */
    public static function classExists(string $class) : bool
    {
        return class_exists($class) || interface_exists($class);
    }
    
    
    /**
     * 获取类反射对象
     * @param string|object $objectOrClass 类名或对象
     * @return ReflectionClass
     */
    public static function getReflectionClass($objectOrClass) : ReflectionClass
    {
        if (is_object($objectOrClass)) {
            $objectOrClass = get_class($objectOrClass);
        }
        
        if (!isset(self::$reflectionClass[$objectOrClass])) {
            try {
                self::$reflectionClass[$objectOrClass] = new ReflectionClass($objectOrClass);
            } catch (Throwable $e) {
                throw new RuntimeException($e->getMessage());
            }
        }
        
        return self::$reflectionClass[$objectOrClass];
    }
    
    
    /**
     * 通过ReflectionProperty获取值
     * @param object             $object 对象
     * @param ReflectionProperty $property ReflectionProperty
     * @return mixed
     */
    public static function getPropertyValue(object $object, ReflectionProperty $property)
    {
        if (!$property->isPublic()) {
            $property->setAccessible(true);
        }
        
        return $property->getValue($object);
    }
    
    
    /**
     * 通过Object获取属性值
     * @param object $object 类对象
     * @param string $name 属性名称
     * @return mixed
     */
    public static function getPropertyValueByObject(object $object, string $name)
    {
        try {
            return self::getPropertyValue($object, self::getReflectionClass($object)->getProperty($name));
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
    
    
    /**
     * 通过Object设置属性值
     * @param object $object 类对象
     * @param string $name 属性名称
     */
    public static function setPropertyValueByObject(object $object, string $name, $value)
    {
        try {
            self::setPropertyValue($object, self::getReflectionClass($object)->getProperty($name), $value);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
    
    
    /**
     * 通过ReflectionProperty设置值
     * @param object             $object 对象
     * @param ReflectionProperty $property ReflectionProperty
     * @param mixed              $value 值
     * @return void
     */
    public static function setPropertyValue(object $object, ReflectionProperty $property, $value)
    {
        if (!$property->isPublic()) {
            $property->setAccessible(true);
        }
        
        $property->setValue($object, $value);
    }
    
    
    /**
     * 获取完整类名称
     * @param string|null $classname
     * @return string|null
     */
    public static function getAbsoluteClassname($classname) : ?string
    {
        if (!$classname) {
            return $classname;
        }
        if (is_object($classname)) {
            $classname = get_class($classname);
        }
        
        return '\\' . ltrim($classname, '\\');
    }
    
    
    /**
     * 获取最顶级的ReflectionClass
     * @param ReflectionClass $class
     * @return ReflectionClass
     */
    public static function getTopReflectionClass(ReflectionClass $class) : ReflectionClass
    {
        if (!$parent = $class->getParentClass()) {
            return $class;
        }
        
        return self::getTopReflectionClass($parent);
    }
    
    
    /**
     * 通过Object反射调用方法
     * @param object $object 对象
     * @param string $name 方法名称
     * @param array  $args 方法参数
     * @return mixed
     */
    public static function invokeMethodByObject(object $object, string $name, array $args = [])
    {
        $class = self::getReflectionClass($object);
        if (!$class->hasMethod($name)) {
            throw new MethodNotFoundException($object, $name);
        }
        
        try {
            $method = $class->getMethod($name);
            if (!$method->isPublic()) {
                $method->setAccessible(true);
            }
            
            return $method->invokeArgs($object, $args);
        } catch (ReflectionException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}