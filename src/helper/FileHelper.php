<?php
declare(strict_types = 1);

namespace BusyPHP\helper;

use BusyPHP\exception\FileNotFoundException;
use FilesystemIterator;
use InvalidArgumentException;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use League\MimeTypeDetection\MimeTypeDetector;
use LengthException;
use RangeException;
use think\exception\FileException;
use think\exception\HttpException;
use think\facade\Request;
use think\File;
use think\file\UploadedFile;
use think\Response;
use Throwable;

/**
 * 文件操作辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午上午10:12 FileHelper.php $
 */
class FileHelper
{
    /** @var string[] 常用mimetype类型 */
    public static $mimetypeMap = [
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
    public static $mimeTypeDetector;
    
    /**
     * 支持 {@see getimagesize()} 获取图片信息的扩展集合
     * @var string[]
     */
    public static $getImageSizeExtensions = [
        'gif',
        'jpg',
        'jpeg',
        'png',
        'swf',
        'swc',
        'psd',
        'tiff',
        'bmp',
        'iff',
        'jp2',
        'jpx',
        'jb2',
        'jpc',
        'xbm',
        'wbmp',
        'webp'
    ];
    
    /**
     * 常用图片格式
     * @var string[]
     */
    public static $commonImageExtensions = [
        'jpeg',
        'jpg',
        'png',
        'gif',
        'bmp',
        'webp'
    ];
    
    /**
     * 上传错误
     * @var string[]
     */
    public static $uploadErrorMap = [
        UPLOAD_ERR_INI_SIZE   => '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值',
        UPLOAD_ERR_FORM_SIZE  => '上传文件的大小超过了表单中 MAX_FILE_SIZE 选项指定的值',
        UPLOAD_ERR_PARTIAL    => '文件只有部分被上传',
        UPLOAD_ERR_NO_FILE    => '没有文件被上传',
        UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
        UPLOAD_ERR_CANT_WRITE => '文件写入失败',
        UPLOAD_ERR_EXTENSION  => 'PHP扩展停止了文件上传',
    ];
    
    
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
            throw new FileNotFoundException($path);
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
     * 检测文件扩展名是否合规
     * @param string[] $extensions 允许的文件扩展名集合
     * @param string   $extension 要检测的文件扩展名
     */
    public static function checkExtension(array $extensions, string $extension)
    {
        $extensions = array_change_key_case($extensions, CASE_LOWER);
        $extension  = strtolower(trim($extension, '.'));
        if ($extension === '') {
            throw new InvalidArgumentException('文件扩展名不能为空');
        }
        
        // 非法文件
        if (in_array($extension, ['php', 'php5', 'asp', 'jsp'])) {
            throw new RangeException(sprintf('非法文件类型: %s', $extension));
        }
        
        if ($extensions && !in_array($extension, $extensions)) {
            throw new RangeException(sprintf('文件格式不正确: %s', $extension));
        }
    }
    
    
    /**
     * 检测图片是否合规
     * @param string $filename 图片路径或文件内容
     * @param string $extension 文件扩展名
     * @param bool   $content $filename是否为文件内容
     * @return array
     */
    public static function checkImage(string $filename, string $extension = '', bool $content = false) : array
    {
        if ($content) {
            if (!$extension) {
                throw new InvalidArgumentException('文件扩展名不能为空');
            }
        } else {
            $extension = strtolower($extension ?: pathinfo($filename, PATHINFO_EXTENSION));
        }
        
        if (!in_array($extension, static::$getImageSizeExtensions)) {
            return [0, 0];
        }
        
        $info = $content ? getimagesizefromstring($filename) : getimagesize($filename);
        if (!$info || ('gif' == $extension && empty($info['bits'])) || $info[0] <= 0 || $info[1] <= 0) {
            throw new RangeException('非法图像文件');
        }
        
        return $info;
    }
    
    
    /**
     * 检测文件Mimetype是否合规
     * @param array  $mimetypes 允许的mimetype集合
     * @param string $mimetype 要检测的mimetype
     */
    public static function checkMimetype(array $mimetypes, string $mimetype)
    {
        if (!$mimetypes) {
            return;
        }
        
        $mimetypes = array_change_key_case($mimetypes, CASE_LOWER);
        $mimetype  = strtolower($mimetype);
        foreach ($mimetypes as $item) {
            if (false !== strpos($item, '/*')) {
                $pattern = str_replace('/', '\/', $item);
                $pattern = str_replace('*', '.*', $pattern);
                if (preg_match('/^' . $pattern . '$/i', $mimetype)) {
                    return;
                }
            } else {
                if (in_array($mimetype, $mimetypes)) {
                    return;
                }
            }
        }
        
        throw new RangeException(sprintf("不支持该mimetype: %s", $mimetype));
    }
    
    
    /**
     * 检测文件大小是否合规
     * @param int $maxsize 最大限制
     * @param int $filesize 文件大小
     */
    public static function checkFilesize(int $maxsize, int $filesize)
    {
        if ($filesize <= 0) {
            throw new LengthException('禁止上传空文件');
        }
        
        if ($maxsize > 0 && $filesize > $maxsize) {
            throw new LengthException(sprintf('请上传%s内的文件', TransHelper::formatBytes($maxsize)));
        }
    }
    
    
    /**
     * 将上传的文件转为File对象
     * @param File|string|array $file
     * @return File
     */
    public static function convertUploadToFile($file) : File
    {
        if (!$file) {
            throw new InvalidArgumentException('没有要上传的数据');
        }
        
        if (!$file instanceof File) {
            // 通过键取文件
            if (is_string($file)) {
                $file = Request::file($file);
                if (!$file) {
                    throw new InvalidArgumentException('没有文件被上传');
                }
                
                if (is_array($file)) {
                    if (count($file) > 1) {
                        throw new RangeException('不支持同时上传多个文件，请分开上传');
                    }
                    
                    $file = $file[0];
                }
            }
            
            //
            // 是$_FILES
            elseif (is_array($file) && isset($file['name'])) {
                if (is_array($file['name'])) {
                    if (count($file['name']) > 1) {
                        throw new RangeException('不支持同时上传多个文件，请分开上传');
                    }
                    
                    $newFile = [
                        'name'     => $file['name'][0],
                        'tmp_name' => $file['tmp_name'][0],
                        'type'     => $file['type'][0],
                        'error'    => $file['error'][0],
                    ];
                    $file    = $newFile;
                }
                
                if ($file['error'] > 0) {
                    throw new FileException(static::$uploadErrorMap[$file['error']] ?? "上传错误{$file['error']}", $file['error']);
                }
                
                $file = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['error']);
            }
            
            //
            // 其它数据
            else {
                throw new RangeException('上传数据异常');
            }
        }
        
        return $file;
    }
    
    
    /**
     * 通过路径判断是否常见图片
     * @param string $path
     * @return bool
     */
    public static function isCommonImageByPath(string $path) : bool
    {
        return static::isCommonImageByExtension(pathinfo($path, PATHINFO_EXTENSION));
    }
    
    
    /**
     * 通过扩展名判断是否常见图片
     * @param string $extension
     * @return bool
     */
    public static function isCommonImageByExtension(string $extension) : bool
    {
        return in_array(strtolower($extension), self::$commonImageExtensions);
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
     * 写入内容到文件
     * @param string $filename 文件路径
     * @param string $content 文件内容
     * @param int    $writeFlags 写入
     * @param int    $dirPermissions 目录权限
     * @param int    $filePermissions 文件权限
     * @return bool
     */
    public static function write(string $filename, string $content = '', $writeFlags = LOCK_EX, int $dirPermissions = 0775, int $filePermissions = 0777) : bool
    {
        if (!static::createDir(dirname($filename), $dirPermissions)) {
            return false;
        }
        
        if (file_put_contents($filename, $content, $writeFlags) === false) {
            return false;
        }
        
        return chmod($filename, $filePermissions);
    }
    
    
    /**
     * 删除文件
     * @param string $path
     * @return bool
     */
    public static function delete(string $path) : bool
    {
        if (!is_file($path)) {
            return false;
        }
        
        try {
            return unlink($path);
        } catch (Throwable $e) {
            return false;
        }
    }
    
    
    /**
     * 创建文件夹
     * @param string $path 路径
     * @param int    $permissions 权限
     * @return bool
     */
    public static function createDir(string $path, int $permissions = 0777) : bool
    {
        $umask = umask(0);
        if (!is_dir($path)) {
            if (false === @mkdir($path, $permissions, true) || false === is_dir($path)) {
                return false;
            }
        }
        umask($umask);
        
        return true;
    }
    
    
    /**
     * 删除文件夹
     * @param string $path 路径
     * @return bool
     */
    public static function deleteDir(string $path) : bool
    {
        if (!is_dir($path)) {
            return false;
        }
        
        $items = new FilesystemIterator($path);
        
        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                static::deleteDir($item->getPathname());
            } else {
                static::delete($item->getPathname());
            }
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
            throw new HttpException(404, sprintf("The resource does not exist: %s", $filename));
        }
        
        $header  = [];
        $content = file_get_contents($filename);
        $etag    = sprintf('"%s"', md5(filemtime($filename) . $content));
        if (str_replace('W/', '', Request::header('if-none-match', '')) == $etag) {
            $content                  = null;
            $code                     = 304;
            $header['Content-Length'] = 0;
        } else {
            $code                     = 200;
            $header['Content-Length'] = strlen($content);
        }
        
        $header['Cache-Control'] = sprintf("max-age=%s, public", $expireSecond);
        $header['Etag']          = $etag;
        
        return Response::create($content)
            ->contentType(static::getMimetypeByPath($filename) ?: 'text/plain')
            ->code($code)
            ->header($header);
    }
}