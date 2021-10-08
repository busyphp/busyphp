<?php
declare (strict_types = 1);

namespace BusyPHP\model;

use BusyPHP\helper\util\Str;

/**
 * Map
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/8 下午上午10:18 Map.php $
 */
class Map extends Field
{
    public function __get($name)
    {
        if (isset($this->{Str::camel($name)})) {
            return parent::__get($name);
        }
        
        return null;
    }
    
    
    /**
     * 获取值
     * @param string $key 键名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->{Str::camel($key)} ?? $default;
    }
    
    
    /**
     * 设置值
     * @param string $key 键名
     * @param mixed  $value 键值
     */
    public function set($key, $value)
    {
        $this->{Str::camel($key)} = $value;
    }
    
    
    /**
     * 删除键
     * @param string ...$keys
     */
    public function remove(...$keys)
    {
        foreach ($keys as $key) {
            unset($this->{Str::camel($key)});
        }
    }
}