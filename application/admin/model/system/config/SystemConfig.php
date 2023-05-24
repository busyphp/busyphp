<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\config;

use BusyPHP\App;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\helper\FileHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\interfaces\SettingInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use RuntimeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use Throwable;

/**
 * 系统键值对配置数据模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午3:20 SystemConfig.php $
 * @method SystemConfigField getInfo(string $id, string $notFoundMessage = null)
 * @method SystemConfigField|null findInfo(string $id = null)
 * @method SystemConfigField[] selectList()
 * @method SystemConfigField[] indexList(string|Entity $key = '')
 * @method SystemConfigField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 * @method SystemConfigField getInfoByType(string $type, string $notFoundMessage = null)
 * @method SystemConfigField|null findInfoByType(string $type)
 */
class SystemConfig extends Model implements ContainerInterface, SettingInterface
{
    protected string $dataNotFoundMessage = '配置不存在';
    
    protected string $fieldClass          = SystemConfigField::class;
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 生成ID
     * @param string $type
     * @return string
     */
    public static function createId(string $type) : string
    {
        return md5($type);
    }
    
    
    /**
     * 设置配置
     * @param SystemConfigField $data
     * @return string
     * @throws DbException
     */
    public function create(SystemConfigField $data) : string
    {
        $this->validate($data, static::SCENE_CREATE);
        $data->setId(static::createId($data->type));
        $this->insert($data);
        
        return $data->id;
    }
    
    
    /**
     * 修改配置
     * @param SystemConfigField $data
     * @throws Throwable
     */
    public function modify(SystemConfigField $data)
    {
        $this->transaction(function() use ($data) {
            $info = $this->lock(true)->getInfo($data->id);
            $this->validate($data, static::SCENE_UPDATE, $info);
            
            // 如果更改了type则同时更改ID
            if (!$info->system && $data->type !== $info->type) {
                $data->setId(static::createId($data->type));
            }
            
            $this->where(SystemConfigField::id($info->id))->update($data);
        });
    }
    
    
    /**
     * 删除配置
     * @param string $id
     * @return int
     * @throws Throwable
     */
    public function remove(string $id) : int
    {
        return $this->transaction(function() use ($id) {
            $info = $this->lock(true)->getInfo($id);
            if ($info->system) {
                throw new RuntimeException('禁止删除系统配置');
            }
            
            return $this->delete($info->id);
        });
    }
    
    
    /**
     * 设置配置
     * @param string            $type
     * @param SystemConfigField $data
     * @return string
     * @throws Throwable
     */
    public function setting(string $type, SystemConfigField $data) : string
    {
        $type = trim($type);
        if (!$type) {
            throw new ParamInvalidException('$type');
        }
        $data->setType($type);
        
        return $this->transaction(function() use ($type, $data) {
            $id = static::createId($type);
            if (!$this->lock(true)->findInfo($id)) {
                if (!$data->name) {
                    $data->setName($type);
                }
                $this->create($data);
                
                return $id;
            }
            
            $this->where(SystemConfigField::id($id))->update($data->exclude(SystemConfigField::type()));
            
            return $id;
        });
    }
    
    
    /**
     * @inheritDoc
     * @param string $name
     * @param array  $data
     * @throws Throwable
     */
    public function setSettingData(string $name, array $data)
    {
        $setting = SystemConfigField::init();
        $setting->setContent($data);
        
        $this->setting($name, $setting);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getSettingData(string $name, bool $force = false) : array
    {
        $info = $this->getCache($name);
        if (!$info instanceof SystemConfigField || $force) {
            try {
                $info = $this->getInfoByType($name);
            } catch (Throwable $e) {
                $this->deleteCache($name);
                
                return [];
            }
            
            $this->setCache($name, $info);
        }
        
        return $info->content;
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
            $this->setCache($item->type, $item);
            if ($item->append) {
                $config[$item->type] = $item->content;
            }
        }
        
        // 生成系统配置
        FileHelper::write(App::getInstance()
            ->getRuntimeConfigPath('config.php'), sprintf("<?php\n// 本配置由系统自动生成与%s\nreturn %s;", date('Y-m-d H:i:s'), var_export($config, true)));
    }
}