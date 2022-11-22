<?php

namespace BusyPHP\traits;

use think\Container as ThinkContainer;

/**
 * 容器特征类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/22 19:16 Container.php $
 */
trait Container
{
    /**
     * 定义容器接口
     * @return class-string<static>
     */
    protected static function defineContainer() : string
    {
        return static::class;
    }
    
    
    /**
     * 实例化容器
     * @param array $vars 参数
     * @param bool  $newInstance 是否单例
     * @return static
     */
    final protected static function makeContainer(array $vars = [], bool $newInstance = false) : self
    {
        return ThinkContainer::getInstance()->make(static::defineContainer(), $vars, $newInstance);
    }
    
    
    /**
     * 获取容器真实类名
     * @return class-string<static>|static
     */
    final public static function class() : string
    {
        return ThinkContainer::getInstance()->getAlias(static::defineContainer());
    }
    
    
    /**
     * 获取定义的容器接口
     * @return class-string<static>
     */
    final public static function getDefineContainer() : string
    {
        return static::defineContainer();
    }
    
    
    /**
     * 获取单例
     * @return static
     */
    public static function instance()
    {
        return self::makeContainer();
    }
    
    
    /**
     * 获取实例
     * @return static
     */
    public static function init()
    {
        return self::makeContainer([], true);
    }
}