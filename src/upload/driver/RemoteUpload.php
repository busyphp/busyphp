<?php
declare(strict_types = 1);

namespace BusyPHP\upload\driver;

use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\helper\FileHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\Upload;
use BusyPHP\upload\parameter\RemoteParameter;
use BusyPHP\upload\result\UploadResult;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use League\Flysystem\FilesystemException;
use think\Container;
use think\facade\Filesystem;
use think\File;
use think\filesystem\Driver;

/**
 * 抓取远程文件上传类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/20 下午下午4:07 Remote.php $
 * @property RemoteParameter $parameter
 */
class RemoteUpload extends Upload
{
    /**
     * 执行上传
     * @return UploadResult
     * @throws FilesystemException
     * @throws GuzzleException
     */
    protected function handle() : UploadResult
    {
        if (!$this->parameter instanceof RemoteParameter) {
            throw new ClassNotExtendsException($this->parameter, RemoteParameter::class);
        }
        
        if (!$url = $this->parameter->getUrl()) {
            throw new InvalidArgumentException('远程文件URL为空');
        }
        
        // 解析URL
        $urls = parse_url($url) ?: [];
        $host = $urls['host'] ?? ''; //TODO host 过滤
        $path = $urls['path'] ?? '';
        
        // 取出扩展名/文件名/mimetype
        // 分为2种情况：
        // 1. 取出的是有效的扩展名
        // 2. 取出的不是有效的扩展名
        $filename  = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $mimetype  = FileHelper::getMimetypeByPath($path);
        
        // 发起一次head请求，获取文件名/长度/mimetype
        $client      = new Client($this->parameter->getOptions());
        $response    = $client->head($url, $this->parameter->getHeadOptions());
        $mimetype    = $response->getHeaderLine('content-type') ?: $mimetype;
        $extension   = FileHelper::getExtensionByMimetype($mimetype) ?: $extension;
        $disposition = $response->getHeaderLine('content-disposition');
        
        // 匹配 attachment
        // attachment; filename=content.txt
        // attachment; filename*=UTF-8''filename.txt
        // attachment; filename="EURO rates"; filename*=utf-8''%e2%82%ac%20rates
        // attachment; filename="omáèka.jpg"
        if ($disposition && preg_match('/filename\*?=[\'"]?(?:UTF-\d[\'"]*)?([^;\r\n"\']*)[\'"]?;?/i', $disposition, $match)) {
            $filename  = rawurldecode($match[1]) ?: $filename;
            $extension = pathinfo($filename, PATHINFO_EXTENSION) ?: $extension;
        }
        
        // 校验
        $basename  = $this->parameter->getBasename($url, sprintf('%s.%s', $filename, $extension));
        $mimetype  = $this->parameter->getMimetype($url, $mimetype, $basename);
        $extension = pathinfo($basename, PATHINFO_EXTENSION);
        $this->checkExtension($extension);
        $this->checkMimetype($mimetype);
        
        // 创建一个临时空文件
        $disk = $this->parameter->getTmpDisk();
        if (!$disk instanceof Driver) {
            $disk = Filesystem::disk($disk ?: 'local');
        }
        $dir = trim(trim($this->parameter->getTmpDir()), '/') ?: 'remotes';
        $tmp = sprintf('%s/%s.%s', $dir, md5(implode(',', [
            $url,
            StringHelper::uuid(),
            microtime()
        ])), $extension);
        $disk->write($tmp, '');
        
        // 下载文件至临时文件
        try {
            $file = new File($disk->path($tmp));
            $client->get($url, array_merge($this->parameter->getGetOptions(), [
                'sink'     => $file->getPathname(),
                'progress' => function() {
                    if (is_callable($progress = $this->parameter->getProgress())) {
                        Container::getInstance()->invokeFunction($progress, func_get_args());
                    }
                }
            ]));
            
            $md5      = $file->md5();
            $filesize = $file->getSize();
            $this->checkFilesize($filesize);
            [$width, $height] = FileHelper::checkImage($file->getPathname(), $extension);
            $path = $this->putFileToDisk($file);
        } finally {
            if ($disk->fileExists($tmp)) {
                $disk->delete($tmp);
            }
        }
        
        $result = new UploadResult();
        $result->setBasename($basename);
        $result->setPath($path);
        $result->setFilesize($filesize);
        $result->setMimetype($mimetype);
        $result->setMd5($md5);
        $result->setWidth($width);
        $result->setHeight($height);
        
        return $result;
    }
}