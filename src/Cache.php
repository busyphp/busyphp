<?php
declare(strict_types = 1);

namespace BusyPHP;

use BusyPHP\helper\CacheHelper;

/**
 * 基本缓存管理类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/31 下午10:56 上午 Cache.php $
 * @deprecated 请使用 {@see CacheHelper}
 */
class Cache
{
    /**
     * 设置缓存
     * @param mixed  $dir 缓存路径
     * @param string $name 缓存名称
     * @param mixed  $value 缓存值
     * @param int    $expire 过期时间，0不过期
     * @return bool
     */
    public static function set($dir, $name, $value, $expire = 0)
    {
        return CacheHelper::set($dir, $name, $value, $expire);
    }
    
    
    /**
     * 获取缓存
     * @param mixed  $dir 缓存路径
     * @param string $name 缓存名称
     * @return mixed
     */
    public static function get($dir, $name)
    {
        return CacheHelper::get($dir, $name);
    }
    
    
    /**
     * 删除缓存
     * @param mixed  $dir 缓存路径
     * @param string $name 缓存名称
     * @return bool
     */
    public static function delete($dir, $name)
    {
        return CacheHelper::delete($dir, $name);
    }
    
    
    /**
     * 清理缓存
     * @param mixed $dir 缓存路径
     * @return void
     */
    public static function clear($dir) : void
    {
        CacheHelper::clear($dir);
    }
}