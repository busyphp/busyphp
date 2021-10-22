<?php
declare (strict_types = 1);

namespace BusyPHP\model;

use BusyPHP\helper\StringHelper;

/**
 * Map
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/8 下午上午10:18 Map.php $
 */
class Map extends Field
{
    public function __get($name)
    {
        if (isset($this->{StringHelper::camel($name)})) {
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
        return $this->{StringHelper::camel($key)} ?? $default;
    }
    
    
    /**
     * 设置值
     * @param string $key 键名
     * @param mixed  $value 键值
     */
    public function set($key, $value)
    {
        $this->{StringHelper::camel($key)} = $value;
    }
    
    
    /**
     * 删除键
     * @param string ...$keys
     */
    public function remove(...$keys)
    {
        foreach ($keys as $key) {
            unset($this->{StringHelper::camel($key)});
        }
    }
}