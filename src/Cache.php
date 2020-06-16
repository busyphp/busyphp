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
        static $type;
        if (!isset($type)) {
            $type = strtolower(config('cache.default'));
        }
        
        switch ($type) {
            // 文件缓存方式
            case 'file':
                self::rmdir(App::runtimeCachePath() . self::name($dir, ''));
            break;
            
            // Redis缓存方式
            case 'redis':
                $name = self::name($dir, '');
                if ($name) {
                    $redis   = cache()->handler();
                    $pattern = self::name($dir, '') . '*';
                    $keys    = $redis->keys($pattern);
                    $redis->del($keys);
                } else {
                    cache()->clear();
                }
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
     * @return string
     */
    public static function name($dir, $name)
    {
        if (is_object($dir)) {
            $dir = get_class($dir);
        }
        
        if (substr($dir, 0, 7) === 'BusyPHP') {
            $dir = 'core/' . trim(str_replace('\\', '/', substr($dir, 7)), '/');
        } elseif (substr($dir, 0, 3) === 'app') {
            $dir = 'app/' . trim(str_replace('\\', '/', substr($dir, 3)), '/');
        }
        
        return (!empty($dir) ? trim($dir, '/') . '/' : '') . $name;
    }
    
    
    /**
     * 删除文件夹
     * @param $dirname
     * @return bool
     */
    private static function rmdir($dirname)
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
    private static function unlink(string $path) : bool
    {
        try {
            return is_file($path) && unlink($path);
        } catch (\Exception $e) {
            return false;
        }
    }
}