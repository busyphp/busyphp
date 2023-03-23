<?php
declare(strict_types = 1);

namespace BusyPHP\traits;

use BusyPHP\interfaces\ContainerInterface;
use think\Container;

/**
 * 容器定义特征类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/22 19:16 ContainerDefine.php $
 */
trait ContainerDefine
{
    /**
     * @var array<string,string>
     */
    private static $defineMap = [];
    
    
    /**
     * 实例化容器
     * @param array $vars 参数
     * @param bool  $newInstance 是否单例
     * @return static
     */
    final protected static function makeContainer(array $vars = [], bool $newInstance = false) : static
    {
        return Container::getInstance()->make(self::getDefineContainer(), $vars, $newInstance);
    }
    
    
    /**
     * 获取容器真实类名
     * @return class-string<static>|static
     */
    final public static function class() : string
    {
        return Container::getInstance()->getAlias(self::getDefineContainer());
    }
    
    
    /**
     * 获取定义的容器接口
     * @return class-string<static>
     */
    final public static function getDefineContainer() : string
    {
        if (!isset(self::$defineMap[static::class])) {
            if (is_subclass_of(static::class, ContainerInterface::class)) {
                $trueClass = static::defineContainer();
            } else {
                $selfClass   = self::class;
                $parentClass = get_parent_class(static::class);
                $trueClass   = static::class;
                do {
                    if ($parentClass == $selfClass) {
                        break;
                    }
                    $trueClass   = $parentClass;
                    $parentClass = get_parent_class($parentClass);
                } while (true);
            }
            
            self::$defineMap[static::class] = $trueClass;
        }
        
        return self::$defineMap[static::class];
    }
}