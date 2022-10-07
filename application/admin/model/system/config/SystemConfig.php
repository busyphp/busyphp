<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\config;

use BusyPHP\App;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\helper\FileHelper;
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
 * @method SystemConfigInfo getInfo(int $id, string $notFoundMessage = null)
 * @method SystemConfigInfo|null findInfo(int $id = null, string $notFoundMessage = null)
 * @method SystemConfigInfo[] selectList()
 * @method SystemConfigInfo[] buildListWithField(array $values, string|Entity $key = null, string|Entity $field = null)
 * @method SystemConfigInfo getInfoByType(string $type, string $notFoundMessage = null)
 * @method SystemConfigInfo|null findInfoByType(string $type, string $notFoundMessage = null)
 * @method static SystemConfig getClass()
 */
class SystemConfig extends Model
{
    protected $dataNotFoundMessage = '配置不存在';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = SystemConfigInfo::class;
    
    
    /**
     * @inheritDoc
     */
    final protected static function defineClass() : string
    {
        return self::class;
    }
    
    
    /**
     * 添加配置
     * @param SystemConfigField $data
     * @return int
     * @throws DbException
     */
    public function createInfo(SystemConfigField $data) : int
    {
        return (int) $this->validate($data, self::SCENE_CREATE)->addData();
    }
    
    
    /**
     * 修改配置
     * @param SystemConfigField $data
     * @throws ParamInvalidException
     * @throws Throwable
     */
    public function updateInfo(SystemConfigField $data)
    {
        $this->transaction(function() use ($data) {
            $info = $this->lock(true)->getInfo($data->id);
            $this->validate($data, self::SCENE_UPDATE, $info)->saveData();
        });
    }
    
    
    /**
     * 删除配置
     * @param int $data 信息ID
     * @return int
     * @throws Throwable
     */
    public function deleteInfo($data) : int
    {
        $id = (int) $data;
        
        return $this->transaction(function() use ($id) {
            $info = $this->lock(true)->getInfo($id);
            if ($info->system) {
                throw new RuntimeException('禁止删除系统配置');
            }
            
            return parent::deleteInfo($info->id);
        });
    }
    
    
    /**
     * 设置键值数据
     * @param string $key 数据名称
     * @param array  $value 数据值
     * @throws DbException
     * @throws ParamInvalidException
     */
    public function setKey($key, $value)
    {
        $key = trim($key);
        if (!$key) {
            throw new ParamInvalidException('key');
        }
        
        $data = SystemConfigField::init();
        $data->setContent($value);
        $this->whereEntity(SystemConfigField::type($key))->saveData($data);
    }
    
    
    /**
     * 获取键值数据
     * @param string $key 数据名称
     * @param bool   $must 强制更新缓存
     * @return mixed
     */
    public function get($key, $must = false)
    {
        $info = $this->getCache($key);
        if (!$info || $must) {
            try {
                $info = $this->getInfoByType($key);
            } catch (Throwable $e) {
                $this->deleteCache($key);
                
                throw new RuntimeException(sprintf('config "%s" does not exist', $key));
            }
            
            $this->setCache($key, $info);
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