<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\facade\Image;
use BusyPHP\facade\Uploader;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\helper\FileHelper;
use BusyPHP\helper\FilesystemHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\image\parameter\FormatParameter;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\uploader\front\Local;
use League\Flysystem\FilesystemException;
use RuntimeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\Raw;
use think\exception\FileException;
use think\facade\Config;
use think\facade\Filesystem;
use think\facade\Request;
use think\File;
use think\file\UploadedFile;
use think\Log;
use Throwable;

/**
 * 文件管理模型
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2017 busy^life <busy.life@qq.com>
 * @version $Id: 2017-05-30 下午7:38 SystemFile.php busy^life $
 * @method SystemFileField getInfo(int $id, string $notFoundMessage = null)
 * @method SystemFileField|null findInfo(int $id = null)
 * @method SystemFileField[] selectList()
 * @method SystemFileField[] indexList(string|Entity $key = '')
 * @method SystemFileField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 * @method SystemFileField|null findInfoByHash(string $hash)
 * @method SystemFileField|null findInfoByUniqueId(string $uniqueId)
 * @method SystemFileField|null findInfoByPath(string $path)
 * @method SystemFileField|null findInfoByUrlHash(string $urlHash)
 * @method SystemFileField|null findInfoByUrl(string $url)
 */
class SystemFile extends Model implements ContainerInterface
{
    //+--------------------------------------
    //| 文件类型
    //+--------------------------------------
    /**
     * 图片
     * @var string
     * @icon fa fa-file-image-o
     * @types jpg,jpeg,png,gif,bmp,webp,ico
     */
    const FILE_TYPE_IMAGE = 'image';
    
    /**
     * 视频
     * @var string
     * @icon fa fa-file-video-o
     * @types avi,wmv,mpeg,mp4,m4v,mov,asf,flv,f4v,rmvb,rm,3gp,vob,webm,ogv
     */
    const FILE_TYPE_VIDEO = 'video';
    
    /**
     * 音频
     * @var string
     * @icon fa fa-file-audio-o
     * @types mp3,wav,wma,aac,wave,ogg
     */
    const FILE_TYPE_AUDIO = 'audio';
    
    /**
     * 文档
     * @var string
     * @icon fa fa-file-o
     * @types pdf,doc,docx,xls,xlsx,ppt,pptx,txt,md,rft
     */
    const FILE_TYPE_DOC = 'doc';
    
    /**
     * 压缩包
     * @var string
     * @icon fa fa-file-zip-o
     * @types zip,rar,7z,gz,tar
     */
    const FILE_TYPE_ZIP = 'zip';
    
    /**
     * 应用程序
     * @var string
     * @icon fa fa-codepen
     * @types apk,ipa,dmg,exe,app
     */
    const FILE_TYPE_APP = 'app';
    
    /**
     * 其它文件
     * @var string
     * @icon fa fa-paperclip
     */
    const FILE_TYPE_FILE = 'file';
    
    //+--------------------------------------
    //| 其它
    //+--------------------------------------
    /** 临时附件前缀 */
    const MARK_VALUE_TMP_PREFIX = 'temp_';
    
    protected string $dataNotFoundMessage = '文件不存在';
    
    protected string $fieldClass          = SystemFileField::class;
    
    protected array  $config;
    
    
    public function __construct(string $connect = '', bool $force = false)
    {
        $this->config = (array) (Config::get('admin.model.system_file') ?: []);
        
        parent::__construct($connect, $force);
    }
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 获取附件分类
     * @param string|null $type
     * @param string|null $key
     * @return array|mixed
     */
    public static function getTypes(string $type = null, string $key = null) : mixed
    {
        static $_data;
        
        if (!isset($_data)) {
            $data = ClassHelper::getConstAttrs(self::class, 'FILE_TYPE_', ['icon', 'types']);
            foreach ($data as &$vo) {
                $vo['types'] = array_filter(explode(',', $vo['types']));
                unset($vo['var'], $vo['key']);
            }
            
            unset($vo);
            foreach ((array) Config::get('admin.model.system_file.file_type_map', []) as $index => $vo) {
                $vo['name']  = $vo['name'] ?? '';
                $vo['types'] = $vo['types'] ?? [];
                $vo['icon']  = $vo['icon'] ?? '';
                $vo['value'] = $index;
                
                if (isset($data[$index])) {
                    $vo['types'] = array_unique(array_merge($data[$index]['types'], $vo['types']));
                    $vo['name']  = $vo['name'] ?: $data[$index]['name'];
                    $vo['icon']  = $vo['icon'] ?: $data[$index]['icon'];
                } else {
                    $vo['name'] = $vo['name'] ?: $index;
                    $vo['icon'] = $vo['icon'] ?: 'fa fa-file-o';
                }
                
                $data[$index] = $vo;
            }
            
            // 将其它插入到最后
            $file = $data[self::FILE_TYPE_FILE];
            unset($data[self::FILE_TYPE_FILE]);
            $data[self::FILE_TYPE_FILE] = $file;
            
            $_data = $data;
        }
        
        if (!is_null($type) && !is_null($key)) {
            return $_data[$type][$key] ?? null;
        }
        
        return ArrayHelper::getValueOrSelf($_data, $type);
    }
    
    
    /**
     * 创建一个临时的业务参数
     * @param mixed $type 分类
     * @return string
     */
    public static function createTmp(mixed $type = '') : string
    {
        return static::MARK_VALUE_TMP_PREFIX . md5($type . ',' . md5(uniqid()) . ',' . StringHelper::random(32));
    }
    
    
    /**
     * 生成文件名
     * @param string      $classType 文件分类
     * @param string      $classValue 文件分类值
     * @param int         $userId 用户ID
     * @param string|File $file 文件内容
     * @param string      $extension 文件扩展名
     * @return string
     */
    public static function createFilename(string $classType, string $classValue, int $userId, string|File $file, string $extension = '') : string
    {
        $classType = $classType ?: 'file';
        $type      = StorageSetting::instance()->getDirGenerateType();
        $dir       = '';
        
        // hash多层
        if (str_starts_with($type, 'hash-')) {
            $level = max(intval(substr($type, 5)), 1);
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
        if ($extension === '' && is_string($file)) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
        }
        if ($extension === '') {
            throw new FileException('必须指定文件扩展名');
        }
        
        return $classType . $dir . DIRECTORY_SEPARATOR . md5(
                implode(',', [
                    md5(uniqid($classType)),
                    $classType,
                    $classValue,
                    $userId
                ])
            ) . '.' . $extension;
    }
    
    
    /**
     * 生成文件唯一码
     * @param string $hash
     * @param string $imageStyle
     * @return string
     */
    public static function createUniqueId(string $hash, string $imageStyle) : string
    {
        return md5($hash . '@' . $imageStyle);
    }
    
    
    /**
     * 添加文件
     * @param SystemFileField $data
     * @return SystemFileField
     * @throws DbException
     */
    public function create(SystemFileField $data) : SystemFileField
    {
        return $this->getInfo($this->validate($data, static::SCENE_CREATE)->insert());
    }
    
    
    /**
     * 更新分类业务参数
     * @param string $oldValue 旧业务参数
     * @param string $newValue 新业务参数
     * @return int
     * @throws DbException
     */
    public function updateValue(string $oldValue, string $newValue) : int
    {
        $oldValue = trim($oldValue);
        $newValue = trim($newValue);
        if ($oldValue === '') {
            throw new ParamInvalidException('$oldValue');
        }
        if ($oldValue === $newValue) {
            return 0;
        }
        
        return $this->where(SystemFileField::classValue($oldValue))->setField(SystemFileField::classValue(), $newValue);
    }
    
    
    /**
     * 通过文件分类和业务值更新业务值
     * @param string $classType 文件分类
     * @param mixed  $classValue 文件业务值
     * @param mixed  $newValue 新文件分类值
     * @return int
     * @throws DbException
     */
    public function updateValueByClass(string $classType, string $classValue, string $newValue) : int
    {
        return $this
            ->whereClass($classType, trim($classValue))
            ->setField(SystemFileField::classValue(), trim($newValue));
    }
    
    
    /**
     * 通过文件ID更新业务值
     * @param int    $id 文件ID
     * @param string $newValue 新文件分类值
     * @return int
     * @throws DbException
     */
    public function updateValueById(int $id, string $newValue) : int
    {
        return $this
            ->where(SystemFileField::id($id))
            ->setField(SystemFileField::classValue(), trim($newValue));
    }
    
    
    /**
     * 查询分类条件
     * @param string      $classType 文件分类
     * @param string|null $classValue 文件业务参数
     * @return $this
     */
    public function whereClass(string $classType, string $classValue = null) : self
    {
        $this->where(SystemFileField::classType(trim($classType)));
        
        if (!is_null($classValue)) {
            $classValue = trim($classValue);
            $this->where(SystemFileField::classValue($classValue));
        }
        
        return $this;
    }
    
    
    /**
     * 已上传完成的文件
     * @param bool $complete
     * @return $this
     */
    public function whereComplete(bool $complete = true) : self
    {
        $this->where(SystemFileField::pending($complete ? 0 : 1));
        
        return $this;
    }
    
    
    /**
     * 删除附件
     * @param int $id
     * @return int
     * @throws Throwable
     */
    public function remove(int $id) : int
    {
        return $this->transaction(function() use ($id) {
            $info = $this->lock(true)->getInfo($id);
            
            // 没有相同的文件才真实的删除
            if (!$this->where(SystemFileField::uniqueId($info->uniqueId))
                ->where(SystemFileField::id('<>', $info->id))
                ->find()) {
                try {
                    $info->filesystem()->delete($info->path);
                } catch (Throwable $e) {
                    $this->log($e, Log::ERROR);
                }
            }
            
            // 删除数据
            return $this->delete($info->id);
        });
    }
    
    
    /**
     * 清理重复文件
     * @return int
     * @throws DbException
     */
    public function clearRepeat() : int
    {
        $min = $this->field(SystemFileField::id()->exp('min')->as('min_id'))
            ->group(SystemFileField::uniqueId())
            ->buildSql();
        $sql = $this->field('t.min_id')->table($min)->alias('t')->buildSql(false);
        
        return $this->where(SystemFileField::id('NOT IN', new Raw($sql)))->delete();
    }
    
    
    /**
     * 清理无效文件
     * @return int
     * @throws DbException
     */
    public function clearInvalid() : int
    {
        return $this->where(SystemFileField::pending(1))->delete();
    }
    
    
    /**
     * 通过文件url删除
     * @param string $url 附件地址
     * @return int
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    public function deleteByUrl(string $url) : int
    {
        return $this->remove($this->failException(true)->findInfoByUrlHash(md5(trim($url)))->id);
    }
    
    
    /**
     * 通过文件分类删除
     * @param string      $classType 标识类型
     * @param string|null $classValue 标识值
     * @return int
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    public function deleteByClass(string $classType, string $classValue = null) : int
    {
        return $this->remove($this->whereClass($classType, $classValue)->failException(true)->findInfo()->id);
    }
    
    
    /**
     * 上传文件
     * @param SystemFileUploadData $uploadData
     * @return SystemFileField
     * @throws Throwable
     */
    public function upload(SystemFileUploadData $uploadData) : SystemFileField
    {
        $storage    = StorageSetting::instance();
        $diskName   = $uploadData->getDisk() ?: $storage->getDisk();
        $filesystem = Filesystem::disk($diskName);
        $result     = Uploader::driver($uploadData->getDriver())
            ->disk($filesystem)
            ->path(function($file, $extension) use ($uploadData) {
                return static::createFilename(
                    $uploadData->getClassType(),
                    $uploadData->getClassValue(),
                    $uploadData->getUserId(),
                    $file,
                    $extension
                );
            })
            ->limitMaxsize($storage->getMaxSize($uploadData->getClassType()))
            ->limitMimetypes($storage->getMimeType($uploadData->getClassType()))
            ->limitExtensions($storage->getAllowExtensions($uploadData->getClassType()))
            ->upload($uploadData->getData());
        
        try {
            // 处理图片
            $extension  = strtolower(pathinfo($result->getPath(), PATHINFO_EXTENSION));
            $imageStyle = $storage->getImageStyle($uploadData->getClassType(), $diskName);
            $styleRule  = '';
            if (array_key_exists($extension, FormatParameter::getFormatMap()) && $imageStyle !== '') {
                $imageResult = Image::path($result->getPath())->style($imageStyle)->disk($filesystem)->save();
                $result->setFilesize($imageResult->getSize());
                $result->setWidth($imageResult->getWidth());
                $result->setHeight($imageResult->getHeight());
                $styleRule = $filesystem->image()->getStyleByCache($imageStyle)->rule;
            }
            
            // 秒传
            $uniqueId = static::createUniqueId($result->getMd5(), $styleRule);
            if ($info = $this->where(SystemFileField::disk($diskName))
                ->whereComplete()
                ->findInfoByUniqueId($uniqueId)) {
                $data = clone $info;
                $data->setId(null);
                $data->setUserId($uploadData->getUserId());
                $data->setClassType($uploadData->getClassType());
                $data->setClassValue($uploadData->getClassValue());
                $data->setName($result->getBasename());
                $data->setFast(true);
                $data->setPending(false);
            } else {
                $data = SystemFileField::init();
                $data->setName($result->getBasename());
                $data->setUrl($filesystem->url($result->getPath()));
                $data->setSize($result->getFilesize());
                $data->setMimeType($result->getMimetype());
                $data->setHash($result->getMd5());
                $data->setUniqueId($uniqueId);
                $data->setWidth($result->getWidth());
                $data->setHeight($result->getHeight());
                $data->setPath($result->getPath());
                $data->setUserId($uploadData->getUserId());
                $data->setClassValue($uploadData->getClassValue());
                $data->setClassType($uploadData->getClassType());
                $data->setExtension($extension);
                $data->setDisk($diskName);
            }
            
            return $this->create($data);
        } catch (Throwable $e) {
            // 删除文件
            $filesystem->delete($result->getPath());
            
            throw $e;
        } finally {
            // 秒传则删除当前上传的文件
            if (isset($info)) {
                $filesystem->delete($result->getPath());
            }
        }
    }
    
    
    /**
     * 前端准备上传，进行秒传验证，如果不存在则创建一个准备上传的记录
     * @param SystemFileFrontPrepareUploadData $prepare
     * @return SystemFilePartPrepareResult
     * @throws DataNotFoundException
     * @throws DbException
     * @throws FilesystemException
     */
    public function frontPrepareUpload(SystemFileFrontPrepareUploadData $prepare) : SystemFilePartPrepareResult
    {
        $md5        = $prepare->getMd5();
        $userId     = $prepare->getUserId();
        $classType  = $prepare->getClassType();
        $classValue = $prepare->getClassValue();
        $filename   = $prepare->getFilename();
        $mimetype   = $prepare->getMimetype();
        $filesize   = $prepare->getFilesize();
        $fast       = false;
        $path       = '';
        $setting    = StorageSetting::instance();
        $disk       = $prepare->getDisk() ?: $setting->getDisk();
        $filesystem = Filesystem::disk($disk);
        
        // 处理图片
        $extension  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $imageStyle = $setting->getImageStyle($classType, $disk);
        $styleRule  = '';
        if (array_key_exists($extension, FormatParameter::getFormatMap()) && $imageStyle !== '') {
            $styleRule = $filesystem->image()->getStyleByCache($imageStyle)->rule;
        }
        
        // 查询是否可以秒传
        $uniqueId = static::createUniqueId($md5, $styleRule);
        if ($info = $this->where(SystemFileField::disk($disk))->whereComplete()->findInfoByUniqueId($uniqueId)) {
            $path = $info->path;
            $fast = Filesystem::disk($info->disk)->fileExists($path);
        }
        
        // 秒传
        if ($fast) {
            $data = clone $info;
            $data->setId(null);
            $data->setUserId($userId);
            $data->setClassType($classType);
            $data->setClassValue($classValue);
            $data->setName($filename);
            $data->setFast(true);
            $data->setPending(false);
        } else {
            FileHelper::checkExtension($setting->getAllowExtensions($classType), $extension);
            FileHelper::checkFilesize($setting->getMaxSize($classType), $filesize);
            if ($mimetype) {
                FileHelper::checkMimetype($setting->getMimeType($classType), $mimetype);
            }
            
            $data = SystemFileField::init();
            $data->setName($filename);
            $data->setHash($md5);
            $data->setExtension($extension);
            $data->setUserId($userId);
            $data->setClassType($classType);
            $data->setClassValue($classValue);
            $data->setMimeType($mimetype);
            $data->setSize($filesize);
            $data->setDisk($disk);
            $data->setPath($path ?: static::createFilename($classType, $classValue, $userId, $filename, $extension));
            $data->setUrl($filesystem->url($data->path));
            $data->setUniqueId($uniqueId);
            $data->setFast(false);
            $data->setPending(true);
        }
        $info = $this->create($data);
        
        // 启用分块上传
        $uploadId = '';
        if (!$fast) {
            $uploadId = $filesystem->front()
                ->frontPrepareUpload($data->path, $filename, $md5, $filesize, $mimetype, $prepare->isPart());
        }
        
        return new SystemFilePartPrepareResult(
            $info,
            $uploadId,
            $filesystem->front()->getFrontServerUrl(Request::isSsl())
        );
    }
    
    
    /**
     * 前端完成上传
     * @param int    $id 文件ID
     * @param string $uploadId 分块上传ID
     * @param array  $parts 分块数据
     * @throws Throwable
     */
    public function frontDoneUpload(int $id, string $uploadId = '', array $parts = [])
    {
        $info = $this->getInfo($id);
        if (!$info->pending) {
            throw new RuntimeException('该文件已上传完成');
        }
        
        $filesystem = Filesystem::disk($info->disk);
        $setting    = StorageSetting::instance();
        try {
            $result   = $filesystem->front()->frontDoneUpload($info->path, $uploadId, $parts);
            $filesize = $result['filesize'] ?? 0;
            $mimetype = $result['mimetype'] ?? '';
            
            FileHelper::checkFilesize($setting->getMaxSize($info->classType), $filesize);
            FileHelper::checkMimetype($setting->getMimeType($info->classType), $mimetype);
            
            // 组装更新参数
            $data = SystemFileField::init();
            $data->setPending(false);
            $data->setWidth($result['width'] ?? 0);
            $data->setHeight($result['height'] ?? 0);
            $data->setMimeType($mimetype);
            $data->setSize($filesize);
            
            // 处理图片
            $imageStyle = $setting->getImageStyle($info->classType, $info->disk);
            if (array_key_exists($info->extension, FormatParameter::getFormatMap()) && $imageStyle !== '') {
                $imageResult = Image::path($info->path)->disk($filesystem)->style($imageStyle)->save();
                $data->setWidth($imageResult->getWidth());
                $data->setHeight($imageResult->getHeight());
                $data->setSize($imageResult->getSize());
            }
            
            $this->where(SystemFileField::id($info->id))->update($data);
        } catch (Throwable $e) {
            // 删除数据
            try {
                $this->remove($info->id);
            } catch (Throwable $e) {
                // 忽略异常
            }
            
            throw $e;
        }
    }
    
    
    /**
     * 获取前端上传临时令牌
     * @param int $id 文件ID
     * @param int $expire 有效时长秒
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getFrontTmpToken(int $id, int $expire = 1800) : array
    {
        $info = $this->getInfo($id);
        if (!$info->pending) {
            throw new RuntimeException('该文件已上传完成');
        }
        
        return Filesystem::disk($info->disk)->front()->getFrontTmpToken($info->path, $expire);
    }
    
    
    /**
     * 上传分块到本地磁盘
     * @param int         $id 文件ID
     * @param File|string $file 上传文件对象
     * @param string      $uploadId uploadId
     * @param int         $partNumber 分块编号
     * @return array{etag: string, filesize: int, part_number: int} 该数据用于执行 {@see SystemFile::frontDoneUpload()} 时回传
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function frontLocalUpload(int $id, File|string $file, string $uploadId = '', int $partNumber = 0) : array
    {
        $front = FilesystemHelper::public()->front();
        if (!$front instanceof Local) {
            throw new ClassNotExtendsException($front, Local::class);
        }
        
        $info = $this->getInfo($id);
        if ($info->disk != FilesystemHelper::STORAGE_PUBLIC) {
            throw new RuntimeException('非本地磁盘系统');
        }
        
        if (!$info->pending) {
            throw new RuntimeException('该文件已上传完成');
        }
        
        return $front->upload(
            $info->path,
            $file,
            $uploadId,
            $partNumber
        );
    }
}