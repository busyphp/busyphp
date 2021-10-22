<?php

namespace BusyPHP\contract\abstracts;

use think\facade\Event;

/**
 * 事件订阅基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午11:54 上午 Subscribe.php $
 * @method void subscribe() 手动订阅
 */
abstract class Subscribe
{
    /**
     * 事件前缀
     * @var string
     */
    protected static $prefix = '';
    
    /**
     * 事件前缀，系统会覆盖该参数，不要手动指定
     * @var string
     */
    protected $eventPrefix;
    
    
    /**
     * Subscribe constructor.
     */
    public function __construct()
    {
        $prefix = static::$prefix;
        if (!is_null($prefix)) {
            $prefix = trim($prefix);
            $prefix = $prefix ?: static::class;
        } else {
            $prefix = '';
        }
        static::$prefix = $prefix;
        
        $this->eventPrefix = $prefix;
    }
    
    
    /**
     * 触发订阅事件
     * @param string $event 事件名称
     * @param mixed  $params 事件参数
     * @return mixed
     */
    public static function trigger(string $event, $params = null)
    {
        return Event::trigger(static::$prefix . $event, $params, true);
    }
    
    
    /**
     * 检查是否注册了订阅
     * @param string $event
     * @return bool
     */
    public static function has(string $event)
    {
        return Event::hasListener(static::$prefix . $event);
    }
}