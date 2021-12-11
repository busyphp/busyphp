<?php

namespace BusyPHP\file\upload;

use BusyPHP\file\Upload;
use Closure;
use Exception;
use think\exception\FileException;

/**
 * 通过数据上传文件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/11 下午2:34 DataUpload.php $
 */
class DataUpload extends Upload
{
    /**
     * 文件MimeType
     * @var string
     */
    protected $mimeType = '';
    
    /**
     * 文件扩展名
     * @var string
     */
    protected $extension = '';
    
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
    public function setMimeType(string $mimeType) : self
    {
        $this->mimeType = $mimeType;
        
        return $this;
    }
    
    
    /**
     * 设置文件扩展名，不含"."，当无法获取时使用改值
     * @param string $extension
     * @return $this
     */
    public function setExtension(string $extension) : self
    {
        $this->extension = $extension;
        
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
     * @param string $data 上传的数据
     * @return array [文件名称,文件对象,图像信息]
     * @throws Exception
     */
    protected function handle($data) : array
    {
        if (!$data) {
            throw new FileException('没有要上传的数据');
        }
        
        // 文件名
        if (is_callable($this->name) || $this->name instanceof Closure) {
            $name = call_user_func_array($this->name, []);
        } else {
            $name = $this->name ?: 'DATA' . date('YmdHis') . '.' . $this->extension;
        }
        
        $this->checkExtension($this->extension);
        $this->checkFileSize(strlen($data));
        $this->checkMimeType($this->mimeType);
        
        // 写入文件
        $path   = $this->putContent($data, $this->extension);
        $system = $this->fileSystem();
        
        // 图片检测
        try {
            $imageInfo = $this->checkImage($system->path($path), $this->extension);
        } catch (Exception $e) {
            $system->delete($path);
            throw $e;
        }
        
        return [$name, $path, $imageInfo];
    }
}