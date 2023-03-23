<?php
declare(strict_types = 1);

namespace BusyPHP\helper;

use think\facade\Filesystem;
use think\filesystem\Driver;

/**
 * Filesystem辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/25 22:01 FilesystemHelper.php $
 */
class FilesystemHelper
{
    /** @var string 本地系统文件磁盘标识 */
    const STORAGE_PUBLIC = 'public';
    
    /** @var string 本地临时文件磁盘标识 */
    const STORAGE_LOCAL = 'local';
    
    
    /**
     * 获取本地公共文件驱动
     * @return Driver
     */
    public static function public() : Driver
    {
        return Filesystem::disk(self::STORAGE_PUBLIC);
    }
    
    
    /**
     * 获取本地临时文件驱动
     * @return Driver
     */
    public static function runtime() : Driver
    {
        return Filesystem::disk(self::STORAGE_LOCAL);
    }
}