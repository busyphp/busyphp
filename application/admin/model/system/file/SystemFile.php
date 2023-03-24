<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\facade\Image;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\helper\FileHelper;
use BusyPHP\helper\FilesystemHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\image\parameter\FormatParameter;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\model;
use BusyPHP\model\Entity;
use BusyPHP\Upload;
use BusyPHP\upload\front\Local;
use League\Flysystem\FilesystemException;
use RangeException;
use RuntimeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\Raw;
use think\exception\FileException;
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
 * @method SystemFileField|null findInfoByPath(string $path)
 * @method SystemFileField|null findInfoByUrlHash(string $urlHash)
 * @method SystemFileField|null findInfoByUrl(string $url)
 */
class SystemFile extends Model implements ContainerInterface
{
    //+--------------------------------------
    //| 文件类型
    //+--------------------------------------
    /** @var string 图片 */
    const FILE_TYPE_IMAGE = 'image';
    
    /** @var string 视频 */
    const FILE_TYPE_VIDEO = 'video';
    
    /** @var string 音频 */
    const FILE_TYPE_AUDIO = 'audio';
    
    /** @var string 文档 */
    const FILE_TYPE_DOC = 'doc';
    
    /** @var string 文件 */
    const FILE_TYPE_FILE = 'file';
    
    //+--------------------------------------
    //| 其它
    //+--------------------------------------
    /** 临时附件前缀 */
    const MARK_VALUE_TMP_PREFIX = 'temp_';
    
    protected string $dataNotFoundMessage = '文件不存在';
    
    protected string $fieldClass          = SystemFileField::class;
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 获取附件分类
     * @param string|null $var
     * @return array|mixed
     */
    public static function getTypes(string $var = null)
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstAttrs(self::class, 'FILE_TYPE_', ClassHelper::ATTR_NAME), $var);
    }
    
    
    /**
     * 创建一个临时的业务参数
     * @param null|string $value
     * @return string
     */
    public static function createTmp($value = null) : string
    {
        return static::MARK_VALUE_TMP_PREFIX . md5(($value ?: uniqid()) . StringHelper::random(32));
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
    public static function createFilename(string $classType, string $classValue, $userId, $file, string $extension = '') : string
    {
        $setting = StorageSetting::instance();
        
        // hash多层
        $type = $setting->getDirGenerateType();
        $dir  = '';
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
        
        if (!$extension) {
            throw new FileException('必须指定文件扩展名');
        }
        
        return $classType . $dir . DIRECTORY_SEPARATOR . md5(
                implode(',', [
                    StringHelper::uuid(),
                    $classType,
                    $classValue,
                    $userId
                ])
            ) . '.' . $extension;
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
            if (!$this->where(SystemFileField::urlHash(md5($info->url)))
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
            ->group(SystemFileField::urlHash())
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
     * @param SystemFileUploadParameter $parameter
     * @return SystemFileField
     * @throws Throwable
     */
    public function upload(SystemFileUploadParameter $parameter) : SystemFileField
    {
        $storage    = StorageSetting::instance();
        $disk       = $parameter->getDisk() ?: $storage->getDisk();
        $filesystem = Filesystem::disk($disk);
        $result     = Upload::init(
            $parameter->getParameter(),
            $filesystem,
            function($file, $extension) use ($parameter) {
                return static::createFilename(
                    $parameter->getClassType(),
                    $parameter->getClassValue(),
                    $parameter->getUserId(),
                    $file,
                    $extension
                );
            })
            ->limitMaxsize($storage->getMaxSize($parameter->getClassType()))
            ->limitMimetypes($storage->getMimeType($parameter->getClassType()))
            ->limitExtensions($storage->getAllowExtensions($parameter->getClassType()))
            ->save();
        
        try {
            // 处理图片
            $extension  = strtolower(pathinfo($result->getPath(), PATHINFO_EXTENSION));
            $imageStyle = $storage->getImageStyle($parameter->getClassType(), $disk);
            if (in_array($extension, array_keys(FormatParameter::getFormats())) && $imageStyle !== '') {
                $imageResult = Image::path($result->getPath())->style($imageStyle)->disk($filesystem)->save();
                $result->setFilesize($imageResult->getSize());
                $result->setWidth($imageResult->getWidth());
                $result->setHeight($imageResult->getHeight());
            }
            
            // 插入到数据库
            $data = SystemFileField::init();
            $data->setName($result->getBasename());
            $data->setUrl($filesystem->url($result->getPath()));
            $data->setSize($result->getFilesize());
            $data->setMimeType($result->getMimetype());
            $data->setHash($result->getMd5());
            $data->setWidth($result->getWidth());
            $data->setHeight($result->getHeight());
            $data->setPath($result->getPath());
            $data->setUserId($parameter->getUserId());
            $data->setClassValue($parameter->getClassValue());
            $data->setClassType($parameter->getClassType());
            $data->setExtension($extension);
            $data->setDisk($disk);
            
            return $this->create($data);
        } catch (Throwable $e) {
            // 删除文件
            $filesystem->delete($result->getPath());
            
            throw $e;
        }
    }
    
    
    /**
     * 前端准备上传
     * @param SystemFilePrepareUploadParameter $parameter
     * @return SystemFilePrepareUploadResult
     * @throws DataNotFoundException
     * @throws DbException
     * @throws FilesystemException
     */
    public function frontPrepareUpload(SystemFilePrepareUploadParameter $parameter) : SystemFilePrepareUploadResult
    {
        $md5        = $parameter->getMd5();
        $userId     = $parameter->getUserId();
        $classType  = $parameter->getClassType();
        $classValue = $parameter->getClassValue();
        $filename   = $parameter->getFilename();
        $mimetype   = $parameter->getMimetype();
        $filesize   = $parameter->getFilesize();
        
        // 校验文件分类
        if (!array_key_exists($classType, SystemFileClass::instance()->getList())) {
            throw new RangeException(sprintf('文件分类%s不存在', $classType));
        }
        
        $fast       = false;
        $path       = '';
        $setting    = StorageSetting::instance();
        $disk       = $parameter->getDisk() ?: $setting->getDisk();
        $filesystem = Filesystem::disk($disk);
        
        // 查询是否可以秒传
        if ($info = $this->where(SystemFileField::disk($disk))->whereComplete()->findInfoByHash($md5)) {
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
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
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
            $data->setFast(false);
            $data->setPending(true);
        }
        $info = $this->create($data);
        
        // 启用分块上传
        $uploadId = '';
        if (!$fast) {
            $uploadId = $filesystem->front()
                ->prepareUpload($data->path, $md5, $filesize, $mimetype, $parameter->isPart());
        }
        
        return new SystemFilePrepareUploadResult(
            $info,
            $uploadId,
            $filesystem->front()->getUrl(Request::isSsl())
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
            $result   = $filesystem->front()->doneUpload($info->path, $uploadId, $parts);
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
            if (in_array($info->extension, array_keys(FormatParameter::getFormats())) && $imageStyle !== '') {
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
    public function frontTmpToken(int $id, int $expire = 1800) : array
    {
        $info = $this->getInfo($id);
        if (!$info->pending) {
            throw new RuntimeException('该文件已上传完成');
        }
        
        return Filesystem::disk($info->disk)->front()->getTmpToken($info->path, $expire);
    }
    
    
    /**
     * 前端上传整个文件或分块到本地磁盘
     * @param int          $id 文件ID
     * @param UploadedFile $file 上传文件对象
     * @param string       $uploadId uploadId
     * @param int          $partNumber 分块编号
     * @return string ETag
     * @throws Throwable
     */
    public function frontLocalUpload(int $id, UploadedFile $file, string $uploadId = '', int $partNumber = 0) : string
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