<?php
declare(strict_types = 1);

namespace BusyPHP;

use BusyPHP\contract\structs\items\AppListItem;
use BusyPHP\initializer\RegisterService;
use think\initializer\BootService;
use think\initializer\Error;

/**
 * App
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/16 下午11:29 上午 App.php $
 * @property Request $request
 * @property Db      $db
 */
class App extends \think\App
{
    /**
     * 框架版本号
     * @var string
     */
    private $frameworkVersion = '6.0.0';
    
    /**
     * 框架名称
     * @var string
     */
    private $frameworkName = 'BusyPHP快速开发框架';
    
    /**
     * 应用初始化器
     * @var array
     */
    protected $initializers = [
        Error::class,
        RegisterService::class,
        BootService::class,
    ];
    
    
    /**
     * 单例
     * @return static
     */
    public static function init() : self
    {
        return static::getInstance();
    }
    
    
    /**
     * 构造
     * App constructor.
     * @param string $rootPath
     */
    public function __construct(string $rootPath = '')
    {
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
        include_once $this->getFrameworkPath('helper/helper.php');
        
        parent::load();
        
        // 加载自定义全局配置文件
        $this->config->load($this->getRuntimeConfigPath('config.php'), 'user');
    }
    
    
    /**
     * 获取框架名称
     * @return string
     */
    public function getFrameworkName() : string
    {
        return $this->frameworkName;
    }
    
    
    /**
     * 获取框架版本号
     * @return string
     */
    public function getFrameworkVersion() : string
    {
        return $this->frameworkVersion;
    }
    
    
    /**
     * 获取框架入口目录路径
     * @param string $path
     * @return string
     */
    public function getFrameworkPath(string $path = '') : string
    {
        $root = __DIR__ . DIRECTORY_SEPARATOR;
        $path = $this->parsePath($path);
        
        return $path ? $root . $path : $root;
    }
    
    
    /**
     * 获取应用目录名称
     * @return string
     */
    public function getDirName() : string
    {
        return pathinfo($this->getAppPath(), PATHINFO_FILENAME);
    }
    
    
    /**
     * 获取应用集合
     * @return AppListItem[]
     */
    public function getList() : array
    {
        $basePath = $this->getBasePath();
        $list     = [];
        $maps     = ['admin' => '后台管理', 'home' => '前端网站'];
        foreach (scandir($basePath) as $value) {
            if (!is_dir($path = $basePath . $value . DIRECTORY_SEPARATOR) || $value === '.' || $value === '..') {
                continue;
            }
            
            $name   = '';
            $config = [];
            if (is_file($configFile = $path . 'config' . DIRECTORY_SEPARATOR . 'app.php')) {
                $config = include $configFile;
                $config = is_array($config) ? $config : [];
                $name   = $config['app_name'] ?? '';
            }
            if (!$name) {
                $name = ($maps[$value] ?? '') ?: $value;
            }
            
            $item         = new AppListItem();
            $item->path   = $path;
            $item->dir    = $value;
            $item->name   = $name;
            $item->config = $config;
            
            $list[] = $item;
        }
        
        return $list;
    }
    
    
    /**
     * 获取网站入口根目录路径
     * @param string $path
     * @return string
     */
    public function getPublicPath(string $path = '') : string
    {
        $root = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
        $root = rtrim(dirname($root), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $path = $this->parsePath($path);
        
        return $path ? $root . $path : $root;
    }
    
    
    /**
     * 获取核心目录路径
     * @param string $path
     * @return string
     */
    public function getCorePath(string $path = '') : string
    {
        $root = $this->getRootPath();
        $path = $this->parsePath($path);
        
        return $path ? $root . $path : $root;
    }
    
    
    /**
     * 获取运行目录路径
     * @param string $path
     * @return string
     */
    public function getRuntimeRootPath(string $path = '') : string
    {
        $root = $this->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR;
        $path = $this->parsePath($path);
        
        return $path ? $root . $path : $root;
    }
    
    
    /**
     * 获取临时缓存基本路径
     * @param string $path
     * @return string
     */
    public function getRuntimeCachePath(string $path = '') : string
    {
        $root = $this->getRuntimeRootPath('cache/');
        $path = $this->parsePath($path);
        
        return $path ? $root . $path : $root;
    }
    
    
    /**
     * 获取临时配置基本路径
     * @param string $path
     * @return string
     */
    public function getRuntimeConfigPath(string $path = '') : string
    {
        $root = $this->getRuntimeRootPath('config/');
        $path = $this->parsePath($path);
        
        return $path ? $root . $path : $root;
    }
    
    
    /**
     * 将URL解析出真实的路径
     * @param string $url 地址
     * @return string
     */
    public static function urlToPath(string $url) : string
    {
        if (!$url) {
            return '';
        }
        
        return App::init()->getPublicPath($url);
    }
    
    
    /**
     * 解析路径
     * @param string $path
     * @return string
     */
    protected function parsePath(string $path) : string
    {
        $path = trim($path);
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}