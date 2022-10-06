<?php
declare(strict_types = 1);

namespace BusyPHP\traits;

use BusyPHP\helper\CacheHelper;
use DateInterval;
use DateTimeInterface;

/**
 * 缓存特征类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/10/5 8:29 PM Cache.php $
 */
trait Cache
{
    /**
     * 获取静态缓存
     * @param string $name 缓存名称
     * @return mixed
     */
    public function getCache($name)
    {
        return CacheHelper::get(static::class, $name);
    }
    
    
    /**
     * 设置静态缓存
     * @param string                                  $name 缓存名称
     * @param mixed                                   $value 缓存内容
     * @param int|DateTimeInterface|DateInterval|null $expire 有效时间（秒）
     * @return bool
     */
    public function setCache(string $name, $value, $expire = 600) : bool
    {
        return CacheHelper::set(static::class, $name, $value, $expire);
    }
    
    
    /**
     * 移除静态缓存
     * @param string $name 缓存名称
     * @return bool
     */
    public function deleteCache(string $name = '') : bool
    {
        return CacheHelper::delete(static::class, $name);
    }
    
    
    /**
     * 清理静态缓存
     */
    public function clearCache() : bool
    {
        return CacheHelper::clear(static::class);
    }
}