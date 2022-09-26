<?php

namespace BusyPHP\helper;

use League\MimeTypeDetection\FinfoMimeTypeDetector;
use League\MimeTypeDetection\MimeTypeDetector;
use think\exception\FileException;
use think\exception\HttpException;
use think\facade\Request;
use think\Response;

/**
 * 文件操作辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午上午10:12 FileHelper.php $
 */
class FileHelper
{
    /** @var string[] 常用mimetype类型 */
    protected static $mimetypeMap = [
        'image/apng'                                                                => 'apng',
        'image/bmp'                                                                 => 'bmp',
        'image/gif'                                                                 => 'gif',
        'image/x-icon'                                                              => 'ico',
        'image/jpeg'                                                                => 'jpeg',
        'image/png'                                                                 => 'png',
        'image/tiff'                                                                => 'tiff',
        'image/webp'                                                                => 'webp',
        'image/wmf'                                                                 => 'wmf',
        'video/3gpp'                                                                => '3gpp',
        'application/x-7z-compressed'                                               => '7z',
        'application/vnd.android.package-archive'                                   => 'apk',
        'video/x-msvideo'                                                           => 'avi',
        'image/avif'                                                                => 'avif',
        'application/x-msdownload'                                                  => 'bat',
        'application/x-bzip'                                                        => 'bz',
        'application/x-bzip2'                                                       => 'bz2',
        'text/css'                                                                  => 'css',
        'text/csv'                                                                  => 'csv',
        'application/x-apple-diskimage'                                             => 'dmg',
        'application/msword'                                                        => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.template'   => 'dotx',
        'application/xml-dtd'                                                       => 'dtd',
        'application/vnd.ms-fontobject'                                             => 'eot',
        'application/postscript'                                                    => 'eps',
        'video/x-flv'                                                               => 'flv',
        'text/plain'                                                                => 'txt',
        'application/x-iso9660-image'                                               => 'iso',
        'application/java-archive'                                                  => 'jar',
        'text/x-java-source'                                                        => 'java',
        'application/javascript'                                                    => 'js',
        'application/json'                                                          => 'json',
        'application/json5'                                                         => 'json5',
        'text/less'                                                                 => 'less',
        'application/vnd.apple.mpegurl'                                             => 'm3u8',
        'audio/mpeg'                                                                => 'mp3',
        'video/mp4'                                                                 => 'mp4',
        'application/pdf'                                                           => 'pdf',
        'application/x-httpd-php'                                                   => 'php',
        'application/powerpoint'                                                    => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/vnd.3gpp.pic-bw-small'                                         => 'psb',
        'application/x-photoshop'                                                   => 'psd',
        'video/mp2t'                                                                => 'ts',
        'font/ttf'                                                                  => 'ttf',
        'font/collection'                                                           => 'ttc',
        'model/u3d'                                                                 => 'u3d',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
        'application/vnd.ms-excel'                                                  => 'xls',
        'application/zip'                                                           => 'zip',
        'application/xml'                                                           => 'xml',
        'font/woff'                                                                 => 'woff',
        'font/woff2'                                                                => 'woff2',
        'image/svg+xml'                                                             => 'svg',
        'font/otf'                                                                  => 'otf',
        'application/x-rar'                                                         => 'rar',
        'text/html'                                                                 => 'html',
        'application/iphone'                                                        => 'ipa',
    ];
    
    /** @var MimeTypeDetector */
    protected static $mimeTypeDetector;
    
    
    /**
     * Mimetype解析器
     * @return MimeTypeDetector
     */
    protected static function mimeTypeDetector() : MimeTypeDetector
    {
        if (!static::$mimeTypeDetector instanceof MimeTypeDetector) {
            static::$mimeTypeDetector = new FinfoMimeTypeDetector();
        }
        
        return static::$mimeTypeDetector;
    }
    
    
    /**
     * 获取文件mimetype
     * @param string $path 文件绝对路径
     * @return string
     */
    public static function getMimetype(string $path) : string
    {
        $mimetype = static::getMimetypeByFile($path);
        if (in_array(strtolower($mimetype), ['application/octet-stream', 'inode/x-empty', 'application/x-empty'])) {
            $mimetype = static::getMimetypeByPath($path);
        }
        
        return $mimetype;
    }
    
    
    /**
     * 通过路径获取mimetype
     * @param string $path
     * @return string
     */
    public static function getMimetypeByPath(string $path) : string
    {
        return static::mimeTypeDetector()->detectMimeTypeFromPath($path) ?: '';
    }
    
    
    /**
     * 通过文件绝对路径获取mimetype
     * @param string $path
     * @return string
     */
    public static function getMimetypeByFile(string $path) : string
    {
        if (!is_file($path)) {
            throw new FileException(sprintf('文件不存在: %s', $path));
        }
        
        return static::mimeTypeDetector()->detectMimeTypeFromFile($path) ?: '';
    }
    
    
    /**
     * 通过文件内容获取mimetype
     * @param string $content
     * @return string
     */
    public static function getMimetypeByContent(string $content) : string
    {
        return static::mimeTypeDetector()->detectMimeTypeFromBuffer($content) ?: '';
    }
    
    
    /**
     * 通过文件扩展名获取mimetype
     * @param string $extension
     * @return string
     */
    public static function getMimetypeByExtension(string $extension) : string
    {
        return static::mimeTypeDetector()->detectMimeTypeFromPath('file.' . $extension) ?: '';
    }
    
    
    /**
     * 通过Mimetype获取文件扩展名
     * @param string $mimetype
     * @return string
     */
    public static function getExtensionByMimetype(string $mimetype) : string
    {
        return static::$mimetypeMap[$mimetype] ?? '';
    }
    
    
    /**
     * 解析文件路径
     * @param string   $path 文件路径
     * @param null|int $options <pre>
     * PATHINFO_DIRNAME,
     * PATHINFO_BASENAME,
     * PATHINFO_EXTENSION and
     * PATHINFO_FILENAME
     * </pre>
     * @return string|array
     */
    public static function pathInfo($path, $options = null)
    {
        // 不包含中文走系统
        $match = preg_match('/[\x{4e00}-\x{9fa5}]/u', $path);
        if (false === $match || $match <= 0) {
            if (isset($options)) {
                return pathinfo($path, $options);
            }
            
            return pathinfo($path);
        }
        
        $pathParts              = [];
        $pathParts['dirname']   = rtrim(substr($path, 0, strrpos($path, '/')), "/") . "/";
        $pathParts['basename']  = ltrim(substr($path, strrpos($path, '/')), "/");
        $pathParts['extension'] = substr(strrchr($path, '.'), 1);
        $pathParts['filename']  = ltrim(substr($pathParts ['basename'], 0, strrpos($pathParts ['basename'], '.')), "/");
        
        switch ($options) {
            case PATHINFO_EXTENSION:
                return $pathParts['extension'];
            case PATHINFO_BASENAME:
                return $pathParts['basename'];
            case PATHINFO_DIRNAME:
                return $pathParts['dirname'];
            case PATHINFO_FILENAME:
                return $pathParts['filename'];
        }
        
        return $pathParts;
    }
    
    
    /**
     * 文件写入
     * @param string $filename 文件地址，不存在会自动创建
     * @param string $string 写入的内容
     * @return bool
     */
    public static function write(string $filename, string $string = '') : bool
    {
        $string = $string ?? '';
        $path   = dirname($filename);
        if (!is_dir($path)) {
            if (!mkdir($path, 0775, true)) {
                return false;
            }
        }
        
        if (false === $handle = fopen($filename, "w")) {
            return false;
        }
        
        if (false === $result = fwrite($handle, $string)) {
            fclose($handle);
            
            return false;
        }
        fclose($handle);
        
        return chmod($filename, 0777);
    }
    
    
    /**
     * 创建文件夹
     * @param string $path 路径
     * @return bool
     */
    public static function createDir(string $path) : bool
    {
        $path = dirname($path);
        if (!is_dir($path)) {
            return mkdir($path, 0777, true);
        }
        
        return true;
    }
    
    
    /**
     * 删除文件夹
     * @param string $path 路径
     * @param bool   $retain 是否保留文件夹，true 保留，false 不保留，默认不保留
     * @return bool
     */
    public static function deleteDir(string $path, bool $retain = false) : bool
    {
        if (!is_dir($path)) {
            return false;
        }
        
        $handle = opendir($path);
        while ($file = readdir($handle)) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $path . '/' . $file;
            if (!is_dir($filePath)) {
                @unlink($filePath);
            } else {
                self::deleteDir($filePath, false);
            }
        }
        closedir($handle);
        
        if ($retain) {
            return true;
        }
        
        return rmdir($path);
    }
    
    
    /**
     * 响应资源文件
     * @param string $filename
     * @param int    $expireSecond
     * @return Response
     */
    public static function responseAssets(string $filename, int $expireSecond = 31536000) : Response
    {
        if (!$filename || !is_file($filename)) {
            throw new HttpException(404, sprintf("资源不存在: %s", $filename));
        }
        
        $header  = [];
        $content = file_get_contents($filename);
        $etag    = sprintf('"%s"', md5(filemtime($filename) . $content));
        if (str_replace('W/', '',Request::header('if-none-match')) == $etag) {
            $content                  = null;
            $code                     = 304;
            $header['Content-Length'] = 0;
        } else {
            $code                     = 200;
            $header['Content-Length'] = strlen($content);
        }
        
        $header['Cache-Control'] = "max-age=$expireSecond, public";
        $header['Etag']          = $etag;
        
        return Response::create($content)
            ->contentType(static::getMimetypeByPath($filename) ?: 'text/plain')
            ->code($code)
            ->header($header);
    }
}