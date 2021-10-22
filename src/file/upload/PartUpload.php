<?php
declare(strict_types = 1);

namespace BusyPHP\file\upload;

use BusyPHP\exception\PartUploadSuccessException;
use BusyPHP\file\Upload;
use Exception;
use think\exception\FileException;
use think\File;

/**
 * 分块上传
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/21 下午上午12:53 PartUpload.php $
 */
class PartUpload extends Upload
{
    /**
     * 文件唯一ID
     * @var string
     */
    protected $id;
    
    /**
     * 分块总数
     * @var int
     */
    protected $total;
    
    /**
     * 当前上传的分块数
     * @var int
     */
    protected $current = 0;
    
    /**
     * 所有分块是否上传完成
     * @var bool
     */
    protected $complete = false;
    
    /**
     * 文件名
     * @var string
     */
    protected $name = '';
    
    /**
     * @var resource
     */
    private $lockResource;
    
    
    /**
     * 设置上传文件的唯一ID
     * @param string $id
     * @return $this
     */
    public function setId(string $id) : self
    {
        $this->id = $id;
        
        return $this;
    }
    
    
    /**
     * 设置总共有多少个分块
     * @param int $total
     * @return $this
     */
    public function setTotal(int $total) : self
    {
        $this->total = $total;
        
        return $this;
    }
    
    
    /**
     * 设置当前上传的第几个分块
     * @param int $current
     * @return $this
     */
    public function setCurrent(int $current) : self
    {
        $this->current = $current;
        
        return $this;
    }
    
    
    /**
     * 设置所有分块是否上传完毕
     * @param bool $complete
     * @return $this
     */
    public function setComplete(bool $complete) : self
    {
        $this->complete = $complete;
        
        return $this;
    }
    
    
    /**
     * 设置上传的文件名，可包涵扩展名
     * @param string $name
     * @return $this
     */
    public function setName(string $name) : self
    {
        $this->name = trim($name);
        
        return $this;
    }
    
    
    /**
     * 上传处理
     * @param mixed $file 上传的数据
     * @return array [文件名称,文件对象,图像信息]
     * @throws Exception
     */
    protected function handle($file) : array
    {
        // 只有一个分块，则直接走上传
        if (!$this->complete && $this->total <= 1) {
            $local = new LocalUpload($this);
            
            return $local->dealFile($file);
        }
        
        if (!$this->id) {
            throw new FileException('缺少分块文件ID');
        }
        
        $fileSystem = $this->fileSystem();
        $tempSystem = $this->tempSystem();
        $tempDir    = 'parts' . DIRECTORY_SEPARATOR . md5(implode(',', [
                $this->id,
                $this->userId,
                $this->classType,
                $this->classValue
            ])) . DIRECTORY_SEPARATOR;
        $configPath = $tempDir . 'config.json';
        $lockPath   = $tempSystem->path($tempDir) . 'lock.lock';
        
        // 创建分块零时目录
        $tempSystem->createDir($tempDir);
        
        // 合成分块
        if ($this->complete) {
            $this->lock($lockPath);
            
            try {
                if (!$this->name) {
                    throw new FileException('缺少文件名');
                }
                
                $extension = pathinfo($this->name, PATHINFO_EXTENSION);
                $path      = $this->createFilename($this->name, $extension);
                $config    = $tempSystem->read($configPath);
                $config    = json_decode(trim($config) ?: '{}', true);
                if (!$config) {
                    throw new FileException("分块记录为空: {$configPath}");
                }
                
                $list  = $config['list'] ?? [];
                $total = $config['total'] ?? 0;
                if (count($list) != $total || !$list) {
                    throw new FileException("分块记录不匹配: {$configPath}");
                }
                
                ksort($list);
                
                // 创建一个空文件
                if (!$fileSystem->put($path, '')) {
                    throw new FileException("创建文件失败: {$path}");
                }
                
                $filePath     = $fileSystem->path($path);
                $fileResource = fopen($filePath, 'w+b');
                if (!$fileResource) {
                    throw new FileException("读取文件失败: {$path}");
                }
                
                // 遍历分块写入到文件中
                foreach ($list as $item) {
                    $content = $tempSystem->read($item);
                    if (false === $content) {
                        throw new FileException("读取分块文件失败: {$item}");
                    }
                    
                    if (!fwrite($fileResource, $content)) {
                        throw new FileException("分块写入到文件失败: {$path}");
                    }
                }
                fclose($fileResource);
                
                // 释放锁
                $this->unlock();
                
                // 清理临时目录
                $tempSystem->deleteDir($tempDir);
                
                $file = new File($filePath);
                $this->checkExtension($file->getExtension());
                $this->checkFileSize($file->getSize());
                $this->checkMimeType($file->getMime());
                $imageInfo = $this->checkImage($filePath, $file->getExtension());
                
                return [$this->name, $path, $imageInfo];
            } catch (Exception $e) {
                // 释放锁
                $this->unlock();
                
                // 清理临时目录
                $tempSystem->deleteDir($tempDir);
                
                // 清理上传的文件
                if (!empty($path)) {
                    $fileSystem->delete($path);
                }
                
                
                throw $e;
            }
        }
        
        
        // 写入分块临时文件
        $partPath = "{$tempDir}{$this->current}.part";
        $this->lock($lockPath);
        
        try {
            // 将分块写入到临时目录
            $content = fopen($this->getFile($file)->getRealPath(), 'r');
            if (!$tempSystem->putStream($partPath, $content)) {
                throw new FileException("分块文件写入失败: {$partPath}");
            }
            if (is_resource($content)) {
                fclose($content);
            }
            
            // 获取分块记录数据
            if (file_exists($tempSystem->path($configPath))) {
                $config = $tempSystem->read($configPath);
                $config = json_decode($config, true);
                $config = is_array($config) ? $config : [];
            } else {
                $config = [];
            }
            
            if (!$config) {
                $config = [
                    'create_time' => time(),
                    'id'          => $this->id,
                    'total'       => $this->total,
                    'list'        => []
                ];
            }
            $config['list'][$this->current] = $partPath;
            if (!$tempSystem->put($configPath, json_encode($config))) {
                throw new FileException("分块记录写入失败: {$configPath}");
            }
            
            $this->unlock();
        } catch (Exception $e) {
            $this->unlock();
            $tempSystem->deleteDir($tempDir);
            
            throw $e;
        }
        
        throw new PartUploadSuccessException();
    }
    
    
    /**
     * 上锁
     * @param $file
     */
    protected function lock($file)
    {
        $this->lockResource = fopen($file, 'w+b');
        if (!$this->lockResource) {
            throw new FileException("无法打开锁文件");
        }
        
        // 持续获得锁
        while (true) {
            if (flock($this->lockResource, LOCK_EX)) {
                break;
            }
            usleep(1000);
        }
    }
    
    
    /**
     * 释放锁
     */
    protected function unlock()
    {
        if (!is_resource($this->lockResource)) {
            return;
        }
        
        flock($this->lockResource, LOCK_UN);
        fclose($this->lockResource);
        $this->lockResource = null;
    }
}