<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\config;

use BusyPHP\App;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\helper\FileHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\interfaces\SettingInterface;
use BusyPHP\model;
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
 * @method SystemConfigField getInfo(int $id, string $notFoundMessage = null)
 * @method SystemConfigField|null findInfo(int $id = null)
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
     * 添加配置
     * @param SystemConfigField $data
     * @return int
     * @throws DbException
     */
    public function create(SystemConfigField $data) : int
    {
        return (int) $this->validate($data, static::SCENE_CREATE)->insert();
    }
    
    
    /**
     * 修改配置
     * @param SystemConfigField $data
     * @param string            $scene
     * @throws Throwable
     */
    public function modify(SystemConfigField $data, string $scene = self::SCENE_UPDATE)
    {
        $this->transaction(function() use ($data, $scene) {
            $info = $this->lock(true)->getInfo($data->id);
            $this->validate($data, $scene, $info)->update();
        });
    }
    
    
    /**
     * 删除配置
     * @param int $id
     * @return int
     * @throws Throwable
     */
    public function remove(int $id) : int
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
     * @inheritDoc
     * @throws DbException
     */
    public function setSettingData(string $name, array $data)
    {
        $key = trim($name);
        if (!$key) {
            throw new ParamInvalidException('name');
        }
        
        $saveData = SystemConfigField::init();
        $saveData->setContent($data);
        $this->where(SystemConfigField::type($key))->update($saveData);
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