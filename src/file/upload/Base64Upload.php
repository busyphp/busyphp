<?php
declare(strict_types = 1);

namespace BusyPHP\file\upload;

use BusyPHP\file\Upload;
use Closure;
use Exception;
use League\Flysystem\Util\MimeType;
use think\exception\FileException;
use think\helper\Str;

/**
 * Base64上传类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/20 下午下午4:07 Base64Upload.php $
 */
class Base64Upload extends Upload
{
    /**
     * 文件MimeType
     * @var string
     */
    protected $mimeType = 'text/plain';
    
    /**
     * 文件扩展名
     * @var string
     */
    protected $extension = 'txt';
    
    /**
     * 文件名
     * @var string|callable
     */
    protected $name = '';
    
    
    /**
     * 设置文件MimeType，当无法获取时使用该值
     * @param string $mimeType
     * @return $this
     */
    public function setDefaultMimeType(string $mimeType) : self
    {
        $mimeType       = trim($mimeType);
        $this->mimeType = $mimeType ?: $this->mimeType;
        
        return $this;
    }
    
    
    /**
     * 设置文件扩展名，不含"."，当无法获取时使用改值
     * @param string $extension
     * @return $this
     */
    public function setDefaultExtension(string $extension) : self
    {
        $extension       = trim($extension);
        $this->extension = $extension ?: $this->extension;
        
        return $this;
    }
    
    
    /**
     * 设置文件名，可包涵扩展名
     * @param string|callable $name
     * @return $this
     */
    public function setName($name) : self
    {
        $this->name = $name;
        
        return $this;
    }
    
    
    /**
     * 上传处理
     * @param mixed $base64 上传的数据
     * @return array [文件名称,文件对象,图像信息]
     * @throws Exception
     */
    protected function handle($base64) : array
    {
        if (!$base64) {
            throw new FileException('没有要上传的数据');
        }
        
        // 获取附件扩展名
        $mimeType = '';
        if (preg_match('/^(data:\s*(.*);\s*base64,)/i', $base64, $match)) {
            $mimeType = strtolower($match[2]);
            $base64   = str_replace($match[1], '', $base64);
        }
        
        // 获取文件类型
        $extension = '';
        $mimeType  = strtolower($mimeType);
        foreach (MimeType::getExtensionToMimeTypeMap() as $key => $value) {
            if ($mimeType === strtolower($value)) {
                $extension = $key;
                break;
            }
        }
        
        $mimeType  = $mimeType ?: $this->mimeType;
        $extension = strtolower($extension ?: $this->extension);
        
        // 文件名
        if (is_callable($this->name) || $this->name instanceof Closure) {
            $name = call_user_func_array($this->name, []);
        } else {
            $name = $this->name ?: 'BASE64_' . date('YmdHis') . '_' . Str::random(6) . '.' . $extension;
        }
        
        if (!$extension) {
            throw new FileException('无法获取文件扩展名');
        }
        
        // 解密数据
        if (!$file = base64_decode(str_replace(' ', '+', $base64))) {
            throw new FileException('上传数据异常');
        }
        
        $this->checkExtension($extension);
        $this->checkFileSize(strlen($file));
        $this->checkMimeType($mimeType);
        
        // 写入文件
        $path   = $this->putContent($file, $extension);
        $system = $this->fileSystem();
        
        // 图片检测
        try {
            $imageInfo = $this->checkImage($system->path($path), $extension);
        } catch (Exception $e) {
            $system->delete($path);
            throw $e;
        }
        
        return [$name, $path, $imageInfo];
    }
}