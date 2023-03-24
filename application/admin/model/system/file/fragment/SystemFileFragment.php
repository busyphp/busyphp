<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\file\fragment;

use BusyPHP\app\admin\model\system\file\chunks\SystemFileChunks;
use BusyPHP\app\admin\model\system\file\chunks\SystemFileChunksField;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\FilesystemHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
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
 * @method SystemFileFragmentField getInfo(int $id, string $notFoundMessage = null)
 * @method SystemFileFragmentField|null findInfo(int $id = null)
 * @method SystemFileFragmentField[] selectList()
 * @method SystemFileFragmentField[] indexList(string|Entity $key = '')
 * @method SystemFileFragmentField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 */
class SystemFileFragment extends Model implements ContainerInterface
{
    protected string $fieldClass          = SystemFileFragmentField::class;
    
    protected string $dataNotFoundMessage = '碎片不存在';
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
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
        
        $data = SystemFileFragmentField::init();
        $data->setUserId($userId);
        $data->setFileId($fileId);
        $data->setPath($path);
        $data->setCreateTime(time());
        
        return (int) $this->insert($data);
    }
    
    
    /**
     * 删除碎片
     * @param int $id
     * @return int
     * @throws Throwable
     */
    public function remove(int $id) : int
    {
        return $this->transaction(function() use ($id) {
            $info = $this->lock(true)->getInfo($id);
            $dir  = SystemFileChunks::class()::buildDir($info->id);
            FilesystemHelper::runtime()->deleteDirectory($dir);
            
            $result = $this->delete($info->id);
            SystemFileChunks::init()->where(SystemFileChunksField::fragmentId($info->id))->delete();
            
            return $result;
        });
    }
    
    
    /**
     * 合并碎片
     * @param int $id 碎片ID
     * @param int $total 碎片总数
     * @return SystemFileFragmentField
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    public function merge(int $id, int $total) : SystemFileFragmentField
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
                ->where(SystemFileChunksField::fragmentId($info->id))
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
            
            $this->where(SystemFileFragmentField::id($id))->setField(SystemFileFragmentField::merging(), true);
            $this->commit();
        } catch (Throwable $e) {
            $this->rollback();
            
            throw $e;
        }
        
        // 合并碎片
        try {
            $resource = null;
            $tmp      = FilesystemHelper::runtime();
            $local    = FilesystemHelper::public();
            
            // 创建一个空文件
            $local->write($info->path, '');
            if (!$resource = fopen($local->path($info->path), 'w+b')) {
                throw new FileException("打开文件失败: $info->path");
            }
            
            // 遍历碎片写入到文件
            foreach ($chunkList as $item) {
                try {
                    $body = $tmp->read($item->path);
                } catch (Throwable $e) {
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
                $this->where(SystemFileFragmentField::id($id))
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
            $this->remove($info->id);
        } catch (Throwable $e) {
        }
        
        return $info;
    }
}