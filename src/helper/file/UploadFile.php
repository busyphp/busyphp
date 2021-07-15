<?php

namespace BusyPHP\helper\file;

use BusyPHP\helper\net\Http;
use BusyPHP\exception\AppException;
use BusyPHP\Request;
use think\Container;
use think\helper\Str;

/**
 * 附件上传类
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2018 busy^life <busy.life@qq.com>
 * @version $Id: 2018-01-22 下午7:51 UploadFile.php busy^life $
 */
class UploadFile
{
    //+--------------------------------------
    //| 上传方式
    //+--------------------------------------
    /** 普通上传 */
    const TYPE_FILE = 1;
    
    /** BASE64上传 */
    const TYPE_BASE64 = 2;
    
    /** 远程下载 */
    const TYPE_REMOTE = 3;
    
    /** 分片上传 */
    const TYPE_CHUNK = 4;
    
    /** 附件移动方式上传 */
    const TYPE_MOVE = 5;
    
    //+--------------------------------------
    //| 目录命名方式
    //+--------------------------------------
    /** hash方式命名 */
    const FOLDER_NAME_METHOD_HASH = 'hash';
    
    /** 日期方式命名 */
    const FOLDER_NAME_METHOD_DATE = 'date';
    
    /** @var array 配置 */
    protected $options = [
        'type'          => self::TYPE_FILE,
        'limit'         => [
            'size' => 0,
            'ext'  => [],
            'mime' => [],
        ],
        'root_path'     => '',
        'tmp_path'      => '',
        'root_url'      => '/',
        'replace_cover' => true,
        'folder'        => [
            'name_method' => self::FOLDER_NAME_METHOD_HASH,
            'name_format' => 1
        ],
        'file'          => [
            'name_method' => 'uniqid',
            'hash_method' => 'md5_file',
        ],
        'remote'        => [
            'ext'         => '',
            'mime'        => '',
            'filter_host' => [],
            'curl_opt'    => [],
            'filename'    => '',
        ],
        'base64'        => [
            'ext'      => '',
            'mime'     => '',
            'filename' => ''
        ],
        'chunk'         => [
            'guid_key'     => 'guid',
            'total_key'    => 'chunks',
            'index_key'    => 'chunk',
            'complete_key' => 'is_complete',
            'filename_key' => 'filename'
        ]
    ];
    
    /** @var UploadFileResult 附件结果容器 */
    protected $uploadResult = null;
    
    /** @var array mimeType映射 */
    public static $mimeTypes = [
        'image/gif'    => 'gif',
        'image/png'    => 'png',
        'image/jpeg'   => 'jpeg',
        'image/jpg'    => 'jpg',
        'image/x-icon' => 'ico'
    ];
    
    
    /**
     * 设置限制上传大小
     * @param int $size 0为不限
     * @return $this
     */
    public function setLimitMaxSize($size)
    {
        $this->options['limit']['size'] = floatval($size);
        
        return $this;
    }
    
    
    /**
     * 设置限制扩展名
     * @param array|string $extensions 允许的扩展名如: jpg,jpeg,png 或者 array('jpg', 'jpeg', 'png')
     * @return $this
     */
    public function setLimitExtensions($extensions)
    {
        if (!$extensions) {
            $this->options['limit']['ext'] = [];
            
            return $this;
        }
        
        if (!is_array($extensions)) {
            $extensions = explode(',', $extensions);
        }
        
        $extensions = array_map('trim', $extensions);
        $extensions = array_filter($extensions);
        $extensions = array_unique($extensions);
        
        $this->options['limit']['ext'] = $extensions;
        
        return $this;
    }
    
    
    /**
     * 设置限制上传的mime类型
     * @param string|array $mime 允许的扩展名如: image/*,image/png 或者 array('image/*', 'image/png')
     * @return $this
     */
    public function setLimitMimeTypes($mime)
    {
        if (!$mime) {
            $this->options['limit']['mime'] = [];
            
            return $this;
        }
        
        if (!is_array($mime)) {
            $mime = explode(',', $mime);
        }
        $mime = array_map('trim', $mime);
        $mime = array_filter($mime);
        $mime = array_unique($mime);
        
        $this->options['limit']['mime'] = $mime;
        
        return $this;
    }
    
    
    /**
     * 设置上传方式
     * @param int $type TYPE_REMOTE, TYPE_BASE64, TYPE_FILE
     * @return $this
     */
    public function setType($type)
    {
        $this->options['type'] = intval($type);
        
        return $this;
    }
    
    
    /**
     * 设置上传保存基本目录入口路径
     * @param string $root
     * @return $this
     */
    public function setRootPath($root)
    {
        $this->options['root_path'] = rtrim($root, '/') . '/';
        
        return $this;
    }
    
    
    /**
     * 设置URL基本入口
     * @param string $root
     * @return $this
     */
    public function setRootUrl($root)
    {
        $root = trim($root, '/');
        if (!$root) {
            $this->options['root_url'] = '/';
        }
        
        $this->options['root_url'] = '/' . $root . '/';
        
        return $this;
    }
    
    
    /**
     * 设置上传临时目录路径
     * @param string $tmp
     * @return $this
     */
    public function setTmpPath($tmp)
    {
        $this->options['tmp_path'] = rtrim($tmp, '/') . '/';
        
        return $this;
    }
    
    
    /**
     * 设置同名是否覆盖，默认覆盖
     * @param boolean $status
     * @return $this
     */
    public function setReplaceCover($status)
    {
        $this->options['replace_cover'] = $status ? true : false;
        
        return $this;
    }
    
    
    /**
     * 设置目录命名方法
     * @param string|callable $method 方法名称或回调函数
     * @param string          $format 格式，为hash是数字代表目录深度,为date是日期格式,其它方法无效
     * @return $this
     */
    public function setFolderNameMethod($method, $format = '')
    {
        $this->options['folder']['name_method'] = $method;
        $this->options['folder']['name_format'] = trim($format);
        
        return $this;
    }
    
    
    /**
     * 设置文件命名方法和hash方法
     * @param string|callable $method 命名方法
     * @param string|callable $hashMethod hash方法
     * @return $this
     */
    public function setFileNameMethod($method, $hashMethod = null)
    {
        $this->options['file']['name_method'] = $method;
        $this->options['file']['hash_method'] = $hashMethod ? $hashMethod : 'md5_file';
        
        return $this;
    }
    
    
    /**
     * 设置远程下载的默认扩展名
     * @param string $extension
     * @param string $mime
     * @return $this
     */
    public function setRemoteDefaultExtension($extension, $mime = '')
    {
        $this->options['remote']['ext']  = trim($extension, '.');
        $this->options['remote']['mime'] = strtolower(trim($mime));
        
        return $this;
    }
    
    
    /**
     * 设置远程下载配置
     * @param string $filename 文件名称
     * @param string $extension 文件后缀
     * @param string $mime Mime类型
     * @return $this
     */
    public function setRemoteConfig($filename, $extension, $mime = '')
    {
        $this->options['remote']['filename'] = $filename;
        $this->options['remote']['ext']      = is_callable($extension) ? $extension : trim($extension, '.');
        $this->options['remote']['mime']     = strtolower(trim($mime));
        
        return $this;
    }
    
    
    /**
     * 设置过滤的远程域名
     * @param string|array $hosts
     * @return $this
     */
    public function setRemoteFilterHost($hosts)
    {
        $this->options['remote']['filter_host'] = is_array($hosts) ? $hosts : explode(',', $hosts);
        
        return $this;
    }
    
    
    /**
     * 设置远程下载CURL配置
     * @param array $options
     * @return $this
     */
    public function setRemoteCurlOptions($options)
    {
        $this->options['remote']['curl_opt'] = $options;
        
        return $this;
    }
    
    
    /**
     * 设置Base64上传的默认扩展名
     * @param string $extension 文件后缀
     * @param string $mime Mime类型
     * @return $this
     */
    public function setBase64DefaultExtension($extension, $mime = '')
    {
        $this->options['base64']['ext']  = trim($extension, '.');
        $this->options['base64']['mime'] = strtolower(trim($mime));
        
        return $this;
    }
    
    
    /**
     * 设置Base64上传配置
     * @param string $filename 文件名称
     * @param string $extension 文件后缀
     * @param string $mime Mime类型
     */
    public function setBase64Config($filename, $extension, $mime = '')
    {
        $this->options['base64']['filename'] = $filename;
        $this->options['base64']['ext']      = trim($extension, '.');
        $this->options['base64']['mime']     = strtolower(trim($mime));
    }
    
    
    /**
     * 设置分片上传键名
     * @param string $guid
     * @param string $total
     * @param string $index
     * @param string $completeKey
     * @param string $filenameKey
     * @return $this
     */
    public function setChunkField($guid = 'guid', $total = 'chunks', $index = 'chunk', $completeKey = 'is_complete', $filenameKey = 'filename')
    {
        $this->options['chunk']['guid_key']     = trim($guid);
        $this->options['chunk']['total_key']    = trim($total);
        $this->options['chunk']['index_key']    = trim($index);
        $this->options['chunk']['complete_key'] = trim($completeKey);
        $this->options['chunk']['filename_key'] = trim($filenameKey);
        
        return $this;
    }
    
    
    /**
     * 执行附件上传
     * @param mixed $data 远程下载则data为url，base64上传则data为字符串，普通上传则$_FILES[key]数组
     * @return UploadFileResult
     * @throws AppException
     */
    public function upload($data)
    {
        switch ($this->options['type']) {
            case self::TYPE_REMOTE:
                return $this->uploadByRemote($data);
            
            case self::TYPE_BASE64:
                return $this->uploadByBase64($data);
            
            case self::TYPE_CHUNK:
                return $this->uploadByChunk($data);
            
            case self::TYPE_MOVE:
                return $this->uploadByMove($data);
            break;
            
            default:
                return $this->uploadByFile($data);
        }
    }
    
    
    /**
     * 分片上传
     * @param $data
     * @return UploadFileResult|true
     * @throws AppException
     */
    public function uploadByChunk($data)
    {
        /** @var Request $request */
        $request = Container::getInstance()->make(Request::class);
        
        $guid             = trim($request->post($this->options['chunk']['guid_key']));
        $total            = intval($request->post($this->options['chunk']['total_key']));
        $index            = intval($request->post($this->options['chunk']['index_key']));
        $isComplete       = intval($request->post($this->options['chunk']['complete_key'])) > 0;
        $completeFilename = trim($request->post($this->options['chunk']['filename_key']));
        
        // 非分片上传走普通上传
        if (!$isComplete && $total <= 1 && $index <= 0) {
            return $this->uploadByFile($data);
        }
        
        // 校验GUID
        if (!$guid) {
            throw new AppException('分片上传缺少guid');
        }
        
        // 初始化参数
        $this->parseOptions();
        $tmpPath    = $this->options['tmp_path'] . 'chunks/';
        $folderName = md5($guid);
        $folder     = [];
        for ($i = 0; $i < 3; $i++) {
            $folder[] = $folderName[$i];
        }
        $tmpPath .= implode('/', $folder) . '/';
        if (!is_dir($tmpPath)) {
            if (!mkdir($tmpPath, 0775, true)) {
                throw new AppException("创建临时目录{$tmpPath}失败");
            }
        }
        
        // 合并附件
        $this->uploadResult = new UploadFileResult();
        if ($isComplete) {
            if (!$completeFilename) {
                throw new AppException('附件名称不能为空');
            }
            
            $extension = File::getExtension($completeFilename);
            if (!$extension) {
                throw new AppException('无法获取附件扩展名');
            }
            
            // 读取日志文件
            $log     = md5($completeFilename . $guid . $_SERVER['HTTP_USER_AGENT']) . '.log';
            $logName = $tmpPath . $log;
            if (!$config = unserialize(file_get_contents($logName))) {
                throw new AppException('文件上传保存错误[log]');
            }
            if (!$config['list']) {
                throw new AppException('文件上传保存错误[list]');
            }
            
            // 文件的总数不等于开始的统计条数认为无效
            if (count($config['list']) != $config['total']) {
                array_map('unlink', $config['list']);
                throw new AppException('文件上传保存错误[total]');
            }
            
            // 遍历合并文件
            ksort($config['list']);
            $this->parseSaveFilePath($completeFilename, $extension);
            if (false === $resource = fopen($this->uploadResult->savePath, 'wb')) {
                array_map('unlink', $config['list']);
                throw new AppException('文件上传保存错误[open]');
            }
            
            flock($resource, LOCK_EX);
            foreach ($config['list'] as $i => $item) {
                if (!is_file($item) || false === $tmpFile = fopen($item, 'rb')) {
                    array_map('unlink', $config['list']);
                    throw new AppException('文件上传保存错误[item.open]');
                }
                
                if (false === $tmpStr = fread($tmpFile, filesize($item))) {
                    array_map('unlink', $config['list']);
                    throw new AppException('文件上传保存错误[item.read]');
                }
                
                fclose($tmpFile);
                if (false === fwrite($resource, $tmpStr)) {
                    throw new AppException('文件上传保存错误[item.write]');
                }
            }
            fclose($resource);
            
            // 删除分片
            array_map('unlink', $config['list']);
            
            // 删除日志
            unlink($logName);
            
            // 校验附件
            if (false === $size = self::isFile($this->uploadResult)) {
                throw new AppException('保存的附件无效');
            }
            
            try {
                // 校验大小
                $this->checkSize($size);
                
                // 校验图片
                self::checkImage($this->uploadResult->savePath, $extension);
            } catch (AppException $e) {
                unlink($this->uploadResult->savePath);
                throw new AppException($e);
            }
            
            $this->uploadResult->name      = $completeFilename;
            $this->uploadResult->size      = $size;
            $this->uploadResult->mimeType  = $config['mime'];
            $this->uploadResult->extension = $extension;
            $this->uploadResult->hash      = $this->parseHash($this->uploadResult->savePath);
            $this->uploadResult->url       = $this->parseUrl($this->uploadResult->folderPath, $this->uploadResult->filename);
            
            return $this->uploadResult;
        }
        
        
        // 分片上传
        if (!$data) {
            throw new AppException('没有附件被上传');
        }
        
        // 上传错误
        if ($data['error'] != UPLOAD_ERR_OK) {
            throw new AppException(static::parseFileError($data['error']));
        }
        
        // 校验是否上传文件
        $this->checkExtension(File::getExtension($data['name']));
        $this->checkMimeTypes($data['type']);
        $this->checkIsUploadFile($data['tmp_name']);
        
        $name     = md5($data['name'] . $guid . $index . $total . $_SERVER['HTTP_USER_AGENT']) . '.tmp';
        $log      = md5($data['name'] . $guid . $_SERVER['HTTP_USER_AGENT']) . '.log';
        $filename = $tmpPath . $name;
        $logName  = $tmpPath . $log;
        
        // 上传附件
        if (!move_uploaded_file($data['tmp_name'], $filename)) {
            throw new AppException('移动分片到临时目录失败');
        }
        
        // 校验附件
        if (false === self::isFile($filename)) {
            throw new AppException('保存的分片无效');
        }
        
        // 写入日志
        if (!is_file($logName)) {
            $logs          = [];
            $logs['total'] = $total;
            $logs['mime']  = $data['type'];
            $logs['list']  = [];
        } else {
            $logs = unserialize(file_get_contents($logName));
            if (!$logs) {
                $logs['total'] = $total;
                $logs['mime']  = $data['type'];
                $logs['list']  = [];
            }
        }
        
        try {
            $logs['list'][$index] = $filename;
            if (false === $resource = fopen($logName, 'w')) {
                throw new AppException('分片日志写入失败[open]');
            }
            flock($resource, LOCK_EX);
            if (false === fwrite($resource, serialize($logs))) {
                throw new AppException('分片日志写入失败[write]');
            }
            fclose($resource);
        } catch (AppException $e) {
            unlink($filename);
            throw new AppException($e);
        }
        
        return true;
    }
    
    
    /**
     * Base64上传附件
     * @param string $base64
     * @return UploadFileResult
     * @throws AppException
     */
    public function uploadByBase64($base64)
    {
        $this->parseOptions();
        $base64 = trim($base64);
        if (!$base64) {
            throw new AppException('没有上传数据');
        }
        
        // 获取附件扩展名
        $extension = '';
        $mimeType  = '';
        if ($status = preg_match('/^(data:\s*(.*);\s*base64,)/i', $base64, $result)) {
            $mimeType  = strtolower($result[2]);
            $extension = static::$mimeTypes[$mimeType];
            $base64    = str_replace($result[1], '', $base64);
        }
        
        $mimeType  = !empty($mimeType) ? $mimeType : $this->options['base64']['mime'];
        $extension = !empty($extension) ? $extension : $this->options['base64']['ext'];
        if (!$extension) {
            throw new AppException('无法获取附件扩展名');
        }
        
        $this->checkMimeTypes($mimeType);
        $this->checkExtension($extension);
        
        
        $name = 'BASE64_' . date('YmdHis') . '_' . Str::random(6) . '.' . $extension;
        if (isset($this->options['base64']['filename']) && $this->options['base64']['filename']) {
            $name = $this->options['base64']['filename'];
        }
        if (!$file = base64_decode(str_replace(' ', '+', $base64))) {
            throw new AppException('上传数据解密失败');
        }
        $size = strlen($file);
        $this->checkSize($size);
        
        // 上传附件
        $this->uploadResult = new UploadFileResult();
        $this->parseSaveFilePath($name, $extension);
        
        // 写入文件
        if (false === $resource = fopen($this->uploadResult->savePath, 'w')) {
            throw new AppException('附件写入失败[open]');
        }
        if (false === fwrite($resource, $file)) {
            throw new AppException('附件写入失败[write]');
        }
        fclose($resource);
        
        // 校验文件
        if (false === self::isFile($this->uploadResult)) {
            throw new AppException('保存数据无效');
        }
        
        // 校验图片
        try {
            self::checkImage($this->uploadResult->savePath, $extension);
        } catch (AppException $e) {
            unlink($this->uploadResult->savePath);
            throw new AppException($e);
        }
        
        $this->uploadResult->name      = $name;
        $this->uploadResult->size      = $size;
        $this->uploadResult->mimeType  = $mimeType;
        $this->uploadResult->extension = $extension;
        $this->uploadResult->hash      = $this->parseHash($this->uploadResult->savePath);
        $this->uploadResult->url       = $this->parseUrl($this->uploadResult->folderPath, $this->uploadResult->filename);
        
        return $this->uploadResult;
    }
    
    
    /**
     * 远程下载附件
     * @param string $url 下载地址
     * @return UploadFileResult
     * @throws AppException
     */
    public function uploadByRemote($url)
    {
        $url = trim($url);
        if (!$url) {
            throw new AppException('缺少下载地址');
        }
        
        // 过滤系统资源
        $urls    = parse_url($url);
        $hosts   = $this->options['remote']['filter_host'];
        $hosts   = is_array($hosts) ? $hosts : [];
        $hosts[] = $_SERVER['HTTP_HOST'];
        $hosts   = array_filter($hosts);
        $hosts   = array_unique($hosts);
        if (in_array($urls['host'], $hosts)) {
            throw new AppException('系统禁止下载[' . $urls['host'] . ']域名下的附件');
        }
        
        // 拼接获取扩展名的字符串
        $extensionStr = $urls['path'];
        if ($urls['query']) {
            $extensionStr .= '?' . $urls['query'];
        }
        
        // 获取扩展名
        if (!is_string($this->options['remote']['ext']) && is_callable($this->options['remote']['ext'])) {
            $extension = call_user_func_array($this->options['remote']['ext'], [$urls]);
        } else {
            if (false !== strpos($extensionStr, '.')) {
                $extension = trim(substr($extensionStr, strrpos($extensionStr, '.') + 1));
            }
            $extension = !empty($extension) ? $extension : $this->options['remote']['ext'];
        }
        if (!$extension) {
            throw new AppException('无法获取附件扩展名');
        }
        
        // 生成文件名称
        $name = 'REMOTE_' . date('YmdHis') . '_' . Str::random(6) . '.' . $extension;
        if (isset($this->options['remote']['filename']) && $this->options['remote']['filename']) {
            $name = $this->options['remote']['filename'];
        }
        
        // 初始化选项
        $this->parseOptions();
        $http = Http::init();
        $http->setOpt(CURLOPT_POST, false);
        $http->setUrl($url);
        $http->setUserAgent($_SERVER['HTTP_USER_AGENT']);
        if (isset($this->options['remote']['curl_opt'])) {
            $http->setOpt($this->options['remote']['curl_opt']);
        }
        
        try {
            $fileContent = $http->request();
            
            $this->options['remote']['curl_info'] = $http->getOptions();
            if (!$fileContent || $http->getResponseStatusCode() != 200) {
                throw new AppException('附件下载失败');
            }
        } catch (AppException $e) {
            $this->options['remote']['curl_info'] = $http->getOptions();
            throw new AppException($e);
        }
        
        
        $size = strlen($fileContent);
        $this->checkSize($size);
        $this->checkMimeTypes($http->getResponseContentType());
        $this->checkExtension($extension);
        
        // 实例化容器
        $this->uploadResult = new UploadFileResult();
        $this->parseSaveFilePath($name, $extension);
        
        // 写入文件
        if (false === $resource = fopen($this->uploadResult->savePath, 'w')) {
            throw new AppException('附件写入失败[open]');
        }
        if (false === fwrite($resource, $fileContent)) {
            throw new AppException('附件写入失败[write]');
        }
        fclose($resource);
        
        // 校验文件
        if (false === self::isFile($this->uploadResult)) {
            throw new AppException('保存的附件无效');
        }
        
        
        // 校验图片
        try {
            self::checkImage($this->uploadResult->savePath, $extension);
        } catch (AppException $e) {
            //unlink($this->uploadResult->savePath);
            throw new AppException($e);
        }
        
        
        $this->uploadResult->name      = $name;
        $this->uploadResult->size      = $size;
        $this->uploadResult->mimeType  = $http->getResponseContentType();
        $this->uploadResult->extension = $extension;
        $this->uploadResult->hash      = $this->parseHash($this->uploadResult->savePath);
        $this->uploadResult->url       = $this->parseUrl($this->uploadResult->folderPath, $this->uploadResult->filename);
        
        return $this->uploadResult;
    }
    
    
    /**
     * 移动附件至系统目录
     * @param $filename
     * @return UploadFileResult
     * @throws AppException
     */
    public function uploadByMove($filename)
    {
        if (!is_file($filename)) {
            throw new AppException('被移动的附件不存在');
        }
        
        // 初始化选项参数
        $this->parseOptions();
        
        $this->uploadResult = new UploadFileResult();
        
        // 校验文件
        $extension = File::getExtension($filename);
        $mimeType  = File::getMimeType($filename);
        $fileSize  = filesize($filename);
        $this->checkSize($fileSize);
        $this->checkExtension($extension);
        $this->checkMimeTypes($mimeType);
        self::checkImage($filename, $extension);
        
        // 获取文件名
        $name = File::pathInfo($filename, PATHINFO_BASENAME);
        
        // 执行文件移动
        if (!rename($filename, $this->parseSaveFilePath($name, $extension))) {
            throw new AppException('移动文件失败');
        }
        if (false === self::isFile($this->uploadResult)) {
            throw new AppException('移动的附件无效');
        }
        
        $this->uploadResult->name      = $name;
        $this->uploadResult->size      = $fileSize;
        $this->uploadResult->mimeType  = $mimeType;
        $this->uploadResult->extension = $extension;
        $this->uploadResult->hash      = $this->parseHash($this->uploadResult->savePath);
        $this->uploadResult->url       = $this->parseUrl($this->uploadResult->folderPath, $this->uploadResult->filename);
        
        return $this->uploadResult;
    }
    
    
    /**
     * 正常上传
     * @param array $file 附件数据
     * @return UploadFileResult
     * @throws AppException
     */
    public function uploadByFile($file)
    {
        // 校验文件
        if (!$file) {
            throw new AppException('没有附件被上传');
        }
        
        // 上传错误
        if ($file['error'] != UPLOAD_ERR_OK) {
            throw new AppException(static::parseFileError($file['error']));
        }
        
        // 校验文件
        $extension = File::getExtension($file['name']);
        $this->checkIsUploadFile($file['tmp_name']);
        $this->checkSize($file['size']);
        $this->checkMimeTypes($file['type']);
        $this->checkExtension($extension);
        self::checkImage($file['tmp_name'], $extension);
        
        
        // 初始化选项参数
        $this->parseOptions();
        
        // 移动文件至上传的目录
        $this->uploadResult = new UploadFileResult();
        if (!move_uploaded_file($file['tmp_name'], $this->parseSaveFilePath($file['name']))) {
            throw new AppException('文件上传保存错误');
        }
        
        if (false === self::isFile($this->uploadResult)) {
            throw new AppException('上传附件无效');
        }
        
        $this->uploadResult->name      = $file['name'];
        $this->uploadResult->size      = filesize($this->uploadResult->savePath);
        $this->uploadResult->mimeType  = $file['type'];
        $this->uploadResult->extension = $extension;
        $this->uploadResult->hash      = $this->parseHash($this->uploadResult->savePath);
        $this->uploadResult->url       = $this->parseUrl($this->uploadResult->folderPath, $this->uploadResult->filename);
        
        return $this->uploadResult;
    }
    
    
    /**
     * 解析URL
     * @param string $folderPath
     * @param string $filename
     * @return string
     */
    protected function parseUrl($folderPath, $filename)
    {
        $folderPath = trim($folderPath, '/');
        if (!$folderPath) {
            return $this->options['root_url'] . rawurlencode($filename);
        } else {
            return $this->options['root_url'] . $folderPath . '/' . rawurlencode($filename);
        }
    }
    
    
    /**
     * 解析附件hash
     * @param $filename
     * @return string
     */
    protected function parseHash($filename)
    {
        $hashMethod = $this->options['file']['hash_method'];
        if (!$hashMethod) {
            $hash = md5_file($filename);
        } elseif (is_callable($hashMethod)) {
            $hash = call_user_func_array($hashMethod, [$filename]);
        } elseif (function_exists($hashMethod)) {
            $hash = $hashMethod($filename);
        } else {
            $hash = md5_file($filename);
        }
        
        return $hash;
    }
    
    
    /**
     * 解析出要保存的文件路径
     * @param string $name 文件名称
     * @param string $extension 文件扩展名
     * @return string
     * @throws AppException
     */
    protected function parseSaveFilePath($name, $extension = null)
    {
        $savePath  = $this->options['root_path'];
        $extension = trim($extension, '.');
        $extension = $extension ? $extension : File::getExtension($name);
        
        // 文件夹
        $folder = $this->parseSaveFolder($name . time());
        if ($folder) {
            $savePath .= $folder . '/';
            if (!is_dir($savePath)) {
                if (!mkdir($savePath, 0775, true)) {
                    throw new AppException("上传目录[{$savePath}]创建失败，请检查目录权限");
                }
            }
            if (!is_writeable($savePath)) {
                throw new AppException("上传目录[{$savePath}]不可写，请检查目录权限");
            }
        }
        
        $method   = $this->options['file']['name_method'];
        $filename = $name;
        if (!is_string($method) && is_callable($method)) {
            $filename = call_user_func_array($method, [$name, $extension]);
        } elseif (!empty($method)) {
            if (function_exists($method)) {
                $filename = $method() . '.' . $extension;
            } else {
                $filename = $method . '.' . $extension;
            }
        }
        
        // 转换字符集
        $savePath = $savePath . $filename;
        if (defined('BUSY_PHP_OS_CHARSET') && strtolower(BUSY_PHP_OS_CHARSET) !== 'utf-8') {
            $savePath = self::charset($savePath, 'utf-8', BUSY_PHP_OS_CHARSET);
        }
        
        // 重复不覆盖
        if (!$this->options['replace_cover'] && is_file($savePath)) {
            throw new AppException("该文件已存在[{$filename}]");
        }
        
        if (isset($this->uploadResult)) {
            $this->uploadResult->rootPath   = $this->options['root_path'];
            $this->uploadResult->filename   = $filename;
            $this->uploadResult->folderPath = $folder;
            $this->uploadResult->savePath   = $savePath;
        }
        
        return $savePath;
    }
    
    
    /**
     * 解析要保存的目录
     * @param string $name 文件名称
     * @return string
     */
    protected function parseSaveFolder($name)
    {
        $method = $this->options['folder']['name_method'];
        if (!$method) {
            return '';
        }
        
        // 回调
        if (!is_string($method) && is_callable($method)) {
            return trim(call_user_func_array($method, [$name]), '/');
        }
        
        // 内置方法
        switch (trim($method)) {
            // 日期方法
            case self::FOLDER_NAME_METHOD_DATE:
                $format = trim($this->options['folder']['name_format']);
                $format = $format ? $format : 'Y/m/d';
                $dir    = date($format, time());
            break;
            
            // HASH方法
            case self::FOLDER_NAME_METHOD_HASH:
            default:
                $level = intval($this->options['folder']['name_format']);
                $level = $level <= 1 ? 1 : $level;
                $name  = md5($name);
                $dir   = [];
                for ($i = 0; $i < $level; $i++) {
                    $dir[] = $name[$i];
                }
                $dir = implode('/', $dir);
        }
        
        return trim($dir, '/');
    }
    
    
    /**
     * 初始化选项参数
     * @throws AppException
     */
    protected function parseOptions()
    {
        // 检查上传目录
        $this->options['root_path'] = rtrim($this->options['root_path'], '/') . '/';
        if (!$this->options['root_path']) {
            throw new AppException('请配置上传基本路径[root]');
        }
        
        // 目录不存在则创建
        if (!is_dir($this->options['root_path'])) {
            if (!mkdir($this->options['root_path'], 0775, true)) {
                throw new AppException("上传目录[{$this->options['root_path']}]创建失败，请检查目录权限");
            }
        }
        // 检测是否可写
        if (!is_writeable($this->options['root_path'])) {
            throw new AppException("上传目录[{$this->options['root_path']}]不可写，请检查目录权限");
        }
        
        
        // 临时目录
        if (!$this->options['tmp_path']) {
            $this->options['tmp_path'] = $this->options['root_path'] . 'upload_tmp/';
        }
        if (!is_dir($this->options['tmp_path'])) {
            if (!mkdir($this->options['tmp_path'], 0775, true)) {
                throw new AppException("临时目录[{$this->options['tmp_path']}]创建失败，请检查目录权限");
            }
        }
        // 检查是否可写
        if (!is_writeable($this->options['tmp_path'])) {
            throw new AppException("临时目录[{$this->options['tmp_path']}]不可写，请检查目录权限");
        }
    }
    
    
    /**
     * 校验是否通过上传来的文件
     * @param string $tmpName
     * @throws AppException
     */
    protected function checkIsUploadFile($tmpName)
    {
        if (!is_uploaded_file($tmpName)) {
            throw new AppException('非法上传文件');
        }
    }
    
    
    /**
     * 校验是否在允许上传的mime类型之内
     * @param string $mimeType
     * @throws AppException
     */
    protected function checkMimeTypes($mimeType)
    {
        if (!$this->options['limit']['mime']) {
            return;
        }
        
        $mimeType = strtolower(trim($mimeType));
        foreach ($this->options['limit']['mime'] as $mimeItem) {
            if ($mimeItem == $mimeType) {
                break;
            }
            
            
            if (false !== strpos($mimeItem, '/*')) {
                $mimes = explode('/', $mimeType);
                $items = explode('/', $mimeItem);
                if ($mimes[0] == $items[0]) {
                    break;
                }
            }
        }
        
        throw new AppException('上传文件MIME类型不允许[mime]');
    }
    
    
    /**
     * 校验是否在允许上传的最大字节范围内
     * @param int|string $size
     * @throws AppException
     */
    protected function checkSize($size)
    {
        if ($this->options['limit']['size'] <= 0) {
            return;
        }
        
        $size = floatval($size);
        if ($size <= 0) {
            throw new AppException('上传文件大小不符[size.0]');
        }
        
        if ($size > $this->options['limit']['size']) {
            throw new AppException('上传文件大小不符[size.1]');
        }
    }
    
    
    /**
     * 校验是否包含在允许的扩展名范围内
     * @param string $extension
     * @throws AppException
     */
    protected function checkExtension($extension)
    {
        if (!$this->options['limit']['ext']) {
            return;
        }
        
        $extension = ltrim(trim($extension), '.');
        if (!$extension) {
            throw new AppException('上传文件类型不允许[extension.0]');
        }
        
        if (!in_array(strtolower($extension), $this->options['limit']['ext'])) {
            throw new AppException('上传文件类型不允许[extension.1]');
        }
    }
    
    
    /**
     * 解析获取错误代码信息
     * @param string $errorNo 错误号码
     * @return string
     */
    public static function parseFileError($errorNo)
    {
        switch ($errorNo) {
            case UPLOAD_ERR_INI_SIZE:
                return '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
            break;
            case UPLOAD_ERR_FORM_SIZE:
                return '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
            break;
            case UPLOAD_ERR_PARTIAL:
                return '文件只有部分被上传';
            break;
            case UPLOAD_ERR_NO_FILE:
                return '没有文件被上传';
            break;
            case UPLOAD_ERR_NO_TMP_DIR:
                return '找不到临时文件夹';
            break;
            case UPLOAD_ERR_CANT_WRITE:
                return '文件写入失败';
            break;
            default:
                return '未知上传错误！';
        }
    }
    
    
    /**
     * 自动转换字符集 支持数组转换
     * @param string $content 转换的对象
     * @param string $from 字符串当前字符集
     * @param string $to 要转成的字符集
     * @return string
     */
    public static function charset($content, $from = 'utf-8', $to = 'gbk')
    {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to   = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        
        //如果编码相同或者非字符串标量则不转换
        if (strtoupper($from) === strtoupper($to) || empty($content) || (is_scalar($content) && !is_string($content))) {
            return $content;
        }
        
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($content, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $content);
        } else {
            return $content;
        }
    }
    
    
    /**
     * 检测附件的有效性
     * @param UploadFileResult|string $filePath
     * @return false|int
     */
    protected static function isFile($filePath)
    {
        if ($filePath instanceof UploadFileResult) {
            $filePath = $filePath->savePath;
        }
        $size = filesize($filePath);
        if (!is_file($filePath) || $size <= 0) {
            return false;
        }
        
        return $size;
    }
    
    
    /**
     * 验证图片合法
     * @access private
     * @param string $filePath 附件路径
     * @param string $extension 附件扩展名
     * @throws AppException
     */
    public static function checkImage($filePath, $extension)
    {
        // 如果是图像文件 检测文件格式
        $extension = strtolower($extension);
        if (!in_array($extension, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf'])) {
            return;
        }
        
        // 非法文件
        if (in_array($extension, ['php', 'php5', 'asp', 'jsp', 'js', 'html', 'htm'])) {
            throw new AppException('非法文件');
        }
        
        $info = getimagesize($filePath);
        if (false === $info || ('gif' == $extension && empty($info['bits']))) {
            throw new AppException('非法图像文件');
        }
    }
    
    
    /**
     * 获取上传配置
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}