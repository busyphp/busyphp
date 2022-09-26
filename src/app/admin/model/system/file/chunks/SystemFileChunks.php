<?php

namespace BusyPHP\app\admin\model\system\file\chunks;

use BusyPHP\app\admin\model\system\file\fragment\SystemFileFragment;
use BusyPHP\app\admin\model\system\file\fragment\SystemFileFragmentField;
use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\helper\FileHelper;
use BusyPHP\Model;
use DomainException;
use think\File;
use Throwable;

/**
 * SystemFileChunks
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/8 9:18 PM SystemFileChunks.php $
 * @method SystemFileChunksInfo getInfo($data, $notFoundMessage = null)
 * @method SystemFileChunksInfo findInfo($data = null, $notFoundMessage = null)
 * @method SystemFileChunksInfo[] selectList()
 * @method SystemFileChunksInfo[] buildListWithField(array $values, $key = null, $field = null)
 */
class SystemFileChunks extends Model
{
    protected $bindParseClass      = SystemFileChunksInfo::class;
    
    protected $dataNotFoundMessage = '碎片不存在';
    
    protected $findInfoFilter      = 'intval';
    
    
    /**
     * 创建碎片分块
     * @param int   $fragmentId 碎片ID
     * @param int   $number 分块序号
     * @param mixed $data $_FILES 或 {@see File}
     * @return string
     * @throws Throwable
     */
    public function create(int $fragmentId, int $number, $data) : string
    {
        if (!$fragmentId) {
            throw new ParamInvalidException('$fragmentId');
        }
        if ($number < 1 || $number > 10000) {
            throw new DomainException('number range is 1 - 10000');
        }
        
        // 将文件移动至分块目录
        $tmp  = StorageSetting::init()->getRuntimeFileSystem();
        $dir  = self::buildDir($fragmentId);
        $file = FileHelper::convertUploadToFile($data)->move($tmp->path($dir), self::buildName($number));
        $size = $file->getSize();
        
        $fragmentModel = SystemFileFragment::init();
        $this->startTrans();
        try {
            $fragmentInfo = $fragmentModel->lock(true)->getInfo($fragmentId);
            
            // 插入块记录
            $data             = SystemFileChunksField::init();
            $data->fragmentId = $fragmentId;
            $data->number     = $number;
            $data->createTime = time();
            $data->size       = $size;
            $data->id         = self::buildId($fragmentId, $number);
            $this->addData($data, true);
            
            // 更新碎片总表
            $fragmentData         = SystemFileFragmentField::init();
            $fragmentData->number = $this
                ->whereEntity(SystemFileChunksField::fragmentId($fragmentId))
                ->count();
            $fragmentData->size   = $this
                ->whereEntity(SystemFileChunksField::fragmentId($fragmentId))
                ->sum(SystemFileChunksField::size());
            $fragmentModel->whereEntity(SystemFileFragmentField::id($fragmentInfo->id))->saveData($fragmentData);
            
            $this->commit();
            
            return $data->id;
        } catch (Throwable $e) {
            $this->rollback();
            
            $tmp->deleteDir($dir);
            
            throw $e;
        }
    }
    
    
    /**
     * 生成碎片目录名
     * @param int $fragmentId
     * @return string
     */
    public static function buildDir(int $fragmentId) : string
    {
        return "parts/$fragmentId";
    }
    
    
    /**
     * 生成碎片名
     * @param int $number
     * @return string
     */
    public static function buildName(int $number) : string
    {
        return "$number.part";
    }
    
    
    /**
     * 生成碎片分块ID
     * @param int $fragmentId 碎片ID
     * @param int $number 分块序号
     * @return string
     */
    public static function buildId(int $fragmentId, int $number) : string
    {
        return md5($fragmentId . $number);
    }
}