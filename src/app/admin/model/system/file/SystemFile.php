<?php

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\App;
use BusyPHP\exception\VerifyException;
use BusyPHP\exception\SQLException;
use BusyPHP\helper\file\File;
use BusyPHP\model;
use BusyPHP\helper\util\Transform;

/**
 * 文件管理模型
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2017 busy^life <busy.life@qq.com>
 * @version $Id: 2017-05-30 下午7:38 SystemFile.php busy^life $
 */
class SystemFile extends Model
{
    //+--------------------------------------
    //| 文件类型
    //+--------------------------------------
    /** 图片 */
    const FILE_TYPE_IMAGE = 'image';
    
    /** 视频 */
    const FILE_TYPE_VIDEO = 'video';
    
    /** 附件 */
    const FILE_TYPE_FILE = 'file';
    
    //+--------------------------------------
    //| 其它
    //+--------------------------------------
    /** 临时附件前缀 */
    const MARK_VALUE_TMP_PREFIX = 'tmp_';
    
    
    /**
     * 获取附件信息
     * @param int $id
     * @return array
     * @throws SQLException
     */
    public function getInfo($id)
    {
        return parent::getInfo(floatval($id), '附件不存在');
    }
    
    
    /**
     * 执行添加
     * @param SystemFileField $insert
     * @return int
     * @throws SQLException
     */
    public function insertData($insert)
    {
        $insert->createTime = time();
        $insert->urlHash    = md5($insert->url);
        if (!$insertId = $this->addData($insert)) {
            throw new SQLException('插入附件记录失败', $this);
        }
        
        return $insertId;
    }
    
    
    /**
     * 通过临时文件标识转正
     * @param string $type 文件分类
     * @param string $tmp 临时文件分类值
     * @param string $value 新文件分类值
     * @throws SQLException
     */
    public function updateMarkValueByTmp($type, $tmp, $value)
    {
        $where            = SystemFileField::init();
        $where->markType  = trim($type);
        $where->markValue = trim($tmp);
        
        $save            = SystemFileField::init();
        $save->markValue = trim($value);
        if (false === $result = $this->whereof($where)->saveData($save)) {
            throw new SQLException('修正附件标识失败', $this);
        }
    }
    
    
    /**
     * 通过文件ID转正
     * @param string $type 文件分类
     * @param int    $id 附件ID
     * @param string $value 新文件分类值
     * @throws SQLException
     */
    public function updateMarkValueById($type, $id, $value)
    {
        $where           = SystemFileField::init();
        $where->markType = trim($type);
        $where->id       = floatval($id);
        
        
        $save            = SystemFileField::init();
        $save->markValue = trim($value);
        if (false === $this->whereof($where)->saveData($save)) {
            throw new SQLException('修正附件标识失败', $this);
        }
    }
    
    
    /**
     * 通过文件分类标识获取文件地址
     * @param string $markType
     * @param string $markValue
     * @return string
     * @throws SQLException
     */
    public function getUrlByMark($markType, $markValue)
    {
        $where            = SystemFileField::init();
        $where->markType  = trim($markType);
        $where->markValue = trim($markValue);
        $url              = $this->whereof($where)->getField('url');
        if (!$url) {
            throw new SQLException('附件不存在', $this);
        }
        
        return $url;
    }
    
    
    /**
     * 通过ID获取文件地址
     * @param $id
     * @return string
     * @throws SQLException
     */
    public function getUrlById($id)
    {
        $url = $this->one($id)->getField('url');
        if (!$url) {
            throw new SQLException('附件不存在', $this);
        }
        
        return $url;
    }
    
    
    /**
     * 执行删除
     * @param int $id
     * @return int
     * @throws VerifyException
     * @throws SQLException
     */
    public function del($id)
    {
        $fileInfo = $this->getInfo($id);
        $filePath = App::urlToPath($fileInfo['url']);
        $res      = parent::del($id);
        
        if (is_file($filePath) && !unlink($filePath)) {
            throw new VerifyException('无法删除附件', $filePath);
        }
        
        return $res;
    }
    
    
    /**
     * 通过附件地址删除附件
     * @param string $url 附件地址
     * @throws VerifyException
     * @throws SQLException
     */
    public function delByUrl($url)
    {
        if (!$url) {
            return;
        }
        
        $where          = SystemFileField::init();
        $where->urlHash = md5(trim($url));
        if (false === $this->whereof($where)->deleteData()) {
            throw new SQLException('删除附件记录失败', $this);
        }
        
        
        $filePath = App::urlToPath($url);
        if (is_file($filePath) && !unlink($filePath)) {
            throw new VerifyException('无法删除附件', $filePath);
        }
    }
    
    
    /**
     * 通过附件标识删除附件
     * @param string      $markType 标识类型
     * @param string|null $markValue 标识值
     * @throws SQLException
     * @throws VerifyException
     */
    public function delByMark($markType, $markValue = null)
    {
        $where           = SystemFileField::init();
        $where->markType = trim($markType);
        if ($markValue) {
            $where->markValue = trim($markValue);
        }
        
        $fileInfo = $this->whereof($where)->findData();
        if (!$fileInfo) {
            return;
        }
        
        
        if (false === $this->deleteData($fileInfo['id'])) {
            throw new SQLException('删除附件记录失败', $this);
        }
        
        $filePath = App::urlToPath($fileInfo['url']);
        if (is_file($filePath) && !unlink($filePath)) {
            throw new VerifyException('无法删除附件', $filePath);
        }
    }
    
    
    /**
     * 获取附件类型
     * @param null|string $var
     * @return array|mixed
     */
    public static function getTypes($var = null)
    {
        $array = [
            self::FILE_TYPE_IMAGE => '图片',
            self::FILE_TYPE_VIDEO => '视频',
            self::FILE_TYPE_FILE  => '附件',
        ];
        if (is_null($var)) {
            return $array;
        }
        
        return $array[$var];
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
    
    
    /**
     * 解析数据列表
     * @param array $list
     * @return array
     */
    public static function parseList($list)
    {
        return parent::parseList($list, function($list) {
            foreach ($list as $i => $r) {
                $r['classify_name']      = self::getTypes($r['classify']);
                $r['format_create_time'] = Transform::date($r['create_time']);
                $r['is_admin']           = Transform::dataToBool($r['is_admin']);
                $sizes                   = Transform::formatBytes($r['size'], true);
                $r['size_unit']          = $sizes['unit'];
                $r['size_num']           = $sizes['number'];
                $r['format_size']        = "{$r['size_num']} {$r['size_unit']}";
                $r['filename']           = File::pathInfo($r['url'], PATHINFO_BASENAME);
                $list[$i]                = $r;
            }
            
            return $list;
        });
    }
}