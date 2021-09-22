<?php

namespace BusyPHP\app\admin\model\system\config;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\Handle;
use BusyPHP\model;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 系统键值对配置数据模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午3:20 SystemConfig.php $
 * @method SystemConfigInfo findInfo($data = null, $notFoundMessage = null)
 * @method SystemConfigInfo getInfo($data, $notFoundMessage = null)
 * @method SystemConfigInfo[] selectList()
 */
class SystemConfig extends Model
{
    protected $dataNotFoundMessage = '配置不存在';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = SystemConfigInfo::class;
    
    
    /**
     * 添加配置
     * @param SystemConfigField $insert
     * @return int
     * @throws DbException
     */
    public function insertData($insert)
    {
        $insertId = $this->addData($insert);
        
        // 刷新缓存
        $this->refreshCache($insertId);
        
        return $insertId;
    }
    
    
    /**
     * 修改配置
     * @param SystemConfigField $update 配置ID
     * @throws DbException
     * @throws ParamInvalidException
     */
    public function updateData($update)
    {
        if ($update->id < 1) {
            throw new ParamInvalidException('id');
        }
        
        $this->whereEntity(SystemConfigField::id($update->id))->saveData($update);
        $this->refreshCache($update->id);
    }
    
    
    /**
     * 删除配置
     * @param int $data 信息ID
     * @return int
     * @throws DbException
     * @throws VerifyException
     * @throws DataNotFoundException
     */
    public function deleteInfo($data) : int
    {
        $info = $this->getInfo($data);
        if ($info->isSystem) {
            throw new VerifyException('禁止删除系统配置');
        }
        
        $result = parent::deleteInfo($info->id);
        
        // 刷新缓存
        $this->refreshCache($info->type, true);
        
        return $result;
    }
    
    
    /**
     * 设置键值数据
     * @param string $key 数据名称
     * @param mixed  $value 数据值
     * @throws DbException
     * @throws ParamInvalidException
     */
    public function setKey($key, $value)
    {
        $key = trim($key);
        if (!$key) {
            throw new ParamInvalidException('key');
        }
        
        $update = SystemConfigField::init();
        $update->setContent($value);
        $this->whereEntity(SystemConfigField::type($key))->saveData($update);
        
        // 刷新缓存
        $this->refreshCache($key, true);
    }
    
    
    /**
     * 获取键值数据
     * @param string $key 数据名称
     * @param bool   $must 强制更新缓存
     * @return mixed
     */
    public function get($key, $must = false)
    {
        $cache = $this->getCache($key);
        if (!$cache || $must) {
            try {
                $cache = $this->whereEntity(SystemConfigField::type($key))
                    ->failException(true)
                    ->findInfo(null, "找不到配置[{$key}]的数据");
            } catch (Exception $e) {
                Handle::log($e);
                $this->deleteCache($key);
                
                return null;
            }
            
            $this->setCache($key, $cache);
        }
        
        return $cache->content;
    }
    
    
    /**
     * 刷新缓存
     * @param int  $id
     * @param bool $idIsType
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function refreshCache($id = 0, $idIsType = false)
    {
        // 刷新全部缓存
        if (is_numeric($id) && $id < 1) {
            $list = $this->selectList();
            foreach ($list as $i => $item) {
                $this->get($item->type, true);
            }
            
            return;
        }
        
        // 传入的参数就是type
        if ($idIsType) {
            $this->get($id, true);
            
            return;
        }
        
        
        $info = $this->getInfo($id);
        $this->get($info->type, true);
    }
}