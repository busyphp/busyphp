<?php
declare(strict_types = 1);

namespace BusyPHP;

use FilesystemIterator;

/**
 * 基本缓存管理类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/31 下午10:56 上午 Cache.php $
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
        return cache(self::name($dir, $name), $value, $expire);
    }
    
    
    /**
     * 获取缓存
     * @param mixed  $dir 缓存路径
     * @param string $name 缓存名称
     * @return mixed
     */
    public static function get($dir, $name)
    {
        return cache(self::name($dir, $name));
    }
    
    
    /**
     * 删除缓存
     * @param mixed  $dir 缓存路径
     * @param string $name 缓存名称
     * @return bool
     */
    public static function delete($dir, $name)
    {
        return cache(self::name($dir, $name), null);
    }
    
    
    /**
     * 清理缓存
     * @param mixed $dir 缓存路径
     * @return void
     */
    public static function clear($dir) : void
    {
        switch (strtolower(config('cache.default'))) {
            // 文件缓存方式
            case 'file':
                self::rmdir(App::getInstance()->getRuntimeCachePath(self::name($dir, '')));
            break;
            
            // Redis缓存方式
            case 'redis':
                $prefix = config('cache.stores.redis.prefix');
                $name   = $prefix . self::name($dir, '');
                $redis  = cache()->store('redis')->handler();
                $keys   = $redis->keys($name . '*');
                $redis->del($keys);
            break;
            
            // 其他缓存方式
            default:
                cache()->clear();
        }
    }
    
    
    /**
     * 获取缓存名称
     * @param mixed  $dir 缓存路径
     * @param string $name 缓存名称
     * @param string $driver 驱动名称，设置后通过该驱动获取前缀配置并加上前缀
     * @return string
     */
    public static function name($dir, $name, $driver = null)
    {
        if (is_object($dir)) {
            $dir = get_class($dir);
        }
        
        $dir  = trim(str_replace('\\', '/', $dir), '/');
        $name = $dir ? $dir . '/' . $name : $name;
        
        if ($driver) {
            return config("cache.stores.{$driver}.prefix") . $name;
        }
        
        return $name;
    }
    
    
    /**
     * 删除文件夹
     * @param $dirname
     * @return bool
     */
    protected static function rmdir($dirname)
    {
        if (!is_dir($dirname)) {
            return false;
        }
        
        $items = new FilesystemIterator($dirname);
        
        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                self::rmdir($item->getPathname());
            } else {
                self::unlink($item->getPathname());
            }
        }
        
        @rmdir($dirname);
        
        return true;
    }
    
    
    /**
     * 判断文件是否存在后，删除
     * @access private
     * @param string $path
     * @return bool
     */
    protected static function unlink(string $path) : bool
    {
        try {
            return is_file($path) && unlink($path);
        } catch (\Exception $e) {
            return false;
        }
    }
}