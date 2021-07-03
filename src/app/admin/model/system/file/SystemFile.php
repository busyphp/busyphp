<?php

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\App;
use BusyPHP\exception\VerifyException;
use BusyPHP\exception\SQLException;
use BusyPHP\helper\file\File;
use BusyPHP\model;
use BusyPHP\helper\util\Transform;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

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
    
    /** @var string 附件 */
    const FILE_TYPE_FILE = 'file';
    
    //+--------------------------------------
    //| 其它
    //+--------------------------------------
    /** 临时附件前缀 */
    const MARK_VALUE_TMP_PREFIX = 'tmp_';
    
    protected $dataNotFoundMessage = '附件不存在';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = SystemFileInfo::class;
    
    
    /**
     * 执行添加
     * @param SystemFileField $insert
     * @return int
     * @throws DbException
     */
    public function insertData($insert)
    {
        $insert->createTime = time();
        $insert->urlHash    = md5($insert->url);
        
        return $this->addData($insert);
    }
    
    
    /**
     * 通过临时文件标识转正
     * @param string $type 文件分类
     * @param string $tmp 临时文件分类值
     * @param string $value 新文件分类值
     * @throws DbException
     */
    public function updateMarkValueByTmp($type, $tmp, $value)
    {
        $save            = SystemFileField::init();
        $save->markValue = trim($value);
        
        $this->whereEntity(SystemFileField::markType(trim($type)), SystemFileField::markValue(trim($tmp)))
            ->saveData($save);
    }
    
    
    /**
     * 通过文件ID转正
     * @param string $type 文件分类
     * @param int    $id 附件ID
     * @param string $value 新文件分类值
     * @throws DbException
     */
    public function updateMarkValueById($type, $id, $value)
    {
        $save            = SystemFileField::init();
        $save->markValue = trim($value);
        
        $this->whereEntity(SystemFileField::id(floatval($id)), SystemFileField::markValue(trim($type)))
            ->saveData($save);
    }
    
    
    /**
     * 通过文件分类标识获取文件地址
     * @param string $markType
     * @param string $markValue
     * @return string
     * @throws DbException
     */
    public function getUrlByMark($markType, $markValue)
    {
        $where            = SystemFileField::init();
        $where->markType  = trim($markType);
        $where->markValue = trim($markValue);
        
        return $this->whereEntity(SystemFileField::markType(trim($markType)))
            ->whereEntity(SystemFileField::markValue(trim($markValue)))
            ->failException(true)
            ->value(SystemFileField::url());
    }
    
    
    /**
     * 通过ID获取文件地址
     * @param $id
     * @return string
     * @throws DbException
     */
    public function getUrlById($id)
    {
        return $this->whereEntity(SystemFileField::id($id))->value(SystemFileField::url());
    }
    
    
    /**
     * 执行删除
     * @param int $data
     * @return int
     * @throws DbException
     * @throws VerifyException
     * @throws DataNotFoundException
     */
    public function deleteInfo($data) : int
    {
        $fileInfo = $this->getInfo($data);
        $filePath = App::urlToPath($fileInfo['url']);
        $res      = parent::deleteInfo($data);
        
        if (is_file($filePath) && !unlink($filePath)) {
            throw new VerifyException('无法删除附件', $filePath);
        }
        
        return $res;
    }
    
    
    /**
     * 通过附件地址删除附件
     * @param string $url 附件地址
     * @throws DbException
     * @throws VerifyException
     */
    public function delByUrl($url)
    {
        if (!$url) {
            return;
        }
        
        $this->whereEntity(SystemFileField::urlHash(md5(trim($url))))->delete();
        $filePath = App::urlToPath($url);
        if (is_file($filePath) && !unlink($filePath)) {
            throw new VerifyException('无法删除附件', $filePath);
        }
    }
    
    
    /**
     * 通过附件标识删除附件
     * @param string      $markType 标识类型
     * @param string|null $markValue 标识值
     * @throws DataNotFoundException
     * @throws DbException
     * @throws VerifyException
     */
    public function delByMark($markType, $markValue = null)
    {
        $where = SystemFileField::init();
        $this->whereEntity(SystemFileField::markType(trim($markType)));
        $where->markType = trim($markType);
        if ($markValue) {
            $where->markValue = SystemFileField::markValue(trim($markValue));
        }
        
        $fileInfo = $this->findInfo();
        if (!$fileInfo) {
            return;
        }
        
        $this->deleteInfo($fileInfo->id);
        $filePath = App::urlToPath($fileInfo['url']);
        if (is_file($filePath) && !unlink($filePath)) {
            throw new VerifyException('无法删除附件', $filePath);
        }
    }
    
    
    /**
     * 获取附件类型
     * @param string $var
     * @return array|mixed
     */
    public static function getTypes($var = null)
    {
        return self::parseVars(self::parseConst(self::class, 'TYPE_', [], function($item) {
            return $item['name'];
        }), $var);
    }
    
    
    /**
     * 创建唯一临时文件标识
     * @param null|string $value
     * @return string
     */
    public static function createTmpMarkValue($value = null)
    {
        return self::MARK_VALUE_TMP_PREFIX . md5(($value ? $value : uniqid()) . $_SERVER['HTTP_USER_AGENT'] . request()->ip() . rand(1000000, 9999999));
    }
}