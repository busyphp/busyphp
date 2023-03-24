<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\file\chunks;

use BusyPHP\app\admin\model\system\file\fragment\SystemFileFragment;
use BusyPHP\app\admin\model\system\file\fragment\SystemFileFragmentField;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\helper\FileHelper;
use BusyPHP\helper\FilesystemHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use DomainException;
use think\File;
use Throwable;

/**
 * SystemFileChunks
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/8 9:18 PM SystemFileChunks.php $
 * @method SystemFileChunksField getInfo(string $id, string $notFoundMessage = null)
 * @method SystemFileChunksField|null findInfo(string $id = null)
 * @method SystemFileChunksField[] selectList()
 * @method SystemFileChunksField[] indexList(string|Entity $key = '')
 * @method SystemFileChunksField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 */
class SystemFileChunks extends Model implements ContainerInterface
{
    protected string $fieldClass          = SystemFileChunksField::class;
    
    protected string $dataNotFoundMessage = '碎片不存在';
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
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
        $tmp  = FilesystemHelper::runtime();
        $dir  = static::buildDir($fragmentId);
        $file = FileHelper::convertUploadToFile($data)->move($tmp->path($dir), static::buildName($number));
        $size = $file->getSize();
        
        $fragmentModel = SystemFileFragment::init();
        $this->startTrans();
        try {
            $fragmentInfo = $fragmentModel->lock(true)->getInfo($fragmentId);
            
            // 插入块记录
            $data = SystemFileChunksField::init();
            $data->setFragmentId($fragmentId);
            $data->setNumber($number);
            $data->setCreateTime(time());
            $data->setSize($size);
            $data->setId(static::buildId($fragmentId, $number));
            $this->replace(true)->insert($data);
            
            // 更新碎片总表
            $fragmentData = SystemFileFragmentField::init();
            $fragmentData->setNumber(
                $this
                    ->where(SystemFileChunksField::fragmentId($fragmentId))
                    ->count()
            );
            $fragmentData->setSize(
                $this
                    ->where(SystemFileChunksField::fragmentId($fragmentId))
                    ->sum(SystemFileChunksField::size())
            );
            $fragmentModel->where(SystemFileFragmentField::id($fragmentInfo->id))->update($fragmentData);
            
            $this->commit();
            
            return $data->id;
        } catch (Throwable $e) {
            $this->rollback();
            
            $tmp->deleteDirectory($dir);
            
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
        return sprintf("parts/%s", $fragmentId);
    }
    
    
    /**
     * 生成碎片名
     * @param int $number
     * @return string
     */
    public static function buildName(int $number) : string
    {
        return sprintf("%s.part", $number);
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