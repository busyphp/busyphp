<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\App;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\logs\SystemLogs;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\StringHelper;
use BusyPHP\model;
use Exception;
use League\Flysystem\FileNotFoundException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\exception\FileException;
use think\facade\Filesystem;

/**
 * 文件管理模型
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2017 busy^life <busy.life@qq.com>
 * @version $Id: 2017-05-30 下午7:38 SystemFile.php busy^life $
 * @method SystemFileInfo findInfo($data = null, $notFoundMessage = null)
 * @method SystemFileInfo getInfo($data, $notFoundMessage = null)
 * @method SystemFileInfo[] selectList()
 */
class SystemFile extends Model
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
    
    /** @var string 文件 */
    const FILE_TYPE_FILE = 'file';
    
    //+--------------------------------------
    //| 其它
    //+--------------------------------------
    /** 临时附件前缀 */
    const MARK_VALUE_TMP_PREFIX = 'temp_';
    
    protected $dataNotFoundMessage = '文件数据不存在';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = SystemFileInfo::class;
    
    
    /**
     * 添加文件
     * @param SystemFileField $insert
     * @return int
     * @throws DbException
     */
    public function insertFile(SystemFileField $insert)
    {
        $list      = SystemFileClass::init()->getList();
        $classInfo = $list[$insert->classType] ?? null;
        if (!$classInfo) {
            throw new VerifyException('文件分类不能为空', 'class_type');
        }
        
        $insert->createTime = time();
        $insert->urlHash    = md5($insert->url);
        $insert->client     = App::init()->request->isCli() ? SystemLogs::CLI_CLIENT_KEY : App::init()->getDirName();
        $insert->type       = $classInfo->type;
        
        return $this->addData($insert);
    }
    
    
    /**
     * 通过文件分类和业务值更新业务值
     * @param string $classType 文件分类
     * @param mixed  $classValue 文件业务值
     * @param mixed  $newValue 新文件分类值
     * @return int
     * @throws DbException
     */
    public function updateValueByClass(string $classType, $classValue, $newValue) : int
    {
        return $this->whereClass($classType, trim($classValue))
            ->setField(SystemFileField::classValue(), trim($newValue));
    }
    
    
    /**
     * 通过文件ID更新业务值
     * @param int    $id 文件ID
     * @param string $newValue 新文件分类值
     * @return int
     * @throws DbException
     */
    public function updateValueById($id, $newValue) : int
    {
        return $this->whereEntity(SystemFileField::id(floatval($id)))
            ->setField(SystemFileField::classValue(), trim($newValue));
    }
    
    
    /**
     * 查询分类条件
     * @param string $classType 文件分类
     * @param string $classValue 文件业务参数
     * @return $this
     */
    public function whereClass(string $classType, $classValue = null) : self
    {
        $classValue = trim($classValue);
        $this->whereEntity(SystemFileField::classType(trim($classType)));
        
        if ($classValue !== null) {
            $this->whereEntity(SystemFileField::classValue($classValue));
        }
        
        return $this;
    }
    
    
    /**
     * 删除附件
     * @param int $data
     * @return int
     * @throws VerifyException
     * @throws Exception
     */
    public function deleteInfo($data) : int
    {
        $this->startTrans();
        try {
            $info = $this->lock(true)->getInfo(intval($data));
            
            // 删除文件
            if (!static::deleteFile($info)) {
                throw new FileException("文件删除失败: {$info->path}");
            }
            
            // 删除数据
            $res = parent::deleteInfo($info->id);
            
            $this->commit();
            
            return $res;
        } catch (Exception $e) {
            $this->rollback();
            
            throw $e;
        }
    }
    
    
    /**
     * 通过文件url删除
     * @param string $url 附件地址
     * @return int
     * @throws DbException
     * @throws VerifyException
     * @throws Exception
     */
    public function deleteByUrl(string $url) : int
    {
        $url = trim($url);
        
        return $this->deleteInfo($this->whereEntity(SystemFileField::urlHash(md5($url)))
            ->failException(true)
            ->findInfo()->id);
    }
    
    
    /**
     * 通过文件分类删除
     * @param string $classType 标识类型
     * @param string $classValue 标识值
     * @return int
     * @throws DataNotFoundException
     * @throws DbException
     * @throws VerifyException
     * @throws Exception
     */
    public function deleteByClass($classType, $classValue = null)
    {
        return $this->deleteInfo($this->whereClass($classType, $classValue)->failException(true)->findInfo()->id);
    }
    
    
    /**
     * 获取附件类型
     * @param string $var
     * @return array|mixed
     */
    public static function getTypes($var = null)
    {
        return self::parseVars(self::parseConst(self::class, 'FILE_TYPE_', [], function($item) {
            return $item['name'];
        }), $var);
    }
    
    
    /**
     * 创建一个临时的业务参数
     * @param null|string $value
     * @return string
     */
    public static function createTempClassValue($value = null)
    {
        return self::MARK_VALUE_TMP_PREFIX . md5(($value ?: uniqid()) . StringHelper::random(32));
    }
    
    
    /**
     * 通过信息删除文件
     * @param SystemFileInfo $info
     * @return bool
     */
    public static function deleteFile(SystemFileInfo $info) : bool
    {
        try {
            return Filesystem::disk($info->disk)->delete($info->path);
        } catch (FileNotFoundException $e) {
            return true;
        }
    }
}