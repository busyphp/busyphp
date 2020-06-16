<?php
declare(strict_types = 1);

namespace BusyPHP;

use BusyPHP\initializer\Error;
use BusyPHP\initializer\RegisterService;
use think\initializer\BootService;

/**
 * App
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/16 下午11:29 上午 App.php $
 */
class App extends \think\App
{
    /**
     * 版本号
     * @var string
     */
    public static $busyVersion = '3.0.1';
    
    /**
     * 框架名称
     * @var string
     */
    public static $busyName = 'BusyPHP快速开发框架';
    
    /**
     * BusyPHP路径
     * @var string
     */
    protected static $busyPath;
    
    /**
     * public路径
     * @var string
     */
    protected static $publicPath;
    
    protected        $initializers = [
        Error::class,
        RegisterService::class,
        BootService::class,
    ];
    
    
    public function __construct(string $rootPath = '')
    {
        spl_autoload_register([$this, 'autoload']);
        
        $this->bind([
            \think\App::class              => App::class,
            \think\Request::class          => Request::class,
            \think\exception\Handle::class => Handle::class,
            \think\Db::class               => Db::class
        ]);
        
        parent::__construct($rootPath);
    }
    
    
    protected function load() : void
    {
        // 加载助手函数
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . 'helper.php';
        
        parent::load();
        
        // 加载自定义全局配置文件
        $this->app->config->load(self::runtimeConfigPath() . 'config.php', 'user');
    }
    
    
    /**
     * 自动导入解析
     * @param $class
     */
    public function autoload($class)
    {
        // 判断前缀不是系统核心命名空间则不解析
        if (substr($class, 0, 7) !== 'BusyPHP') {
            return;
        }
        
        // 子类规则 Class_Child
        // 判断没有包含子类则不解析
        if (false === $index = strpos($class, '_')) {
            return;
        }
        
        $class = ltrim(substr($class, 0, -(strlen($class) - $index)), '\\');
        $class = substr($class, 8);
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $class = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . $class . '.php';
        if (!is_file($class)) {
            return;
        }
        
        require_once $class;
    }
    
    
    /**
     * 获取BusyPHP入口目录路径
     * @param string $path
     * @return string
     */
    public static function getBusyPath($path = '')
    {
        if (!isset(static::$busyPath)) {
            static::$busyPath = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        }
        
        return static::$busyPath . ($path ? $path . DIRECTORY_SEPARATOR : '');
    }
    
    
    /**
     * 获取网站入口根目录路径
     * @param string $path
     * @return string
     */
    public static function getPublicPath($path = '') : string
    {
        if (!isset(static::$publicPath)) {
            $file               = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
            static::$publicPath = rtrim(dirname($file), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }
        
        return static::$publicPath . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
    
    
    /**
     * 获取核心目录路径
     * @param string $path
     * @return string
     */
    public static function corePath($path = '')
    {
        return root_path('core') . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
    
    
    /**
     * 获取运行目录路径
     * @param string $path
     * @return string
     */
    public static function runtimePath($path = '')
    {
        return root_path('runtime') . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
    
    
    /**
     * 获取临时缓存基本路径
     * @param string $path
     * @return string
     */
    public static function runtimeCachePath($path = '') : string
    {
        return static::runtimePath('cache') . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
    
    
    /**
     * 获取临时配置基本路径
     * @param string $path
     * @return string
     */
    public static function runtimeConfigPath($path = '') : string
    {
        return static::runtimePath('config') . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
    
    
    /**
     * 获取临时文件基本路径
     * @param string $path
     * @return string
     */
    public static function runtimeUploadPath($path = '') : string
    {
        return static::runtimePath('upload') . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
    
    
    /**
     * 将URL解析出真实的路径
     * @param string $url 地址
     * @return string
     */
    public static function urlToPath($url)
    {
        return static::getPublicPath() . ltrim($url, '/');
    }
    
    
    /**
     * 获取框架名称
     * @return string
     */
    public function getBusyName() : string
    {
        return self::$busyName;
    }
    
    
    /**
     * 获取框架版本号
     * @return string
     */
    public function getBusyVersion() : string
    {
        return self::$busyVersion;
    }
}