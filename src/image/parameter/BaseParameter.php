<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\helper\StringHelper;
use ReflectionClass;
use ReflectionException;
use think\Container;

/**
 * 图片处理系统模板基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/9 7:37 PM ImageTemplate.php $
 */
abstract class BaseParameter
{
    /** @var string 模版名称 */
    protected static $parameterName = '';
    
    // +----------------------------------------------------
    // + 位置
    // +----------------------------------------------------
    /**
     * 上左
     * @var string
     */
    public const GRAVITY_TOP_LEFT = 'TL';
    
    /**
     * 上中
     * @var string
     */
    public const GRAVITY_TOP_CENTER = 'T';
    
    /**
     * 上右
     * @var string
     */
    public const GRAVITY_TOP_RIGHT = 'TR';
    
    /**
     * 左中
     * @var string
     */
    public const GRAVITY_LEFT_CENTER = 'L';
    
    /**
     * 中间
     * @var string
     */
    public const GRAVITY_CENTER = 'C';
    
    /**
     * 右中
     * @var string
     */
    public const GRAVITY_RIGHT_CENTER = 'R';
    
    /**
     * 下左
     * @var string
     */
    public const GRAVITY_BOTTOM_LEFT = 'BL';
    
    /**
     * 下中
     * @var string
     */
    public const GRAVITY_BOTTOM_CENTER = 'B';
    
    /**
     * 下右
     * @var string
     */
    public const GRAVITY_BOTTOM_RIGHT = 'BR';
    
    /**
     * 随机
     * @var string
     */
    public const GRAVITY_RAND = '*';
    
    /**
     * 默认颜色
     * @var string
     */
    public const DEFAULT_COLOR = '#FFFFFF';
    
    
    /**
     * 校验参数
     */
    public function verification()
    {
    }
    
    
    /**
     * 获取支持的位置集合
     * @param $format
     * @return array|string
     */
    public static function getGravitys($format = null)
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstMap(self::class, 'GRAVITY_', ClassHelper::ATTR_NAME), $format);
    }
    
    
    public static function __make()
    {
        return new static();
    }
    
    
    /**
     * 获取属性
     * @param BaseParameter|null $class
     * @return array
     * @throws ReflectionException
     */
    public static function getParameterAttrs($class = null) : array
    {
        $class   = $class ?: Container::getInstance()->make(static::class);
        $params  = [];
        $reflect = new ReflectionClass($class);
        foreach ($reflect->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }
            
            $name      = $property->getName();
            $getMethod = 'get' . ucfirst($name);
            $isMethod  = 'is' . ucfirst($name);
            $value     = '';
            $has       = false;
            if (method_exists($class, $getMethod)) {
                $has   = true;
                $value = $class->$getMethod();
            } elseif (method_exists($class, $isMethod)) {
                $has   = true;
                $value = $class->$isMethod();
            }
            
            if ($has) {
                $params[StringHelper::snake($name)] = is_bool($value) ? ($value ? 1 : 0) : $value;
            }
        }
        
        return $params;
    }
    
    
    /**
     * 获取参数模板类名前缀
     * @return string
     */
    public static function getParameterKey() : string
    {
        static $key;
        if (!isset($key)) {
            $key = StringHelper::snake(substr(class_basename(static::class), 0, -9));
        }
        
        return $key;
    }
    
    
    /**
     * 获取模版名称
     * @return string
     */
    public static function getParameterName() : string
    {
        return static::$parameterName;
    }
}