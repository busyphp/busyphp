<?php

namespace BusyPHP\app\admin\filesystem;

use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\exception\ClassNotFoundException;
use think\Container;
use think\facade\Config;
use think\facade\Filesystem;
use think\filesystem\Driver as FilesystemDriver;

/**
 * Filesystem管理驱动基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/29 2:12 PM Filesystem.php $
 */
abstract class Driver
{
    /**
     * @var FilesystemDriver
     */
    protected $driver;
    
    
    /**
     * @param FilesystemDriver $driver
     */
    public function __construct(FilesystemDriver $driver)
    {
        $this->driver = $driver;
    }
    
    
    /**
     * 向 {@see FileController::config()} 中注入上传脚本
     * @return string
     */
    abstract public function frontUploadInjectScript() : string;
    
    
    /**
     * 获取磁盘名称
     * @return string
     */
    abstract public function getName() : string;
    
    
    /**
     * 获取磁盘说明
     * @return string
     */
    abstract public function getDescription() : string;
    
    
    /**
     * 获取实例
     * @param string $disk
     * @return static
     */
    public static function getInstance(string $disk) : self
    {
        $class = Config::get(sprintf('filesystem.disks.%s.admin', $disk));
        if (!$class) {
            $class = sprintf('\\BusyPHP\\app\\admin\\filesystem\\driver\\%s', ucfirst(Config::get(sprintf('filesystem.disks.%s.type', $disk))));
        }
        
        if (!class_exists($class)) {
            throw new ClassNotFoundException($class);
        }
        
        if (!is_subclass_of($class, self::class)) {
            throw new ClassNotExtendsException($class, self::class);
        }
        
        return Container::getInstance()->make($class, [Filesystem::disk($disk)]);
    }
}