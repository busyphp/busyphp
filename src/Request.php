<?php
declare (strict_types = 1);

namespace BusyPHP;

/**
 * Request
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/27 下午8:32 上午 Request.php $
 */
class Request extends \think\Request
{
    /**
     * 网站静态资源URL
     * @var string
     */
    protected static $assetsUrl;
    
    /**
     * 网站文件资源URL
     * @var string
     */
    protected static $fileUrl;
    
    /**
     * 来源地址
     * @var string
     */
    protected $varRedirectUrl = 'redirect_url';
    
    /**
     * 应用入口URL
     * @var string
     */
    protected $appUrl;
    
    /**
     * 网站入口URL
     * @var string
     */
    protected $webUrl;
    
    /**
     * 分组
     * @var string
     */
    protected $group;
    
    
    /**
     * 获取控制器名称
     * @param bool $convert 是否转换为小写
     * @param bool $real 是否输出真实的控制器名称
     * @return string
     */
    public function controller(bool $convert = false, $real = false) : string
    {
        $controller = parent::controller($convert);
        
        if (!$real || false === strpos($controller, '.')) {
            return $controller;
        }
        
        $array = explode('.', $controller);
        
        return array_pop($array);
    }
    
    
    /**
     * 设置分组
     * @param string $group
     * @return Request
     */
    public function setGroup(string $group) : self
    {
        $this->group = $group;
        
        return $this;
    }
    
    
    /**
     * 获取分组
     * @param bool $convert
     * @return string
     */
    public function group(bool $convert = false) : string
    {
        $controller = $this->controller ?: '';
        if (!$this->group && false !== strpos($controller, '.')) {
            $array       = explode('.', $controller);
            $this->group = array_shift($array);
        }
        
        $name = $this->group ?: '';
        
        return $convert ? strtolower($name) : $name;
    }
    
    
    /**
     * 设置$_SERVER
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function setServer(string $name, $value) : self
    {
        $this->server[strtoupper($name)] = $value;
        
        return $this;
    }
    
    
    /**
     * 设置$_GET
     * @param string       $name
     * @param string|array $value
     * @return $this
     */
    public function setGet($name, $value = '') : self
    {
        if (isset($this->get[$name]) && is_array($this->get[$name])) {
            $this->get[$name] = array_merge($this->get[$name], $value);
        } else {
            $this->get[$name] = $value;
        }
        
        return $this;
    }
    
    
    /**
     * 设置$_PUT
     * @param string       $name
     * @param string|array $value
     * @return $this
     */
    public function setPut($name, $value = '') : self
    {
        if (isset($this->put[$name]) && is_array($this->put[$name])) {
            $this->put[$name] = array_merge($this->put[$name], $value);
        } else {
            $this->put[$name] = $value;
        }
        
        return $this;
    }
    
    
    /**
     * 设置param
     * @param string       $name
     * @param string|array $value
     * @return $this
     */
    public function setParam($name, $value = '') : self
    {
        if (isset($this->param[$name]) && is_array($this->param[$name])) {
            $this->param[$name] = array_merge($this->param[$name], $value);
        } else {
            $this->param[$name] = $value;
        }
        
        return $this;
    }
    
    
    /**
     * 设置$_POST
     * @param string       $name
     * @param string|array $value
     * @return $this
     */
    public function setPost($name, $value = '') : self
    {
        if (isset($this->post[$name]) && is_array($this->post[$name])) {
            $this->post[$name] = array_merge($this->post[$name], $value);
        } else {
            $this->post[$name] = $value;
        }
        
        return $this;
    }
    
    
    /**
     * 设置$_REQUEST
     * @param string       $name
     * @param string|array $value
     * @return $this
     */
    public function setRequest($name, $value = '') : self
    {
        if (isset($this->request[$name]) && is_array($this->request[$name])) {
            $this->request[$name] = array_merge($this->request[$name], $value);
        } else {
            $this->request[$name] = $value;
        }
        
        return $this;
    }
    
    
    /**
     * 获取Ajax表单伪装键
     * @return string
     */
    public function getVarAjax() : string
    {
        return $this->varAjax;
    }
    
    
    /**
     * 获取Pjax表单伪装键
     * @return string
     */
    public function getVarPjax() : string
    {
        return $this->varPjax;
    }
    
    
    /**
     * 设置当前请求为ajax请求
     */
    public function setRequestIsAjax() : void
    {
        $this->setServer('HTTP_X_REQUESTED_WITH', 'xmlhttprequest');
    }
    
    
    /**
     * 获取来源地址
     * @param string $default 默认地址
     * @return string
     */
    public function getRedirectUrl(string $default = null) : string
    {
        $varRedirectUrl = config('route.var_redirect_url');
        $varRedirectUrl = $varRedirectUrl ?: $this->varRedirectUrl;
        
        $url = $this->param($varRedirectUrl);
        if (!$url) {
            $url = $this->server('HTTP_REFERER');
        }
        if (!$url) {
            $url = $default;
        }
        
        return rawurlencode($url);
    }
    
    
    /**
     * 设置应用入口URL
     * @param string $appUrl
     */
    public function setAppUrl(string $appUrl) : void
    {
        $this->appUrl = $appUrl;
    }
    
    
    /**
     * 设置站点入口URL
     * @param string $webUrl
     */
    public function setWebUrl(string $webUrl) : void
    {
        $this->webUrl = $webUrl;
    }
    
    
    /**
     * 获取站点入口URL
     * @param bool $domain 是否包含完整域名
     * @return string
     */
    public function getWebUrl(bool $domain = false) : string
    {
        return $domain ? $this->domain() . $this->webUrl : $this->webUrl;
    }
    
    
    /**
     * 获取APP入口URL
     * @param bool $domain 是否包含完整域名
     * @return string
     */
    public function getAppUrl(bool $domain = false) : string
    {
        return $domain ? $this->domain() . $this->appUrl : $this->appUrl;
    }
    
    
    /**
     * 获取网站资源入口URL
     * @param bool $domain 是否包含完整域名
     * @return string
     */
    public function getWebAssetsUrl(bool $domain = false) : string
    {
        if (!isset(self::$assetsUrl)) {
            if ($domainAssets = config('route.domain_assets')) {
                self::$assetsUrl = rtrim($domainAssets, '/') . '/';
            } else {
                self::$assetsUrl = $this->getWebUrl($domain) . 'assets/';
            }
        }
        
        return self::$assetsUrl;
    }
    
    
    /**
     * 获取网站文件入口URL
     * @param bool $domain 是否包含完整域名
     * @return string
     */
    public function getWebFileUrl(bool $domain = false) : string
    {
        if (!isset(self::$fileUrl)) {
            if ($domainFile = config('route.domain_file')) {
                self::$fileUrl = rtrim($domainFile, '/') . '/';
            } else {
                self::$fileUrl = $this->getWebUrl($domain) . 'data/file/';
            }
        }
        
        return self::$fileUrl;
    }
}
