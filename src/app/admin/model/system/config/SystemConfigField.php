<?php

namespace BusyPHP\app\admin\model\system\config;

use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Str;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use BusyPHP\helper\util\Regex;
use BusyPHP\helper\util\Transform;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;


/**
 * 系统键值对配置数据模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午3:06 SystemConfigField.php $
 * @method static Entity id($op = null, $value = null) ID
 * @method static Entity content($op = null, $value = null)
 * @method static Entity name($op = null, $value = null) 备注
 * @method static Entity type($op = null, $value = null) 类型
 * @method static Entity isSystem($op = null, $value = null) 系统配置
 * @method static Entity isAppend($op = null, $value = null) 是否加入全局配置
 */
class SystemConfigField extends Field
{
    /**
     * ID
     * @var int
     */
    public $id;
    
    /**
     * @var string
     */
    public $content;
    
    /**
     * 备注
     * @var string
     */
    public $name;
    
    /**
     * 类型
     * @var string
     */
    public $type;
    
    /**
     * 系统配置
     * @var int
     */
    public $isSystem;
    
    /**
     * 是否加入全局配置
     * @var int
     */
    public $isAppend;
    
    
    /**
     * 设置
     * @param int $id
     * @return $this
     * @throws VerifyException
     */
    public function setId($id)
    {
        $this->id = floatval($id);
        if ($this->id < 1) {
            throw new VerifyException('缺少参数', 'id');
        }
        
        return $this;
    }
    
    
    /**
     * 设置
     * @param mixed $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = serialize($content);
        
        return $this;
    }
    
    
    /**
     * 设置备注
     * @param string $name
     * @return $this
     * @throws VerifyException
     */
    public function setName($name)
    {
        $this->name = trim($name);
        if (!$this->name) {
            throw new VerifyException('请输入配置名称', 'name');
        }
        
        return $this;
    }
    
    
    /**
     * 设置类型
     * @param string $type
     * @return $this
     * @throws VerifyException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function setType($type)
    {
        $this->type = trim($type);
        if (!$this->type) {
            throw new VerifyException('请输入配置标识', 'type');
        }
        if (!Regex::account($this->type)) {
            throw new VerifyException('配置标识格式有误，只能包含英文、数字、下划线', 'type');
        }
        
        // 只能是英文开头
        $this->type = Str::snake($this->type);
        if (!Regex::english(substr($this->type, 0, 1))) {
            throw new VerifyException('配置标识不能为数字或下划线开头', 'type');
        }
        
        // 查重
        $model = SystemConfig::init();
        $model->whereEntity(self::type($type));
        if ($this->id > 0) {
            $model->whereEntity(self::id('<>', $this->id));
        }
        if ($model->findInfo()) {
            throw new VerifyException('配置标识不能重复', 'type');
        }
        
        return $this;
    }
    
    
    /**
     * 设置系统配置
     * @param int $isSystem
     * @return $this
     */
    public function setIsSystem($isSystem)
    {
        $this->isSystem = Transform::dataToBool($isSystem);
        
        return $this;
    }
    
    
    /**
     * 设置是否加入全局配置
     * @param int $isAppend
     * @return $this
     */
    public function setIsAppend($isAppend)
    {
        $this->isAppend = Transform::dataToBool($isAppend);
        
        return $this;
    }
}