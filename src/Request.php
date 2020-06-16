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
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setGet($name, $value = '') : self
    {
        $this->get[$name] = $value;
        
        return $this;
    }
    
    
    /**
     * 设置param
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setParam($name, $value = '') : self
    {
        $this->param[$name] = $value;
        
        return $this;
    }
    
    
    /**
     * 设置$_POST
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setPost($name, $value = '') : self
    {
        $this->post[$name] = $value;
        
        return $this;
    }
    
    
    /**
     * 设置$_REQUEST
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setRequest($name, $value = '') : self
    {
        $this->request[$name] = $value;
        
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
