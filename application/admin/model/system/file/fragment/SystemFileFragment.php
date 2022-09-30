<?php

namespace BusyPHP\app\admin\model\system\file\fragment;

use BusyPHP\app\admin\model\system\file\chunks\SystemFileChunks;
use BusyPHP\app\admin\model\system\file\chunks\SystemFileChunksField;
use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\exception\VerifyException;
use BusyPHP\Model;
use RuntimeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\exception\FileException;
use Throwable;

/**
 * SystemFileFragment
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/8 9:15 PM SystemFileFragment.php $
 * @method SystemFileFragmentInfo getInfo($data, $notFoundMessage = null)
 * @method SystemFileFragmentInfo findInfo($data = null, $notFoundMessage = null)
 * @method SystemFileFragmentInfo[] selectList()
 * @method SystemFileFragmentInfo[] buildListWithField(array $values, $key = null, $field = null)
 */
class SystemFileFragment extends Model
{
    protected $bindParseClass      = SystemFileFragmentInfo::class;
    
    protected $dataNotFoundMessage = '碎片不存在';
    
    protected $findInfoFilter      = 'intval';
    
    
    /**
     * 创建碎片
     * @param int    $userId 用户ID
     * @param string $path 碎片路径
     * @param int    $fileId 附件ID
     * @return int
     * @throws DbException
     */
    public function create(int $userId, string $path, int $fileId = 0) : int
    {
        $path      = trim(trim($path), '/');
        $pathInfo  = pathinfo($path);
        $filename  = $pathInfo['filename'] ?? '';
        $extension = $pathInfo['extension'] ?? '';
        if (!$path) {
            throw new VerifyException('碎片名称不能为空', 'path');
        }
        if (!$extension || !$filename) {
            throw new VerifyException('碎片名称格式有误', 'path');
        }
        
        $data             = SystemFileFragmentField::init();
        $data->userId     = $userId;
        $data->fileId     = $fileId;
        $data->path       = $path;
        $data->createTime = time();
        
        return (int) $this->addData($data);
    }
    
    
    /**
     * 删除碎片
     * @param $data
     * @return int
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    public function deleteInfo($data) : int
    {
        $chunks = SystemFileChunks::init();
        $this->startTrans();
        try {
            $info = $this->lock(true)->getInfo($data);
            $dir  = SystemFileChunks::buildDir($info->id);
            if (!StorageSetting::init()->getRuntimeFileSystem()->deleteDir($dir)) {
                throw new FileException("删除碎片失败: $dir");
            }
            
            $result = parent::deleteInfo($info->id);
            $chunks->whereEntity(SystemFileChunksField::fragmentId($info->id))->delete();
            
            $this->commit();
            
            return $result;
        } catch (Throwable $e) {
            $this->rollback();
            
            throw $e;
        }
    }
    
    
    /**
     * 合并碎片
     * @param int $id 碎片ID
     * @param int $total 碎片总数
     * @return SystemFileFragmentInfo
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    public function merge(int $id, int $total) : SystemFileFragmentInfo
    {
        $chunksModel = SystemFileChunks::init();
        
        // 标记为合并中
        $this->startTrans();
        try {
            $info = $this->lock(true)->getInfo($id);
            if ($info->merging) {
                throw new RuntimeException('正在合并中');
            }
            
            // 查找待合并的碎片
            $chunkList  = $chunksModel
                ->whereEntity(SystemFileChunksField::fragmentId($info->id))
                ->order(SystemFileChunksField::number(), 'asc')
                ->selectList();
            $chunkTotal = count($chunkList);
            if ($chunkTotal < 1) {
                throw new RuntimeException('找不到碎片');
            }
            if ($chunkTotal != $total) {
                throw new RuntimeException('碎片数不匹配');
            }
            
            // 校验数字连续性
            foreach ($chunkList as $i => $item) {
                $prev = $chunkList[$i - 1] ?? null;
                if ($prev && $prev->number + 1 != $item->number) {
                    throw new RuntimeException('碎片不连续');
                }
            }
            
            $this->whereEntity(SystemFileFragmentField::id($id))->setField(SystemFileFragmentField::merging(), true);
            $this->commit();
        } catch (Throwable $e) {
            $this->rollback();
            
            throw $e;
        }
        
        // 合并碎片
        try {
            $resource = null;
            $setting  = StorageSetting::init();
            $tmp      = $setting->getRuntimeFileSystem();
            $local    = $setting->getLocalFileSystem();
            
            // 创建一个空文件
            $local->put($info->path, '');
            if (!$resource = fopen($local->path($info->path), 'w+b')) {
                throw new FileException("打开文件失败: $info->path");
            }
            
            // 遍历碎片写入到文件
            foreach ($chunkList as $item) {
                if (!$body = $tmp->read($item->path)) {
                    throw new FileException("读取碎片失败: $item->path");
                }
                if (!fwrite($resource, $body)) {
                    throw new FileException("写入碎片失败: $info->path");
                }
            }
        } catch (Throwable $e) {
            $this->startTrans();
            try {
                $this->lock(true)->getInfo($info->id);
                $this->whereEntity(SystemFileFragmentField::id($id))
                    ->setField(SystemFileFragmentField::merging(), false);
                
                $this->commit();
            } catch (Throwable $e) {
                $this->rollback();
                
                throw $e;
            }
            
            throw $e;
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
        
        // 清空碎片
        try {
            $this->deleteInfo($info->id);
        } catch (Throwable $e) {
        }
        
        return $info;
    }
}