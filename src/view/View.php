<?php
declare(strict_types = 1);

namespace BusyPHP\view;

use BusyPHP\Request;
use think\App;
use think\Container;
use think\contract\TemplateHandlerInterface;
use think\helper\Str;
use think\template\exception\TemplateNotFoundException;

/**
 * 视图基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:27 下午 View.php $
 */
class View implements TemplateHandlerInterface
{
    /**
     * 模板引擎实例
     * @var Template
     */
    protected $template;
    
    /**
     * @var App
     */
    protected $app;
    
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * 模板引擎参数
     * @var array
     */
    protected $config = [
        // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
        'auto_rule'     => 1,
        // 视图目录名
        'view_dir_name' => 'view',
        // 模板起始路径
        'view_path'     => '',
        // 模板文件后缀
        'view_suffix'   => 'html',
        // 模板文件名分隔符
        'view_depr'     => DIRECTORY_SEPARATOR,
        // 是否开启模板编译缓存,设为false则每次都会重新编译
        'tpl_cache'     => false,
    ];
    
    
    public function __construct(App $app, array $config = [])
    {
        $this->app     = $app;
        $this->request = $app->request;
        $this->config  = array_merge($this->config, (array) $config);
        if (empty($this->config['cache_path'])) {
            $this->config['cache_path'] = $app->getRuntimePath() . 'temp' . DIRECTORY_SEPARATOR;
        }
        
        $this->setVarDefine();
        $this->template = new Template($this->config);
        $this->template->setCache($app->cache);
        $this->template->extend('$Think', function(array $vars) {
            $type  = strtoupper(trim(array_shift($vars)));
            $param = implode('.', $vars);
            
            switch ($type) {
                case 'CONST':
                    $parseStr = strtoupper($param);
                break;
                case 'CONFIG':
                    $parseStr = 'config(\'' . $param . '\')';
                break;
                case 'LANG':
                    $parseStr = 'lang(\'' . $param . '\')';
                break;
                case 'NOW':
                    $parseStr = "date('Y-m-d g:i a',time())";
                break;
                case 'LDELIM':
                    $parseStr = '\'' . ltrim($this->getConfig('tpl_begin'), '\\') . '\'';
                break;
                case 'RDELIM':
                    $parseStr = '\'' . ltrim($this->getConfig('tpl_end'), '\\') . '\'';
                break;
                default:
                    $parseStr = defined($type) ? $type : '\'\'';
            }
            
            return $parseStr;
        });
        
        $this->template->extend('$Request', function(array $vars) {
            // 获取Request请求对象参数
            $method = array_shift($vars);
            if (!empty($vars)) {
                $params = implode('.', $vars);
                if ('true' != $params) {
                    $params = '\'' . $params . '\'';
                }
            } else {
                $params = '';
            }
            
            return 'app(\'request\')->' . $method . '(' . $params . ')';
        });
    }
    
    
    /**
     * 设置一些常量
     */
    private function setVarDefine()
    {
        // 页面变量定义
        $this->config['tpl_replace_string'] = isset($this->config['tpl_replace_string']) ? $this->config['tpl_replace_string'] : [];
        
        // 网站根目录地址
        $this->config['tpl_replace_string']['__ROOT__'] = $this->request->getWebUrl();
        
        // 网站根目录地址
        $this->config['tpl_replace_string']['__APP__'] = $this->request->getAppUrl();
        
        // 当前URL，包含QueryString
        $this->config['tpl_replace_string']['__SELF__'] = $this->request->url();
        
        // 静态资源URL
        $this->config['tpl_replace_string']['__ASSETS__'] = $this->request->getWebAssetsUrl();
        
        // 当前域名
        $this->config['tpl_replace_string']['__DOMAIN__'] = $this->request->domain();
    }
    
    
    /**
     * 检测是否存在模板文件
     * @access public
     * @param string $template 模板文件或者模板规则
     * @return bool
     */
    public function exists(string $template) : bool
    {
        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
        }
        
        return is_file($template);
    }
    
    
    /**
     * 渲染模板文件
     * @access public
     * @param string $template 模板文件
     * @param array  $data 模板变量
     * @return void
     */
    public function fetch(string $template, array $data = []) : void
    {
        if (empty($this->config['view_path'])) {
            $view = $this->config['view_dir_name'];
            
            if (is_dir($this->app->getAppPath() . $view)) {
                $path = $this->app->getAppPath() . $view . DIRECTORY_SEPARATOR;
            } else {
                $appName = $this->app->http->getName();
                $path    = $this->app->getRootPath() . $view . DIRECTORY_SEPARATOR . ($appName ? $appName . DIRECTORY_SEPARATOR : '');
            }
            
            $this->config['view_path'] = $path;
            $this->template->view_path = $path;
        }
        
        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
        }
        
        // 模板不存在 抛出异常
        if (!is_file($template)) {
            throw new TemplateNotFoundException('template not exists:' . $template, $template);
        }
        
        $this->template->fetch($template, $data);
    }
    
    
    /**
     * 渲染模板内容
     * @access public
     * @param string $template 模板内容
     * @param array  $data 模板变量
     * @return void
     */
    public function display(string $template, array $data = []) : void
    {
        $this->template->display($template, $data);
    }
    
    
    /**
     * 自动定位模板文件
     * @access private
     * @param string $template 模板文件规则
     * @return string
     */
    protected function parseTemplate(string $template) : string
    {
        // 分析模板文件规则
        $request = $this->app->request;
        
        // 获取视图根目录
        if (strpos($template, '@')) {
            // 跨模块调用
            [$app, $template] = explode('@', $template);
        }
        
        if (isset($app)) {
            $view     = $this->config['view_dir_name'];
            $viewPath = $this->app->getBasePath() . $app . DIRECTORY_SEPARATOR . $view . DIRECTORY_SEPARATOR;
            
            if (is_dir($viewPath)) {
                $path = $viewPath;
            } else {
                $path = $this->app->getRootPath() . $view . DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR;
            }
            
            $this->template->view_path = $path;
        } else {
            $path = $this->config['view_path'];
        }
        
        $depr = $this->config['view_depr'];
        
        if (0 !== strpos($template, '/')) {
            $template   = str_replace(['/', ':'], $depr, $template);
            $controller = $request->controller();
            
            if (strpos($controller, '.')) {
                $pos        = strrpos($controller, '.');
                $controller = substr($controller, 0, $pos) . '.' . substr($controller, $pos + 1);
            }
            
            if ($controller) {
                if ('' == $template) {
                    // 如果模板文件名为空 按照默认模板渲染规则定位
                    if (2 == $this->config['auto_rule']) {
                        $template = $request->action(true);
                    } elseif (3 == $this->config['auto_rule']) {
                        $template = $request->action();
                    } else {
                        $template = Str::snake($request->action());
                    }
                    
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                } elseif (false === strpos($template, $depr)) {
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                }
            }
        } else {
            $template = str_replace(['/', ':'], $depr, substr($template, 1));
        }
        
        return $path . ltrim($template, '/') . '.' . ltrim($this->config['view_suffix'], '.');
    }
    
    
    /**
     * 配置模板引擎
     * @access private
     * @param array $config 参数
     * @return void
     */
    public function config(array $config) : void
    {
        $this->template->config($config);
        $this->config = array_merge($this->config, $config);
    }
    
    
    /**
     * 获取模板引擎配置
     * @access public
     * @param string $name 参数名
     * @return mixed
     */
    public function getConfig(string $name)
    {
        return $this->template->getConfig($name);
    }
    
    
    public function __call($method, $params)
    {
        return call_user_func_array([$this->template, $method], $params);
    }
}