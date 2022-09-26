<?php

namespace BusyPHP\app\admin\plugin;

use BusyPHP\app\admin\controller\common\FileController;
use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\helper\ArrayHelper;
use think\Container;
use think\facade\Filesystem;
use think\filesystem\Driver;

/**
 * 前端上传注入脚本基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/24 11:23 PM FrontUploadInjectScriptPlugin.php $
 */
abstract class FrontUploadInjectScriptPlugin
{
    /**
     * @var Driver
     */
    protected $driver;
    
    
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }
    
    
    /**
     * 向 {@see FileController::config()} 中注入脚本
     * @return string
     */
    abstract public function injectScript() : string;
    
    
    /**
     * 获取实例
     * @param string $disk
     * @return static
     */
    public static function getInstance(string $disk) : self
    {
        $config = Filesystem::getDiskConfig($disk);
        $class  = ArrayHelper::get($config, 'admin');
        if (!is_subclass_of($class, self::class)) {
            throw new ClassNotExtendsException($class, self::class);
        }
        
        return Container::getInstance()->make($class, [Filesystem::disk($disk)]);
    }
    
    
    /**
     * 获取默认实例
     * @return static
     */
    public static function defaultInstance() : self
    {
        return self::getInstance(StorageSetting::init()->getDisk());
    }
}