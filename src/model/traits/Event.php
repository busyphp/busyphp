<?php
declare(strict_types = 1);

namespace BusyPHP\model\traits;

use think\facade\Event as ThinkEvent;

/**
 * 模型事件特征类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/10/5 9:53 PM Event.php $
 */
trait Event
{
    /**
     * 监听事件
     * @param string   $event 事件名
     * @param callable $callback 回调方法
     * @param bool     $first 是否优先执行
     * @return $this
     */
    public function listen(string $event, callable $callback, bool $first = false)
    {
        ThinkEvent::listen($event, $callback, $first);
        
        return $this;
    }
    
    
    /**
     * 触发事件
     * @param string $event 事件名
     * @param mixed  $params 参数
     * @param bool   $once 只获取一个有效返回值
     * @return mixed
     */
    protected function trigger($event, $params = null, bool $once = false)
    {
        if (is_object($event)) {
            $once   = (bool) $params;
            $params = null;
        }
        
        return ThinkEvent::trigger($event, $params, $once);
    }
}