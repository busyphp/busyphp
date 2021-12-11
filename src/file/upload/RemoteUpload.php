<?php
declare(strict_types = 1);

namespace BusyPHP\file\upload;

use BusyPHP\App;
use BusyPHP\file\Upload;
use BusyPHP\helper\HttpHelper;
use Exception;
use think\exception\FileException;

/**
 * 抓取远程文件上传类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/20 下午下午4:07 RemoteUpload.php $
 * @todo 下载超大文件没有进行测试
 */
class RemoteUpload extends Upload
{
    /**
     * @var HttpHelper
     */
    protected $http;
    
    /**
     * 文件扩展名
     * @var string|callable
     */
    protected $extension = '';
    
    /**
     * 文件Mime
     * @var string|callable
     */
    protected $mimeType = '';
    
    /**
     * 设置文件名
     * @var string|callable
     */
    protected $name = '';
    
    /**
     * 忽略的域名
     * @var array
     */
    protected $ignoreHosts = [];
    
    
    public function __construct(?Upload $target = null)
    {
        parent::__construct($target);
        
        $this->http = HttpHelper::init();
    }
    
    
    /**
     * 获取请求
     * @return HttpHelper
     */
    public function getHttp() : HttpHelper
    {
        return $this->http;
    }
    
    
    /**
     * 设置CURL选项
     * @param $key
     * @param $value
     * @return $this
     */
    public function setCurlOption($key, $value) : self
    {
        $this->http->setOpt($key, $value);
        
        return $this;
    }
    
    
    /**
     * 设置文件扩展名，
     * - 字符串: 无法获取到扩展名的时候使用该扩展名
     * - 回调: 通过回调获取，系统不解析
     * @param string|callable $extension
     * @return $this
     */
    public function setExtension($extension) : self
    {
        $this->extension = $extension;
        
        return $this;
    }
    
    
    /**
     * 设置文件Mime类型
     * - 字符串: 无法获取到Mime的时候使用该Mime
     * - 回调: 通过回调获取，系统不解析
     * @param string|callable $mimeType
     * @return $this
     */
    public function setMimeType($mimeType) : self
    {
        $this->mimeType = $mimeType;
        
        return $this;
    }
    
    
    /**
     * 设置文件名
     * - 字符串: 无法获取到文件名的时候使用该文件名
     * - 回调: 通过回调获取，系统不解析
     * @param string|callable $name
     * @return $this
     */
    public function setName($name) : self
    {
        $this->name = $name;
        
        return $this;
    }
    
    
    /**
     * 设置忽略的域名
     * @param array $ignoreHosts
     * @return $this
     */
    public function setIgnoreHosts(array $ignoreHosts) : self
    {
        $this->ignoreHosts = $ignoreHosts;
        
        return $this;
    }
    
    
    /**
     * 上传处理
     * @param mixed $url 远程URL
     * @return array [文件名称,文件对象,图像信息]
     * @throws Exception
     */
    protected function handle($url) : array
    {
        if (!$url) {
            throw new FileException('下载地址不能为空');
        }
        
        $parse = parse_url($url);
        if (!$parse || empty($parse['path']) || empty($parse['host'])) {
            throw new FileException("下载地址无效: {$url}");
        }
        
        $ignoreHosts = array_merge($this->ignoreHosts, [App::getInstance()->request->host(true)]);
        if (in_array($parse['host'], $ignoreHosts)) {
            throw new FileException("下载地址中包含忽略域名: {$url}");
        }
        
        // 获取文件头信息
        $headerHttp = clone $this->http;
        $headerHttp->setUrl($url);
        $headerHttp->setOpt(CURLOPT_NOBODY, true);
        $headerHttp->setOpt(CURLOPT_POST, false);
        $headerHttp->request();
        $headers       = HttpHelper::parseResponseHeaders($headerHttp->getResponseHeaders());
        $mimeType      = strtolower(trim($headers['content-type'] ?? ''));
        $contentLength = intval($headers['content-length'] ?? 0);
        $name          = '';
        $extension     = '';
        
        // 从响应中获取文件名
        if (preg_match('/.*filename=(.+)/is', trim($headers['content-disposition'] ?? ''), $match)) {
            $name      = trim($match[1], "'");
            $name      = trim($name, '"');
            $extension = pathinfo($name, PATHINFO_EXTENSION);
        }
        
        // 通过mimeType解析文件扩展名
        if (!$extension && $mimeType) {
            $extension = self::IMAGE_MIME_TYPES[$mimeType] ?? '';
        }
        
        // 从url中取文件扩展名
        $urlPathInfo = pathinfo($parse['path']);
        $extension   = $extension ?: ($urlPathInfo['extension'] ?? '');
        $name        = $name ?: ($urlPathInfo['basename'] ?? '');
        
        // 自定义Mime
        if ($this->mimeType) {
            if (is_callable($this->mimeType)) {
                $mimeType = call_user_func_array($this->mimeType, [
                    $mimeType,
                    $headers,
                    $urlPathInfo
                ]);
            } else {
                $mimeType = $mimeType ?: $this->mimeType;
            }
        }
        
        // 自定义扩展名
        if ($this->extension) {
            if (is_callable($this->extension)) {
                $extension = call_user_func_array($this->extension, [
                    $extension,
                    $mimeType,
                    $headers,
                    $urlPathInfo
                ]);
            } else {
                $extension = $extension ?: $this->extension;
            }
        }
        
        // 自定义名称
        if ($this->name) {
            if (is_callable($this->name)) {
                $name = call_user_func_array($this->name, [
                    $name,
                    $extension,
                    $mimeType,
                    $headers,
                    $urlPathInfo
                ]);
            } else {
                $name = $name ?: $this->name;
            }
        }
        
        // 校验文件
        $this->checkExtension($extension);
        $this->checkFileSize($contentLength);
        $this->checkMimeType($mimeType);
        
        
        // 创建空文件
        $system = $this->fileSystem();
        $path   = $this->createFilename($url, $extension);
        if (!$system->put($path, '')) {
            throw new FileException("创建文件失败: {$path}");
        }
        $file = $system->path($path);
        
        // 执行下载
        HttpHelper::download($url, $file, [], $this->http);
        
        // 检测图片
        try {
            $imageInfo = $this->checkImage($file, $extension);
        } catch (Exception $e) {
            $system->delete($path);
            throw $e;
        }
        
        return [$name, $path, $imageInfo];
    }
}