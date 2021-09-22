<?php

namespace BusyPHP\file\upload;

use BusyPHP\file\Upload;
use BusyPHP\helper\net\Http;
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
     * @var Http
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
    
    
    public function __construct(?Upload $target = null)
    {
        parent::__construct($target);
        
        $this->http = Http::init();
    }
    
    
    /**
     * 获取请求
     * @return Http
     */
    public function getHttp() : Http
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
        if (!$parse || empty($parse['path'])) {
            throw new FileException("下载地址无效: {$url}");
        }
        $urlPathInfo = pathinfo($parse['path'] ?? '');
        
        
        // 获取文件头信息
        $headerHttp = clone $this->http;
        $headerHttp->setUrl($url);
        $headerHttp->setOpt(CURLOPT_NOBODY, true);
        $headerHttp->setOpt(CURLOPT_POST, false);
        $headerHttp->request();
        $headers       = Http::parseResponseHeaders($headerHttp->getResponseHeaders());
        $contentType   = trim($headers['Content-Type'] ?? '');
        $contentLength = floatval($headers['Content-Length'] ?? 0);
        
        // 获取文件名以及扩展名
        $name      = '';
        $extension = '';
        
        // 从响应中获取文件名
        if (preg_match('/.*filename=(.+)/is', trim($headers['Content-Disposition'] ?? ''), $match)) {
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
        $extension = $extension ?: (($urlPathInfo['extension'] ?? '') ?: $this->extension);
        $name      = $name ?: ($urlPathInfo['basename'] ?? '');
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
        Http::download($url, $file, [], $this->http);
        
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