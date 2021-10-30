<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\config;

use BusyPHP\App;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\FileHelper;
use BusyPHP\helper\LogHelper;
use BusyPHP\model;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 系统键值对配置数据模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
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
    public function insertData(SystemConfigField $insert)
    {
        $insert->content = '';
        $this->checkRepeat($insert);
        
        return $this->addData($insert);
    }
    
    
    /**
     * 修改配置
     * @param SystemConfigField $update
     * @throws ParamInvalidException
     * @throws Exception
     */
    public function updateData(SystemConfigField $update)
    {
        if ($update->id < 1) {
            throw new ParamInvalidException('id');
        }
        
        $this->startTrans();
        try {
            $info = $this->lock(true)->getInfo($update->id);
            if ($info->system) {
                $update->system = true;
                $update->type   = $info->type;
            }
            
            $this->checkRepeat($update, $update->id);
            $this->whereEntity(SystemConfigField::id($update->id))->saveData($update);
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            
            throw $e;
        }
    }
    
    
    /**
     * 查重
     * @param SystemConfigField $data
     * @param int               $id
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function checkRepeat(SystemConfigField $data, $id = 0)
    {
        $this->whereEntity(SystemConfigField::type($data->type));
        if ($id > 0) {
            $this->whereEntity(SystemConfigField::id('<>', $id));
        }
        if ($this->findInfo()) {
            throw new VerifyException('该配置标识已存在', 'type');
        }
    }
    
    
    /**
     * 删除配置
     * @param int $data 信息ID
     * @return int
     * @throws VerifyException
     * @throws Exception
     */
    public function deleteInfo($data) : int
    {
        $this->startTrans();
        try {
            $info = $this->lock(true)->getInfo($data);
            if ($info->system) {
                throw new VerifyException('禁止删除系统配置');
            }
            
            $res = parent::deleteInfo($info->id);
            $this->commit();
            
            try {
                $this->deleteCache($info->type);
            } catch (Exception $e) {
            }
            
            return $res;
        } catch (Exception $e) {
            $this->rollback();
            
            throw $e;
        }
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
                LogHelper::default()->error($e);
                $this->deleteCache($key);
                
                return null;
            }
            
            $this->setCache($key, $cache);
        }
        
        return $cache->content;
    }
    
    
    /**
     * @param string $method
     * @param mixed  $id
     * @param array  $options
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function onChanged(string $method, $id, array $options)
    {
        $this->updateCache();
    }
    
    
    /**
     * 刷新缓存
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function updateCache()
    {
        $config = [];
        foreach ($this->selectList() as $item) {
            $result = $this->get($item->type, true);
            if ($item->append && $result) {
                $config[$item->type] = $result;
            }
        }
        
        // 生成系统配置
        $string = var_export($config, true);
        FileHelper::write(App::init()
            ->getRuntimeConfigPath('config.php'), "<?php // 本配置由系统自动生成 \n\n return {$string};");
    }
}