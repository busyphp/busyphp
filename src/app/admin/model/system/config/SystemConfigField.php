<?php

namespace BusyPHP\app\admin\model\system\config;

use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Str;
use BusyPHP\model\Field;
use BusyPHP\helper\util\Regex;
use BusyPHP\helper\util\Transform;


/**
 * 系统键值对配置数据模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2018 busy^life <busy.life@qq.com>
 * @version $Id: 2018-01-18 上午10:11 SystemConfigField.php busy^life $
 */
class SystemConfigField extends Field
{
    /** @var int */
    public $id = null;
    
    /** @var string */
    public $content = null;
    
    /** @var string 备注 */
    public $name = null;
    
    /** @var string 类型 */
    public $type = null;
    
    /** @var int 是否系统配置 */
    public $isSystem = null;
    
    /** @var int 是否加入全局配置 */
    public $isAppend = null;
    
    
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
        $model       = SystemConfig::init();
        $where       = new self();
        $where->type = $this->type;
        if ($this->id > 0) {
            $where->id = array('neq', $this->id);
        }
        if ($model->whereof($where)->findData()) {
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