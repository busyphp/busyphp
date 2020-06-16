<?php

namespace BusyPHP\helper\net;

use BusyPHP\helper\file\File;
use BusyPHP\exception\AppException;
use BusyPHP\helper\util\Transform;
use CURLFile;

/**
 * HTTP请求类
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2018 busy^life <busy.life@qq.com>
 * @version $Id: 2018-01-06 下午7:22 Http.php busy^life $
 */
class Http
{
    /** 超时时长，单位秒 */
    const REQUEST_TIMEOUT = 30;
    
    /** @var array 请求参数 */
    protected $options = array();
    
    
    /**
     * Http constructor.
     * @throws AppException
     */
    public function __construct()
    {
        if (!function_exists('curl_init')) {
            throw new AppException('curl未开启');
        }
        
        // 超时时间
        // $this->setTimeout(self::REQUEST_TIMEOUT);
        // 浏览器Ua
        $this->setUserAgent(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0');
        // 基本设置
        $options = array(
            // SSL验证, 禁止验证对等证书
            CURLOPT_SSL_VERIFYPEER => false,
            // SSL验证，禁止检测域名
            CURLOPT_SSL_VERIFYHOST => false,
            // 重定向时自动设置header中的Referer
            CURLOPT_AUTOREFERER    => true,
            // 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
            CURLOPT_RETURNTRANSFER => true,
            // 返回响应头
            CURLOPT_HEADER         => true
        );
        
        // safe_mode：PHP安全模式，当开启时一些PHP函数将被禁用
        // open_basedir: 将用户访问文件的活动范围限制在指定的区域
        // 如 open_basedir=.:/tmp 或是具体 /var/tmp
        // 以上两个配置都在php.ini中设定。关于问题，可以修改配置，或者在curl代码那里这样写：
        if (trim(ini_get('open_basedir')) == '' && strtoupper(ini_get('safe_mode')) == 'OFF') {
            // 将服务返回的Location放在header中递归的返回给服务器，
            $options[CURLOPT_FOLLOWLOCATION] = true;
        }
        $this->setOpt($options);
    }
    
    
    /**
     * 设置CURL配置
     * @param int|array $name 配置号
     * @param mixed     $value 配置值
     * @return $this
     * @see http://php.net/manual/zh/curl.constants.php
     */
    public function setOpt($name, $value = null)
    {
        if (!isset($this->options['curl_options'])) {
            $this->options['curl_options'] = array();
        }
        
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->setOpt($k, $v);
            }
        } else {
            $this->options['curl_options'][$name] = $value;
        }
        
        return $this;
    }
    
    
    /**
     * 设置超时时长，单位秒
     * @param int $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->setOpt(CURLOPT_TIMEOUT, $timeout);
        
        return $this;
    }
    
    
    /**
     * 设置请求地址
     * @param string       $url 请求地址
     * @param array|string $params 附加请求参数
     * @return $this
     * @throws AppException
     */
    public function setUrl($url, $params = array())
    {
        $this->options['url'] = trim($url);
        $this->parseUrl($this->options['url'], $params);
        
        return $this;
    }
    
    
    /**
     * 设置UserAgent
     * @param string $userAgent
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->setOpt(CURLOPT_USERAGENT, trim($userAgent));
        
        return $this;
    }
    
    
    /**
     * 设置HTTP Referer来源地址
     * @param string $referer
     * @return $this
     */
    public function setReferer($referer)
    {
        $this->setOpt(CURLOPT_REFERER, $referer);
        
        return $this;
    }
    
    
    /**
     * 设置请求代理
     * @param string     $host
     * @param string     $username 帐号
     * @param string     $password 密码
     * @param string|int $port 端口号
     * @return $this
     */
    public function setProxy($host, $username = '', $password = '', $port = '')
    {
        $this->setOpt(CURLOPT_PROXY, $host);
        if (!empty($port)) {
            $this->setOpt(CURLOPT_PORT, $port);
        }
        if (!empty($username)) {
            $this->setOpt(CURLOPT_PROXYUSERNAME, $username);
        }
        if (!empty($password)) {
            $this->setOpt(CURLOPT_PROXYPASSWORD, $password);
        }
        
        return $this;
    }
    
    
    /**
     * 设置请求参数
     * @param string|array $name 字段或参数合集
     * @param string       $value 参数值
     * @return $this
     */
    public function addParam($name, $value = '')
    {
        if (!isset($this->options['params'])) {
            $this->options['params'] = array();
        }
        
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->addParam($k, $v);
            }
        } else {
            $this->options['params'][$name] = $value;
        }
        
        return $this;
    }
    
    
    /**
     * 设置请求参数
     * @param array|string $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->options['params'] = $params;
        
        return $this;
    }
    
    
    /**
     * 设置请求头
     * @param string|array $name 头名称或头数组集合
     * @param string       $value 头值
     * @return $this
     */
    public function addHeader($name, $value = '')
    {
        if (!isset($this->options['headers'])) {
            $this->options['headers'] = array();
        }
        
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->addHeader($k, $v);
            }
        } else {
            $this->options['headers'][$name] = $value;
        }
        
        return $this;
    }
    
    
    /**
     * 设置请求头
     * @param array|string $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->options['headers'] = $headers;
        
        return $this;
    }
    
    
    /**
     * 设置COOKIE
     * @param string|string $name COOKIE名称或COOKIE数组集合
     * @param string        $value COOKIE值
     * @return $this
     */
    public function addCookie($name, $value = '')
    {
        if (!isset($this->options['cookies'])) {
            $this->options['cookies'] = array();
        }
        
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->addCookie($k, $v);
            }
        } else {
            $this->options['cookies'][$name] = $value;
        }
        
        return $this;
    }
    
    
    /**
     * 设置COOKIE
     * @param array|string $cookies
     * @return $this
     */
    public function setCookies($cookies)
    {
        $this->options['cookies'] = $cookies;
        
        return $this;
    }
    
    
    /**
     * 设置附件
     * @param string|array $name 附件字段
     * @param string       $filename 附件路径
     * @return $this
     * @throws AppException
     */
    public function addFile($name, $filename = '')
    {
        if (!isset($this->options['files'])) {
            $this->options['files'] = array();
        }
        
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                try {
                    $this->addFile($k, $v);
                } catch (AppException $e) {
                    throw new AppException($k . '参数对应的附件无效');
                }
            }
        } else {
            if (!$filename) {
                throw new AppException('请检查附件的有效性');
            }
            $filename = realpath(ltrim($filename, '@'));
            if (!is_file($filename)) {
                throw new AppException('请检查附件的有效性');
            }
            
            if (version_compare(PHP_VERSION, '5.5.0', '<')) {
                $this->options['files'][$name] = '@' . $filename;
            } else {
                $this->options['files'][$name] = new CURLFile($filename);
            }
        }
        
        return $this;
    }
    
    
    /**
     * 解析请求地址
     * @param string       $url 要解析的地址
     * @param array|string $query 追加Query参数会覆盖url中包含的参数
     * @return string
     * @throws AppException
     */
    protected function parseUrl($url, $query = array())
    {
        // 地址
        $url = $url ? $url : $this->options['url'];
        if (!$url) {
            throw new AppException('提交地址不能为空');
        }
        
        if (!$array = parse_url($url)) {
            throw new AppException('请检查提交地址是否有效');
        }
        
        
        $urlTemp = '';
        
        // 请求协议
        if (isset($array['scheme']) && $array['scheme'] === 'https') {
            $this->options['is_ssl'] = true;
            $urlTemp                 .= 'https://';
        } else {
            $this->options['is_ssl'] = false;
            $urlTemp                 .= 'http://';
        }
        
        // 用户名密码
        if (isset($array['user']) && isset($array['pass'])) {
            $urlTemp .= $array['user'] . ':' . $array['pass'] . '@';
        }
        
        // HOST
        $urlTemp .= $array['host'];
        
        // 端口号
        $urlTemp .= isset($array['port']) ? ':' . $array['port'] : '';
        
        // 路径
        $urlTemp .= isset($array['path']) ? $array['path'] : '';
        
        // QUERY
        $params = array();
        if (isset($array['query'])) {
            parse_str($array['query'], $params);
        }
        
        if ($query) {
            if (is_array($query)) {
                $params = array_merge($params, $query);
            } else {
                parse_str($query, $tmp);
                $params = array_merge($params, $tmp);
            }
        }
        if ($params) {
            $urlTemp .= '?' . http_build_query($params);
        }
        
        // fragment
        $urlTemp .= isset($array['fragment']) ? '#' . $array['fragment'] : '';
        
        $this->setOpt(CURLOPT_URL, $urlTemp);
        
        return $urlTemp;
    }
    
    
    /**
     * 执行请求
     * @return string
     * @throws AppException
     */
    public function request()
    {
        $curl = curl_init();
        
        // 请求头
        if (isset($this->options['headers'])) {
            if (is_array($this->options['headers'])) {
                $headers = array();
                foreach ($this->options['headers'] as $key => $value) {
                    $headers[] = "{$key}: {$value}";
                }
                $this->setOpt(CURLOPT_HTTPHEADER, $headers);
            } else {
                $this->setOpt(CURLOPT_HTTPHEADER, $this->options['headers']);
            }
        }
        
        // COOKIE
        if (isset($this->options['cookies'])) {
            if (is_array($this->options['cookies'])) {
                $cookies = array();
                foreach ($this->options['cookies'] as $key => $value) {
                    $value     = rawurlencode($value);
                    $cookies[] = "{$key}={$value}";
                }
                $this->setOpt(CURLOPT_COOKIE, import('; ', $cookies));
            } else {
                $this->setOpt(CURLOPT_COOKIE, $this->options['cookies']);
            }
        }
        
        // 提交参数
        // 包含附件需要数组提交
        if (isset($this->options['files'])) {
            $this->setOpt(CURLOPT_POSTFIELDS, array_merge(isset($this->options['params']) && is_array($this->options['params']) ? $this->options['params'] : array(), $this->options['files']));
        }
        
        // 不包含附件
        // 则按照字符串提交
        elseif (isset($this->options['params'])) {
            $this->setOpt(CURLOPT_POSTFIELDS, is_array($this->options['params']) ? http_build_query($this->options['params']) : $this->options['params']);
        }
        
        // 设置CURL选项
        curl_setopt_array($curl, $this->options['curl_options']);
        
        trace($this->options);
        
        // 执行请求
        $result                     = curl_exec($curl);
        $this->options['curl_info'] = curl_getinfo($curl);
        
        // 请求错误
        if (false === $result) {
            $errorMessage = curl_error($curl);
            $errorCode    = curl_errno($curl);
            curl_close($curl);
            switch ($errorCode) {
                case 28:
                    $errorMessage = "请求超时, 限制为{$this->options['curl_options'][CURLOPT_TIMEOUT]}秒";
                break;
            }
            
            throw new AppException($errorMessage, $errorCode);
        }
        
        curl_close($curl);
        
        // 分隔response header 和 body
        if ($this->options['curl_options'][CURLOPT_HEADER]) {
            $size   = $this->options['curl_info']['header_size'];
            $header = substr($result, 0, $size);
            $result = substr($result, $size);
            
            $this->options['curl_info']['response_header'] = $header;
        }
        
        return $result;
    }
    
    
    /**
     * 获取请求选项
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    
    /**
     * 获取响应的HTTP状态码
     * @return int|false
     */
    public function getResponseStatusCode()
    {
        if (isset($this->options['curl_info']['http_code'])) {
            return $this->options['curl_info']['http_code'];
        }
        
        return false;
    }
    
    
    /**
     * 获取响应头
     * @return string|false
     */
    public function getResponseHeaders()
    {
        return isset($this->options['curl_info']['response_header']) ? $this->options['curl_info']['response_header'] : false;
    }
    
    
    /**
     * 获取响应的ContentType
     * @return bool
     */
    public function getResponseContentType()
    {
        if (isset($this->options['curl_info']['content_type'])) {
            return $this->options['curl_info']['content_type'];
        }
        
        return false;
    }
    
    
    /**
     * 快速实例化
     * @return $this
     */
    public static function init()
    {
        return new static();
    }
    
    
    /**
     * 执行GET请求
     * @param string       $url 请求地址
     * @param string|array $params POST参数
     * @param Http|null    $http 指定实例化好的请求类
     * @return string
     * @throws AppException
     */
    public static function get($url, $params = array(), $http = null)
    {
        if (is_null($http)) {
            $http = new Http();
        }
        
        $http->setUrl($url, $params);
        
        return $http->request();
    }
    
    
    /**
     * 执行POST请求
     * @param string       $url 请求地址
     * @param string|array $params POST参数
     * @param Http|null    $http 指定实例化好的请求类
     * @return string
     * @throws AppException
     */
    public static function post($url, $params = array(), $http = null)
    {
        if (is_null($http)) {
            $http = new Http();
        }
        
        if (!is_array($params)) {
            parse_str($params, $params);
        }
        
        $http->setUrl($url);
        $http->addParam($params);
        $http->setOpt(CURLOPT_POST, true);
        
        return $http->request();
    }
    
    
    /**
     * 执行提交字符串请求
     * @param string    $url 请求地址
     * @param string    $string 要提交的字符串
     * @param string    $contentType 字符串类型
     * @param Http|null $http 指定实例化好的请求类
     * @return string
     * @throws AppException
     */
    public static function postString($url, $string = '', $contentType = '', $http = null)
    {
        if (is_null($http)) {
            $http = new Http();
        }
        
        $contentType = trim($contentType);
        $http->setUrl($url);
        $http->setParams($string);
        $http->addHeader('Content-Type', !empty($contentType) ? $contentType : 'text/plain');
        $http->addHeader('Content-Length', strlen($string));
        
        return $http->request();
    }
    
    
    /**
     * 执行JSON请求
     * @param string       $url 请求地址
     * @param string|array $json 要提交的JSON字符串或数组
     * @param Http|null    $http 指定实例化好的请求类
     * @param bool         $unescapedUnicode JSON遇到中文是否保留中文，针对$json为数组的时候有效，默认保留
     * @return string
     * @throws AppException
     */
    public static function postJSON($url, $json = '', $http = null, $unescapedUnicode = false)
    {
        if (is_array($json)) {
            if ($unescapedUnicode) {
                $json = json_encode($json, JSON_UNESCAPED_UNICODE);
            } else {
                $json = json_encode($json);
            }
        }
        
        return static::postString($url, $json, 'application/json', $http);
    }
    
    
    /**
     * 执行XML请求
     * @param string       $url 请求地址
     * @param string|array $xml 要提交的XML字符串或数组
     * @param Http|null    $http 指定实例化好的请求类
     * @param string|false $root 根节点名称，默认为root,针对$xml为数组的时候有效
     * @param string|false $encode XML ENCode 编码
     * @return string
     * @throws AppException
     */
    public static function postXML($url, $xml = '', $http = null, $root = 'root', $encode = 'utf-8')
    {
        if (is_array($xml)) {
            if ($encode === false || $root === false) {
                $xml = Transform::dataToXml($xml);
            } else {
                $xml = Transform::xmlEncode($xml, $encode, 'root');
            }
        }
        
        return static::postString($url, $xml, 'text/xml', $http);
    }
    
    
    /**
     * 下载文件
     * @param string $url 地址
     * @param string $filename 保存路径
     * @param array  $params 附件参数
     * @param Http   $http Http实例
     * @return string 不带域名的URL地址
     * @throws AppException
     */
    public static function download($url, $filename, $params = array(), $http = null)
    {
        $resource = null;
        try {
            if (is_null($http)) {
                $http = new Http();
            }
            
            // 创建文件夹
            $dir = File::pathInfo($filename, PATHINFO_DIRNAME);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    throw new AppException('无写权限[' . $dir . ']');
                }
            }
            
            $resource = fopen($filename, 'w');
            if (!$resource) {
                throw new AppException('无写权限[' . $filename . ']');
            }
            
            $http->setUrl($url, $params);
            $http->setOpt(CURLOPT_FILE, $resource);
            $http->setOpt(CURLOPT_HEADER, false);
            $http->request();
            
            fclose($resource);
            
            if (!is_file($filename)) {
                throw new AppException('下载失败[' . $filename . ']');
            }
            
            return '/' . str_replace('\\', '/', substr($filename, strlen(BUSY_PHP_PATH)));
        } catch (AppException $e) {
            if ($resource != null) {
                fclose($resource);
            }
            
            throw new AppException($e);
        }
    }
}