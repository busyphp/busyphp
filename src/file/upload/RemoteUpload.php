<?php
declare(strict_types = 1);

namespace BusyPHP\file\upload;

use BusyPHP\App;
use BusyPHP\file\Upload;
use BusyPHP\helper\HttpHelper;
use Exception;
use League\Flysystem\Util\MimeType;
use think\exception\FileException;

/**
 * 抓取远程文件上传类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
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
     * @var string
     */
    protected $extension = '';
    
    /**
     * 文件Mime
     * @var string
     */
    protected $mimeType = '';
    
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
     * 设置文件扩展名，当系统无法获取到扩展名的时候使用该扩展名
     * @param string $extension
     * @return $this
     */
    public function setDefaultExtension(string $extension) : self
    {
        $this->extension = trim($extension);
        
        return $this;
    }
    
    
    /**
     * 设置文件Mime类型，系统无法获取到Mime的时候使用该Mime
     * @param string $mimeType
     * @return $this
     */
    public function setDefaultMimeType(string $mimeType) : self
    {
        $this->mimeType = trim($mimeType);
        
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
        
        $ignoreHosts = array_merge($this->ignoreHosts, [App::init()->request->host(true)]);
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
        $contentType   = trim($headers['content-type'] ?? '');
        $contentLength = intval($headers['content-length'] ?? 0);
        
        // 获取文件名以及扩展名
        $name      = '';
        $extension = '';
        
        // 从响应中获取文件名
        if (preg_match('/.*filename=(.+)/is', trim($headers['content-disposition'] ?? ''), $match)) {
            $name      = $match[1];
            $extension = pathinfo($match[1], PATHINFO_EXTENSION);
        }
        
        // 通过mimeType解析文件扩展名
        if (!$extension && $contentType) {
            $mimeType = strtolower($contentType);
            foreach (MimeType::getExtensionToMimeTypeMap() as $key => $value) {
                if ($mimeType === strtolower($value)) {
                    $extension = $key;
                    break;
                }
            }
        }
        
        // 从url中取文件扩展名
        $urlPathInfo = pathinfo($parse['path']);
        $extension   = $extension ?: (($urlPathInfo['extension'] ?? '') ?: $this->extension);
        $name        = $name ?: ($urlPathInfo['basename'] ?? '');
        if (!$extension) {
            throw new FileException('必须指定文件扩展名');
        }
        
        // 校验文件
        $this->checkExtension($extension);
        $this->checkFileSize($contentLength);
        $this->checkMimeType($contentType ?: $this->mimeType);
        
        
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