<?php
declare (strict_types = 1);

namespace BusyPHP;

use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Filter;
use think\Container;
use think\facade\Config;
use think\file\UploadedFile;

/**
 * Request
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/27 下午8:32 上午 Request.php $
 * @method mixed param($name = '', $default = null, callable $filter = '')
 * @method mixed get($name = '', $default = null, callable $filter = '')
 * @method mixed post($name = '', $default = null, callable $filter = '')
 * @method mixed request($name = '', $default = null, callable $filter = '')
 * @method mixed put($name = '', $default = null, callable $filter = '')
 * @method mixed delete($name = '', $default = null, callable $filter = '')
 * @method mixed patch($name = '', $default = null, callable $filter = '')
 * @method mixed route($name = '', $default = null, callable $filter = '')
 * @method mixed cookie(string $name = '', $default = null, callable $filter = '')
 * @method mixed only(array $name, $data = 'param', callable $filter = '') : array
 * @method UploadedFile|UploadedFile[]|null file(string $name = '')
 */
class Request extends \think\Request
{
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
     * 单例
     * @return Request
     */
    public static function init() : self
    {
        return Container::getInstance()->make(self::class);
    }
    
    
    /**
     * 获取变量 支持过滤和默认值
     * @access public
     * @param array           $data 数据源
     * @param string|false    $name 字段名
     * @param mixed           $default 默认值
     * @param string|callable $filter 过滤函数
     * @return mixed
     */
    public function input(array $data = [], $name = '', $default = null, $filter = '')
    {
        // 获取原始数据
        if (false === $name) {
            return $data;
        }
        
        // list方式获取
        $key = (string) $name;
        if ($key && strpos($key, '/')) {
            $array   = explode('/', $name);
            $key     = $array[0];
            $type    = $array[1];
            $message = $array[2] ?? '';
            if ($type === 'list') {
                $data = $this->getData($data, $key);
                if (is_null($data)) {
                    $data = $default;
                }
                
                if (!is_array($data)) {
                    $data = trim((string) $data);
                    if (!$data) {
                        $data = [];
                    } else {
                        $data = explode(',', $data);
                    }
                }
                $data = array_map('trim', $data);
                
                if ($message && !$data) {
                    throw new VerifyException($message, $key);
                }
                
                return $this->filterData($data, $filter, $key, null);
            }
        }
        
        return parent::input($data, $name, $default, $filter);
    }
    
    
    /**
     * 获取url的path部分，不含应用名称和扩展名
     * @return string
     */
    public function getPath() : string
    {
        $path = $this->url();
        $path = ltrim(trim(parse_url($path, PHP_URL_PATH)), '/');
        if (false !== $index = strrpos($path, '.')) {
            $path = substr($path, 0, $index);
        }
        $path = ltrim(substr("/{$path}", strlen($this->root())), '/');
        
        return $path;
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
     * 获取来源地址请求参数名称
     * @return string
     */
    public function getVarRedirectUrl() : string
    {
        $varRedirectUrl = Config::get('route.var_redirect_url');
        $varRedirectUrl = $varRedirectUrl ?: $this->varRedirectUrl;
        
        return $varRedirectUrl;
    }
    
    
    /**
     * 获取来源地址
     * @param string $default 默认地址
     * @param bool   $byServer 如果无法从参数获取，是否从server中获取
     * @return string
     */
    public function getRedirectUrl(string $default = null, $byServer = true) : string
    {
        $url = $this->param($this->getVarRedirectUrl(), '', 'rawurldecode');
        if (!$url && $byServer) {
            $url = $this->server('HTTP_REFERER');
        }
        if (!$url) {
            $url = $default;
        }
        
        return $url;
    }
    
    
    /**
     * 获取站点入口URL
     * @param bool $domain 是否包含完整域名
     * @return string
     */
    public function getWebUrl(bool $domain = false) : string
    {
        if (!$this->webUrl) {
            $root = $this->baseFile();
            if ($root && 0 !== strpos($this->url(), $root)) {
                $root = str_replace('\\', '/', dirname($root));
            }
            
            $root = rtrim($root, '/') . '/';
            $root = strpos($root, '.') ? ltrim(dirname($root), DIRECTORY_SEPARATOR) : $root;
            if ('' != $root) {
                $root = '/' . ltrim($root, '/');
            }
            $this->webUrl = rtrim($root, '/') . '/';
        }
        
        return $domain ? $this->domain() . $this->webUrl : $this->webUrl;
    }
    
    
    /**
     * 获取APP入口URL
     * @param bool $domain 是否包含完整域名
     * @return string
     */
    public function getAppUrl(bool $domain = false) : string
    {
        if (!$this->appUrl) {
            $appUrl = $this->root();
            if (false === strpos($appUrl, '.')) {
                $appUrl = $this->getWebUrl() . trim($appUrl, '/');
            }
            $this->appUrl = rtrim($appUrl, '/') . '/';
        }
        
        return $domain ? $this->domain() . $this->appUrl : $this->appUrl;
    }
    
    
    /**
     * 获取网站资源入口URL
     * @param bool $domain 是否包含完整域名
     * @return string
     */
    public function getAssetsUrl(bool $domain = false) : string
    {
        if ($domainAssets = App::getInstance()->config->get('route.domain_assets')) {
            return rtrim($domainAssets, '/') . '/';
        } else {
            return $this->getWebUrl($domain) . 'assets/';
        }
    }
}
