<?php
declare(strict_types = 1);

namespace BusyPHP\file;

use BusyPHP\App;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\SystemFileField;
use BusyPHP\app\admin\setting\UploadSetting;
use BusyPHP\app\admin\setting\WatermarkSetting;
use BusyPHP\contract\structs\results\UploadResult;
use BusyPHP\exception\PartUploadSuccessException;
use BusyPHP\helper\StringHelper;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\exception\FileException;
use think\facade\Request;
use think\File;
use think\file\UploadedFile;
use think\filesystem\Driver;

/**
 * 上传类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/20 下午下午8:20 Upload.php $
 */
abstract class Upload
{
    /**
     * 用户ID
     * @var int
     */
    protected $userId = 0;
    
    /**
     * 文件分类
     * @var string
     */
    protected $classType = SystemFile::FILE_TYPE_FILE;
    
    /**
     * 文件业务参数
     * @var string
     */
    protected $classValue = '';
    
    /**
     * 设置
     * @var UploadSetting
     */
    protected $setting;
    
    /**
     * @var App
     */
    protected $app;
    
    /**
     * 自定义生成文件名
     * @var callable|\Closure|string
     */
    protected $nameRule;
    
    /**
     * 上传错误
     * @var string[]
     */
    private static $fileUploadErrors = [
        UPLOAD_ERR_INI_SIZE   => '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值',
        UPLOAD_ERR_FORM_SIZE  => '上传文件的大小超过了表单中 MAX_FILE_SIZE 选项指定的值',
        UPLOAD_ERR_PARTIAL    => '文件只有部分被上传',
        UPLOAD_ERR_NO_FILE    => '没有文件被上传',
        UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
        UPLOAD_ERR_CANT_WRITE => '文件写入失败',
        UPLOAD_ERR_EXTENSION  => 'PHP扩展停止了文件上传',
    ];
    
    /**
     * 图片格式
     * @var string[]
     */
    private static $imageExtensions = [
        'gif',
        'jpg',
        'jpeg',
        'png',
        'tiff',
        'bmp',
        'wmf',
        'webp'
    ];
    
    /**
     * 所属磁盘
     * @var string
     */
    private $disk;
    
    /**
     * 水印设置
     * @var WatermarkSetting
     */
    protected $watermarkSetting;
    
    
    /**
     * Upload constructor.
     * @param Upload|null $target
     */
    public function __construct(?Upload $target = null)
    {
        set_time_limit(0);
        $this->app              = App::getInstance();
        $this->setting          = UploadSetting::init();
        $this->watermarkSetting = WatermarkSetting::init();
        $this->disk             = $this->setting->getDisk();
        
        if ($target) {
            $this->setUserId($target->userId);
            $this->setClassType($target->classType, $target->classValue);
        }
    }
    
    
    /**
     * 设置用户ID
     * @param int $userId 用户ID
     * @return static
     */
    public function setUserId($userId) : self
    {
        $this->userId = intval($userId);
        
        return $this;
    }
    
    
    /**
     * 设置文件分类级文件业务参数
     * @param string $type
     * @param string $value
     * @return $this
     */
    public function setClassType(string $type, string $value = '') : self
    {
        $type             = trim($type);
        $value            = trim($value);
        $this->classType  = $type ?: $this->classType;
        $this->classValue = $value;
        
        return $this;
    }
    
    
    /**
     * 设置命名规则
     * @param callable|\Closure|string $nameRule
     * @return $this
     */
    public function setNameRule($nameRule) : self
    {
        $this->nameRule = $nameRule;
        
        return $this;
    }
    
    
    /**
     * 执行上传
     * @param mixed $data 上传的数据
     * @return UploadResult
     * @throws PartUploadSuccessException
     * @throws Exception
     */
    public function upload($data = null)
    {
        [$name, $path, $imageInfo] = $this->handle($data);
        
        $imageInfo      = $imageInfo ?: [];
        $system         = $this->fileSystem();
        $struct         = new UploadResult();
        $struct->name   = $name;
        $struct->url    = rtrim((trim($system->getConfig()->get('url', '')) ?: '/uploads'), '/') . '/' . $path;
        $struct->file   = new File($system->path($path), true);
        $struct->width  = (int) ($imageInfo[0] ?? 0);
        $struct->height = (int) ($imageInfo[1] ?? 0);
        
        // 图片处理
        try {
            if (in_array($struct->file->getExtension(), self::$imageExtensions)) {
                $thumbType = $this->setting->getThumbType($this->classType);
                $image     = null;
                
                // 需要缩图
                if (in_array($thumbType, [1, 2, 3, 4])) {
                    $image       = new Image($struct->file->getRealPath());
                    $thumbWidth  = $this->setting->getThumbWidth($this->classType);
                    $thumbHeight = $this->setting->getThumbHeight($this->classType);
                    
                    // 按裁剪到指定大小
                    if ($thumbType == 1) {
                        if ($thumbWidth > 0 && $thumbHeight > 0) {
                            $image->width($thumbWidth);
                            $image->height($thumbHeight);
                            $image->thumb(Image::THUMB_CORP);
                        }
                    }
                    
                    //
                    // 忽略比例缩放到指定大小
                    elseif ($thumbType == 2) {
                        if ($thumbWidth > 0 && $thumbHeight > 0) {
                            $image->width($thumbWidth);
                            $image->height($thumbHeight);
                            $image->thumb(Image::THUMB_LOSE);
                        }
                    }
                    
                    //
                    // 等比例缩放
                    elseif ($thumbType == 3 || $thumbType == 4) {
                        // 按照宽缩放
                        if ($struct->width >= $struct->height) {
                            if ($thumbWidth > 0) {
                                $image->thumb(Image::THUMB_ZOOM);
                                $image->width($thumbWidth);
                            }
                        }
                        
                        //
                        // 按照高度缩放
                        elseif ($struct->height > $struct->width) {
                            if ($thumbHeight > 0) {
                                $image->thumb(Image::THUMB_ZOOM);
                                $image->height($thumbHeight);
                            }
                        }
                        
                        // 小图不够放大
                        if ($thumbType == 4) {
                            $image->enlarge(true);
                        }
                    }
                }
                
                // 加水印
                if ($this->setting->isWatermark($this->classType)) {
                    if (!$image) {
                        $image = new Image($struct->file->getRealPath());
                    }
                    
                    $image->watermark($this->watermarkSetting->getFile(), Image::numberToWatermarkPosition($this->watermarkSetting->getPosition()), $this->watermarkSetting->getOpacity(), $this->watermarkSetting->getOffsetX(), $this->watermarkSetting->getOffsetY(), $this->watermarkSetting->getOffsetRotate());
                }
                
                // 图片处理
                if ($image) {
                    $image->save(true, $struct->file->getRealPath());
                    $image->exec(false);
                    $struct->file   = new File($struct->file->getRealPath(), true);
                    $imageInfo      = getimagesize($struct->file->getRealPath());
                    $struct->width  = (int) ($imageInfo[0] ?? 0);
                    $struct->height = (int) ($imageInfo[1] ?? 0);
                }
            }
        } catch (Exception $e) {
            $system->delete($path);
            
            throw $e;
        }
        
        
        // 存到数据库
        $model = SystemFile::init();
        $model->startTrans();
        try {
            $append             = SystemFileField::init();
            $append->name       = $struct->name;
            $append->url        = $struct->url;
            $append->userId     = $this->userId;
            $append->classValue = $this->classValue;
            $append->classType  = $this->classType;
            $append->size       = $struct->file->getSize();
            $append->extension  = $struct->file->getExtension();
            $append->mimeType   = $struct->file->getMime();
            $append->hash       = $struct->file->md5();
            $append->width      = $struct->width;
            $append->height     = $struct->height;
            $append->path       = $path;
            $append->disk       = $this->disk;
            $struct->id         = (int) SystemFile::init()->insertFile($append);
            
            $model->commit();
        } catch (Exception $e) {
            $model->rollback();
            
            $system->delete($path);
            
            throw $e;
        }
        
        return $struct;
    }
    
    
    /**
     * 获取磁盘系统
     * @return Driver
     */
    public function fileSystem() : Driver
    {
        return $this->app->filesystem->disk($this->disk);
    }
    
    
    /**
     * 获取临时文件系统
     * @return Driver
     */
    public function tempSystem() : Driver
    {
        return $this->app->filesystem->disk('local');
    }
    
    
    /**
     * 写入文件
     * @param File $file
     * @return string
     */
    protected function putFile(File $file) : string
    {
        $system = $this->fileSystem();
        $stream = fopen($file->getRealPath(), 'r');
        $path   = $this->createFilename($file);
        $result = $system->putStream($path, $stream);
        if (!$result) {
            throw new FileException('文件写入失败');
        }
        
        return $path;
    }
    
    
    /**
     * 写入内容到文件
     * @param string $content 文件内容
     * @param string $extension 文件扩展名
     * @return string
     */
    protected function putContent(string $content, string $extension)
    {
        $system = $this->fileSystem();
        $path   = $this->createFilename($content, $extension);
        $result = $system->put($path, $content);
        if (!$result) {
            throw new FileException('文件写入失败');
        }
        
        return $path;
    }
    
    
    /**
     * 生成文件名
     * @param string|File $file 文件对象或文件内容
     * @param string      $extension 文件扩展名
     * @return string
     */
    protected function createFilename($file, string $extension = '') : string
    {
        if ($this->nameRule) {
            return call_user_func_array($this->nameRule, [$file, $extension]);
        }
        
        // hash多层
        $type = $this->setting->getDirGenerateType();
        $dir  = '';
        if (0 === strpos($type, 'hash-')) {
            $level = intval(substr($type, 5));
            $level = $level <= 1 ? 1 : $level;
            if ($file instanceof File) {
                $hash = hash_file('sha1', $file->getPathname());
            } else {
                $hash = hash('sha1', $file);
            }
            $dir = [];
            for ($i = 0; $i < $level; $i++) {
                $dir[] = $hash[$i];
            }
            
            $dir = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $dir);
        }
        
        //
        // 日期风格
        elseif ($type) {
            $dir = DIRECTORY_SEPARATOR . date($type);
        }
        
        if ($file instanceof UploadedFile) {
            $extension = $file->getOriginalExtension();
        } elseif ($file instanceof File) {
            $extension = $file->getExtension();
        }
        
        if (!$extension) {
            throw new FileException('必须指定文件扩展名');
        }
        
        return $this->classType . $dir . DIRECTORY_SEPARATOR . md5(implode(',', [
                microtime(true),
                StringHelper::random(32),
                $this->classType,
                $this->classValue,
                $this->userId
            ])) . '.' . $extension;
    }
    
    
    /**
     * 获取上传的文件
     * @param $file
     * @return File
     */
    protected function getFile($file) : File
    {
        if (!$file) {
            throw new FileException('没有要上传的数据');
        }
        
        if (!$file instanceof File) {
            // 通过键取文件
            if (is_string($file)) {
                $file = Request::file($file);
                if (!$file) {
                    throw new FileException('没有文件被上传');
                }
                
                if (is_array($file)) {
                    if (count($file) > 1) {
                        throw new FileException('不支持同时上传多个文件，请分开上传');
                    }
                    
                    $file = $file[0];
                }
            }
            
            //
            // 是$_FILES
            elseif (is_array($file) && isset($file['name'])) {
                if (is_array($file['name'])) {
                    if (count($file['name']) > 1) {
                        throw new FileException('不支持同时上传多个文件，请分开上传');
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
                    throw new FileException(self::$fileUploadErrors[$file['error']] ?? "上传错误{$file['error']}", $file['error']);
                }
                
                $file = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['error']);
            }
            
            //
            // 其它数据
            else {
                throw new FileException('上传数据异常');
            }
        }
        
        return $file;
    }
    
    
    /**
     * 检测文件扩展名是否合规
     * @param string $extension 文件扩展名，不含.
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function checkExtension(string $extension)
    {
        $extension = strtolower($extension);
        if (!$extension) {
            throw new FileException('未知文件扩展名');
        }
        
        $allow = array_map('strtolower', $this->setting->getAllowExtensions($this->classType));
        if (!in_array($extension, $allow)) {
            throw new FileException('上传的文件格式不正确');
        }
        
        // 非法文件
        if (in_array($extension, ['php', 'php5', 'asp', 'jsp'])) {
            throw new FileException('非法文件类型');
        }
    }
    
    
    /**
     * 检测图像是否合规
     * @param string $filename 文件路径
     * @param string $extension 文件扩展名
     * @return array|false
     */
    protected function checkImage(string $filename, string $extension)
    {
        $extension = strtolower($extension);
        if (!in_array($extension, [
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
        ])) {
            return false;
        }
        
        $info = getimagesize($filename);
        if (false === $info || ('gif' == $extension && empty($info['bits']))) {
            throw new FileException('非法图像文件');
        }
        
        return $info;
    }
    
    
    /**
     * 检测文件MimeType是否合规
     * @param string $mimeType
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function checkMimeType(string $mimeType)
    {
        $mimeType = strtolower($mimeType);
        $allow    = array_map('strtolower', $this->setting->getMimeType($this->classType));
        if (!$allow) {
            return;
        }
        
        foreach ($allow as $item) {
            if (false !== strpos($item, '*')) {
                $pattern = str_replace('/', '\/', $item);
                $pattern = str_replace('*', '.*', $pattern);
                if (preg_match('/^' . $pattern . '$/is', $mimeType, $match)) {
                    return;
                }
            } else {
                if ($mimeType == $item) {
                    return;
                }
            }
        }
        
        throw new FileException('上传的文件Mime类型不正确');
    }
    
    
    /**
     * 检测文件大小是否合规
     * @param int $fileSize
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function checkFileSize(int $fileSize)
    {
        if ($fileSize <= 0) {
            throw new FileException('禁止上传空文件');
        }
        
        $maxSize = $this->setting->getMaxSize($this->classType);
        if ($maxSize > 0 && $fileSize > $maxSize) {
            throw new FileException('上传的文件大小超过了系统限制');
        }
    }
    
    
    /**
     * 上传处理
     * @param mixed $data 上传的数据
     * @return array [文件名称,文件对象,图像信息]
     * @throws Exception
     */
    abstract protected function handle($data) : array;
}