<?php
declare(strict_types = 1);

namespace BusyPHP\upload\part;

use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\FileHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\upload\interfaces\PartInterface;
use BusyPHP\upload\parameter\PartAbortParameter;
use BusyPHP\upload\parameter\PartCompleteParameter;
use BusyPHP\upload\parameter\PartInitParameter;
use BusyPHP\upload\parameter\PartPutParameter;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use League\Flysystem\FilesystemException;
use League\Flysystem\StorageAttributes;
use LengthException;
use RangeException;
use ReflectionException;
use RuntimeException;
use think\exception\FileException;
use think\facade\Filesystem;
use think\File;
use think\filesystem\Driver;
use Throwable;

/**
 * 本地分块上传驱动
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/21 8:24 PM Local.php $
 */
class Local implements PartInterface
{
    /** @var Driver */
    protected $driver;
    
    
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }
    
    
    /**
     * 初始化分块上传
     * @param PartInitParameter $parameter
     * @return string
     */
    public function init(PartInitParameter $parameter) : string
    {
        // 校验文件系统
        $this->tmpDisk($parameter->getTmpDisk());
        
        // 生成上传ID
        $uploadId = md5($parameter->getPath());
        
        return TransHelper::base64encodeUrl(json_encode([
            'uploadId' => $uploadId,
            'path'     => $parameter->getPath(),
            'basename' => $parameter->getBasename(),
            'mimetype' => $parameter->getMimetype(),
            'filesize' => $parameter->getFilesize(),
            'tmpDisk'  => $parameter->getTmpDisk(),
            'tmpDir'   => $parameter->getTmpDir(),
            'md5'      => $parameter->getMd5()
        ], JSON_UNESCAPED_UNICODE));
    }
    
    
    /**
     * 上传分块
     * @param PartPutParameter $parameter
     * @return array
     * @throws ReflectionException
     */
    public function put(PartPutParameter $parameter) : array
    {
        $config  = $this->getConfig($parameter->getUploadId());
        $tmpDisk = $this->tmpDisk($config['tmpDisk']);
        $tmpPath = $this->tmpFile($config['tmpDir'], $config['uploadId'], sprintf('%s.%s', $parameter->getPartNumber(), 'part'));
        $file    = $parameter->getFile();
        
        if ($file instanceof File) {
            $info = pathinfo($tmpDisk->path($tmpPath));
            $md5  = $file->md5();
            $size = $file->getSize();
            $file->move($info['dirname'], $info['basename']);
        } else {
            $tmpDisk->put($tmpPath, $file = (string) $file);
            $md5  = md5($file);
            $size = strlen($file);
        }
        
        return [
            'etag'        => $md5,
            'filesize'    => $size,
            'part_number' => $parameter->getPartNumber()
        ];
    }
    
    
    /**
     * 完成整个分块上传
     * mimetype，md5，filesize，width，height 请通过文件内容来取
     * @param PartCompleteParameter $parameter
     * @return array
     * @return array{path: string, md5: string, basename: string, mimetype: string, filesize: int, width: int, height: int}
     * @throws Throwable
     */
    public function complete(PartCompleteParameter $parameter) : array
    {
        $config  = $this->getConfig($parameter->getUploadId());
        $tmpDisk = $this->tmpDisk($config['tmpDisk']);
        
        try {
            $parts = $parameter->getParts();
            if (0 == $partTotal = count($parts)) {
                throw new LengthException('碎片数为空');
            }
            
            // 获取临时目录下的碎片
            $partList = [];
            
            /** @var StorageAttributes $item */
            foreach ($tmpDisk->listContents($this->tmpFile($config['tmpDir'], $config['uploadId'])) as $item) {
                $number   = intval(pathinfo($item->path(), PATHINFO_FILENAME));
                $basename = pathinfo($item->path(), PATHINFO_BASENAME);
                if (!$item->isFile() || $basename === 'config.json' || $number < 1) {
                    continue;
                }
                
                $partList[] = [
                    'number' => $number,
                    'path'   => $item->path()
                ];
            }
            
            // 校验数字连续性检测
            $partList = ArrayHelper::listSortBy($partList, 'number');
            if (count($partList) != $partTotal) {
                throw new LengthException('碎片数不匹配');
            }
            
            foreach ($partList as $i => $item) {
                $prev = $partList[$i - 1] ?? null;
                if ($prev && $prev['number'] + 1 != $item['number']) {
                    throw new RangeException('碎片不连续');
                }
            }
            
            // 向临时目录创建一个空文件
            $completePath = $this->tmpFile($config['tmpDir'], $config['uploadId'], 'complete.file');
            $completeFile = new File($tmpDisk->path($completePath), false);
            $tmpDisk->put($completePath, '');
            
            // 打开文件将碎片写入
            $parts    = ArrayHelper::listByKey($parts, 'part_number');
            $resource = Utils::tryFopen($completeFile->getPathname(), 'w+b');
            foreach ($partList as $item) {
                $partFile = new File($tmpDisk->path($item['path']), false);
                if (!$partFile->isFile()) {
                    throw new FileException(sprintf('碎片不存在: %s', $item['number']));
                }
                
                // 对比
                if (($parts[$item['number']]['etag'] ?? '') != $partFile->md5()) {
                    throw new FileException('碎片不匹配: %s', $item['number']);
                }
                
                try {
                    $body = $tmpDisk->read($item['path']);
                } catch (Throwable $e) {
                    throw new FileException(sprintf('读取碎片失败: %s', $item['number']));
                }
                
                if (!fwrite($resource, $body)) {
                    throw new FileException(sprintf('写入碎片失败: %s', $config['uploadId']));
                }
            }
            
            // 检验图片
            [$width, $height] = FileHelper::checkImage($completeFile->getPathname(), $config['extension']);
            $md5      = $completeFile->md5();
            $filesize = $completeFile->getSize();
            $mimetype = FileHelper::getMimetypeByFile($completeFile->getPathname());
            $mimetype = $mimetype ?: FileHelper::getMimetypeByPath($completeFile->getPathname());
            
            // 初始化有md5值才校验
            if ($config['md5'] && $config['md5'] != $md5) {
                throw new FileException('文件校验失败: %s', $config['uploadId']);
            }
            
            // 移动文件至上传目录
            $info = pathinfo($this->driver->path($config['path']));
            $completeFile->move($info['dirname'], $info['basename']);
        } catch (Throwable $e) {
            $this->clear($config);
            
            throw $e;
        } finally {
            if (!empty($resource)) {
                fclose($resource);
            }
        }
        
        // 合并完成，删除碎片
        $this->clear($config);
        
        return [
            'path'     => $config['path'],
            'md5'      => $md5,
            'basename' => $config['basename'],
            'mimetype' => $mimetype,
            'filesize' => $filesize,
            'width'    => $width,
            'height'   => $height
        ];
    }
    
    
    /**
     * 终止分块上传
     * @param PartAbortParameter $parameter
     */
    public function abort(PartAbortParameter $parameter)
    {
        $this->clear($this->getConfig($parameter->getUploadId()));
    }
    
    
    /**
     * 清理碎片
     * @param array $config
     * @throws FilesystemException
     */
    protected function clear(array $config)
    {
        $this->tmpDisk($config['tmpDisk'])->deleteDirectory($this->tmpFile($config['tmpDir'], $config['uploadId']));
    }
    
    
    /**
     * 获取配置
     * @param string $uploadId
     * @return array{uploadId: string, path: string, basename: string, filesize: int, mimetype: string, extension:string, tmpDisk: string, tmpDir: string, md5: string}
     */
    protected function getConfig(string $uploadId) : array
    {
        $config = json_decode(TransHelper::base64decodeUrl($uploadId), true) ?: [];
        if (!isset($config['uploadId'])) {
            throw new InvalidArgumentException('缺少参数:uploadId');
        }
        if (!isset($config['basename'])) {
            throw new InvalidArgumentException('缺少参数:basename');
        }
        if (!isset($config['mimetype'])) {
            throw new InvalidArgumentException('缺少参数:mimetype');
        }
        if (!isset($config['filesize'])) {
            throw new InvalidArgumentException('缺少参数:filesize');
        }
        if (!isset($config['path'])) {
            throw new InvalidArgumentException('缺少参数:path');
        }
        if (!isset($config['tmpDisk'])) {
            throw new InvalidArgumentException('缺少参数:tmpDisk');
        }
        if (!isset($config['tmpDir'])) {
            throw new InvalidArgumentException('缺少参数:tmpDir');
        }
        if (!isset($config['md5'])) {
            throw new InvalidArgumentException('缺少参数:md5');
        }
        
        $config['path']     = trim($config['path']);
        $config['basename'] = trim($config['basename']);
        $config['mimetype'] = trim($config['mimetype']);
        $config['filesize'] = intval($config['filesize']);
        $config['tmpDir']   = trim($config['tmpDir']);
        $config['tmpDisk']  = trim($config['tmpDisk']);
        if (!$config['uploadId']) {
            throw new InvalidArgumentException('uploadId为空');
        }
        if (!$config['path']) {
            throw new InvalidArgumentException('path为空');
        }
        
        $config['extension'] = pathinfo($config['path'], PATHINFO_EXTENSION);
        
        return $config;
    }
    
    
    /**
     * 分块存储系统
     * @param string $disk
     * @return Driver
     */
    protected function tmpDisk(string $disk) : Driver
    {
        $disk = Filesystem::disk($disk ?: 'local');
        if (!$disk->isLocal()) {
            throw new RuntimeException('分块暂存文件系统非本地文件系统');
        }
        
        return $disk;
    }
    
    
    /**
     * 生成临时路径
     * @param string $dir
     * @param string $uploadId
     * @param string $filename
     * @return string
     */
    protected function tmpFile(string $dir, string $uploadId, string $filename = '') : string
    {
        $dir = trim(trim($dir), '/') ?: 'parts';
        
        return sprintf('%s/%s/%s', $dir, $uploadId, $filename);
    }
}