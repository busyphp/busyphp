<?php
declare(strict_types = 1);

namespace BusyPHP\helper;

use DateInterval;
use DateTimeInterface;
use think\facade\Cache;

/**
 * 缓存辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/29 8:42 PM CacheHelper.php $
 */
class CacheHelper
{
    /**
     * 写入缓存
     * @param object|string                           $tag 缓存标签
     * @param string                                  $name 缓存名称
     * @param mixed                                   $value 缓存内容
     * @param int|DateTimeInterface|DateInterval|null $expire 有效时间（秒）
     * @return bool
     */
    public static function set(object|string $tag, string $name, mixed $value, int|DateTimeInterface|DateInterval|null $expire = null) : bool
    {
        $tag = static::tag($tag);
        
        return Cache::tag($tag)->set(static::name($tag, $name), $value, $expire);
    }
    
    
    /**
     * 如果不存在则写入缓存
     * @param object|string                           $tag 缓存标签
     * @param string                                  $name 缓存名称
     * @param mixed                                   $value 缓存内容
     * @param int|DateTimeInterface|DateInterval|null $expire 有效时间（秒）
     * @return mixed
     */
    public static function remember(object|string $tag, string $name, mixed $value, int|DateTimeInterface|DateInterval|null $expire = null) : mixed
    {
        $tag = static::tag($tag);
        
        return Cache::tag($tag)->remember(static::name($tag, $name), $value, $expire);
    }
    
    
    /**
     * 追加缓存名称到标签
     * @param object|string $tag 缓存标签
     * @param string        $name 缓存名称
     * @return void
     */
    public static function append(object|string $tag, string $name) : void
    {
        $tag = static::tag($tag);
        
        Cache::tag($tag)->append(static::name($tag, $name));
    }
    
    
    /**
     * 批量设置缓存
     * @param object|string                           $tag 缓存标签
     * @param array<string,mixed>                     $values 缓存集合
     * @param int|DateTimeInterface|DateInterval|null $expire 有效时间（秒）
     * @return bool
     */
    public static function setMultiple(object|string $tag, array $values, int|DateTimeInterface|DateInterval|null $expire = null) : bool
    {
        foreach ($values as $key => $val) {
            if (false === static::set($tag, $key, $expire)) {
                return false;
            }
        }
        
        return true;
    }
    
    
    /**
     * 批量获取缓存
     * @param object|string $tag 缓存标签
     * @param string[]      $keys 缓存名称集合
     * @param mixed         $default 默认值
     * @return iterable
     */
    public static function getMultiple(object|string $tag, array $keys, mixed $default = null) : iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = static::get($tag, $key, $default);
        }
        
        return $result;
    }
    
    
    /**
     * 批量删除缓存
     * @param object|string $tag 缓存标签
     * @param string[]      $keys 缓存名称集合
     * @return bool
     */
    public static function deleteMultiple(object|string $tag, array $keys) : bool
    {
        foreach ($keys as $key) {
            $result = static::delete($tag, $key);
            
            if (false === $result) {
                return false;
            }
        }
        
        return true;
    }
    
    
    /**
     * 获取缓存
     * @param object|string $tag 缓存标签
     * @param string        $name 缓存名称
     * @param mixed         $default 默认值
     * @return mixed
     */
    public static function get(object|string $tag, string $name, mixed $default = null) : mixed
    {
        return Cache::get(static::name(static::tag($tag), $name), $default);
    }
    
    
    /**
     * 判断缓存是否存在
     * @param object|string $tag 缓存标签
     * @param string        $name 缓存名称
     * @return bool
     */
    public static function has(object|string $tag, string $name) : bool
    {
        return Cache::has(static::name(static::tag($tag), $name));
    }
    
    
    /**
     * 删除缓存
     * @param object|string $tag 缓存标签
     * @param string        $name 缓存名称
     * @return bool
     */
    public static function delete(object|string $tag, string $name) : bool
    {
        return Cache::delete(static::name(static::tag($tag), $name));
    }
    
    
    /**
     * 清理缓存
     * @param object|string $tag 缓存标签
     * @return bool
     */
    public static function clear(object|string $tag) : bool
    {
        return Cache::tag(static::tag($tag))->clear();
    }
    
    
    /**
     * 清空缓存
     * @return bool
     */
    public static function clean() : bool
    {
        return Cache::clear();
    }
    
    
    /**
     * 获取缓存名称
     * @param object|string $tag 缓存标签
     * @param string        $name 缓存名称
     */
    public static function getCacheKey(object|string $tag, string $name) : string
    {
        return Cache::getCacheKey(static::name(static::tag($tag), $name));
    }
    
    
    /**
     * 获取标签名称
     * @param object|string $tag 缓存标签
     * @return string
     */
    public static function getTagKey(object|string $tag) : string
    {
        return Cache::getCacheKey(Cache::getTagKey(static::tag($tag)));
    }
    
    
    /**
     * 生成TAG
     * @param object|string $tag 缓存标签
     * @return string
     */
    protected static function tag(object|string $tag) : string
    {
        if (is_object($tag)) {
            $tag = get_class($tag);
        }
        
        return trim(str_replace('\\', '/', $tag), '/');
    }
    
    
    /**
     * 生成缓存名称
     * @param mixed  $tag 缓存标签
     * @param string $name 缓存名称
     * @return string
     */
    protected static function name(object|string $tag, string $name) : string
    {
        return $tag . '/' . $name;
    }
}