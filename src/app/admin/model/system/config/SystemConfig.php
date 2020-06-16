<?php

namespace BusyPHP\app\admin\model\system\config;

use BusyPHP\exception\VerifyException;
use BusyPHP\exception\SQLException;
use BusyPHP\model;
use think\facade\Log;

/**
 * 系统键值对配置数据模型
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2017 busy^life <busy.life@qq.com>
 * @version $Id: 2017-06-01 下午11:38 SystemConfig.php busy^life $
 */
class SystemConfig extends Model
{
    /**
     * 获取配置信息
     * @param int $id
     * @return array
     * @throws SQLException
     */
    public function getInfo($id)
    {
        return parent::getInfo(floatval($id), '配置不存在');
    }
    
    
    /**
     * 添加配置
     * @param SystemConfigField $insert
     * @return int
     * @throws SQLException
     */
    public function insertData($insert)
    {
        if (!$insertId = $this->addData($insert)) {
            throw new SQLException('添加配置失败', $this);
        }
        
        // 刷新缓存
        $this->refreshCache($insertId);
        
        return $insertId;
    }
    
    
    /**
     * 修改配置
     * @param SystemConfigField $update 配置ID
     * @throws SQLException
     */
    public function updateData($update)
    {
        if (false === $result = $this->saveData($update)) {
            throw new SQLException('修改配置失败', $this);
        }
        
        // 刷新缓存
        if ($update->id > 0) {
            $this->refreshCache($update->id);
        }
    }
    
    
    /**
     * 删除配置
     * @param int $id 信息ID
     * @throws VerifyException
     * @throws SQLException
     */
    public function del($id)
    {
        $info = $this->getInfo($id);
        if ($info['is_system']) {
            throw new VerifyException('禁止删除系统配置');
        }
        
        if (false === $result = $this->deleteData($id)) {
            throw new SQLException('配置删除失败', $this);
        }
        
        // 刷新缓存
        $this->refreshCache($info['type'], true);
    }
    
    
    /**
     * 设置键值数据
     * @param string $key 数据名称
     * @param mixed  $value 数据值
     * @throws VerifyException
     * @throws SQLException
     */
    public function setKey($key, $value)
    {
        $key = trim($key);
        if (!$key) {
            throw new VerifyException('配置键不能为空', 'key');
        }
        
        $where       = SystemConfigField::init();
        $where->type = $key;
        $update      = SystemConfigField::init();
        $update->setContent($value);
        $this->whereof($where)->updateData($update);
        
        // 刷新缓存
        $this->refreshCache($key, true);
    }
    
    
    /**
     * 获取键值数据
     * @param string $key 数据名称
     * @param bool   $must 强制更新缓存
     * @return array
     */
    public function get($key, $must = false)
    {
        $cache = $this->getCache($key);
        if (!$cache || $must) {
            $where       = SystemConfigField::init();
            $where->type = $key;
            $info        = $this->whereof($where)->findData();
            if (!$info) {
                $this->deleteCache($key);
                
                Log::record('SystemConfig配置不存在[' . $key . ']', 'error');
                
                return null;
            }
            
            $cache = self::parseInfo($info);
            $this->setCache($key, $cache);
        }
        
        return $cache['content'];
    }
    
    
    /**
     * 刷新缓存
     * @param int  $id
     * @param bool $idIsType
     */
    public function refreshCache($id = 0, $idIsType = false)
    {
        // 刷新全部缓存
        if (is_numeric($id) && $id < 1) {
            $list = $this->selecting();
            foreach ($list as $i => $r) {
                $this->get($r['type'], true);
            }
            
            return;
        }
        
        // 传入的参数就是type
        if ($idIsType) {
            $this->get($id, true);
            
            return;
        }
        
        
        try {
            $info = $this->getInfo($id);
            $this->get($info['type'], true);
        } catch (SQLException $e) {
        }
    }
    
    
    /**
     * 解析数据
     * @param array $list
     * @return array
     */
    public static function parseList($list)
    {
        foreach ($list as $i => $r) {
            $r['is_system'] = intval($r['is_system']) > 0;
            $r['is_append'] = intval($r['is_append']) > 0;
            $r['content']   = unserialize($r['content']);
            $list[$i]       = $r;
        }
        
        return parent::parseList($list);
    }
}