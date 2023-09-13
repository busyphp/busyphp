<?php
declare(strict_types = 1);

namespace BusyPHP;

use BusyPHP\initializer\RegisterService;
use think\initializer\BootService;
use think\initializer\Error;

/**
 * App
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/16 下午11:29 上午 App.php $
 * @method static static getInstance() 获取单例
 */
class App extends \think\App
{
    /**
     * 框架版本号
     * @var string
     */
    private $frameworkVersion = '7.0.6';
    
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
     * 构造
     * App constructor.
     * @param string $rootPath
     */
    public function __construct(string $rootPath = '')
    {
        $this->bind([
            \think\App::class => App::class,
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
     * 获取网站入口根目录路径
     * @param string $path
     * @return string
     */
    public function getPublicPath(string $path = '') : string
    {
        $dir  = str_replace('/', DIRECTORY_SEPARATOR, $this->config->get('app.public_dir', '') ?: 'public');
        $root = $this->getRootPath() . trim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
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
        
        return App::getInstance()->getPublicPath($url);
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