<?php

namespace BusyPHP\abstracts;

use think\Container;
use think\facade\Event;

/**
 * 事件订阅基本类，子类使用on开头的公共方法会被自动订阅
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/12/18 下午11:54 上午 BaseEventSubscribe.php $
 */
abstract class BaseEventSubscribe
{
    /**
     * 事件前缀
     * @internal 由 {@see \think\Event::observe()} 进行调用
     * @var string
     */
    protected $eventPrefix;
    
    
    /**
     * 获取单例
     * @return static
     */
    public static function getInstance() : self
    {
        return Container::getInstance()->make(static::class);
    }
    
    
    public function __construct()
    {
        $this->eventPrefix = $this->registerClassName();
    }
    
    
    /**
     * 注册类名
     * @return string
     */
    protected function registerClassName() : string
    {
        return static::class;
    }
    
    
    /**
     * 触发订阅事件
     * @param string $event 事件名称
     * @param mixed  $params 事件参数
     * @param bool   $once 只获取一个有效返回值
     * @return mixed
     */
    public function trigger(string $event, $params = null, bool $once = false)
    {
        return Event::trigger(sprintf('%s%s', $this->eventPrefix, $event), $params, $once);
    }
    
    
    /**
     * 触发事件(只获取一个有效返回值)
     * @param string $event 事件名称
     * @param mixed  $params 事件参数
     * @return mixed
     */
    public function until(string $event, $params = null)
    {
        return $this->trigger($event, $params, true);
    }
    
    
    /**
     * 检查是否注册了订阅
     * @param string $event
     * @return bool
     */
    public function has(string $event) : bool
    {
        return Event::hasListener(sprintf('%s%s', $this->eventPrefix, $event));
    }
    
    
    public function __call($name, $arguments)
    {
        if (count($arguments) == 1) {
            $arguments = $arguments[0];
        }
        
        return $this->until(ucfirst($name), $arguments);
    }
}