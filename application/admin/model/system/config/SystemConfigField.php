<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\config;

use BusyPHP\interfaces\ModelSceneValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use think\Validate;
use think\validate\ValidateRule;

/**
 * 系统键值对配置数据模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午3:06 SystemConfigField.php $
 * @method static Entity id(mixed $op = null, mixed $condition = null) ID
 * @method static Entity content(mixed $op = null, mixed $condition = null) content
 * @method static Entity name(mixed $op = null, mixed $condition = null) 备注
 * @method static Entity type(mixed $op = null, mixed $condition = null) 类型
 * @method static Entity system(mixed $op = null, mixed $condition = null) 系统配置
 * @method static Entity append(mixed $op = null, mixed $condition = null) 是否加入全局配置
 * @method $this setId(mixed $id) 设置ID
 * @method $this setContent(mixed $content) 设置content
 * @method $this setName(mixed $name) 设置备注
 * @method $this setType(mixed $type) 设置类型
 * @method $this setSystem(mixed $system) 设置系统配置
 * @method $this setAppend(mixed $append) 设置是否加入全局配置
 */
class SystemConfigField extends Field implements ModelSceneValidateInterface
{
    /**
     * ID
     * @var int
     */
    public $id;
    
    /**
     * content
     * @var array
     * @busy-array serialize
     */
    public $content;
    
    /**
     * 配置名称
     * @var string
     * @busy-validate require#请输入:attribute
     * @busy-filter trim
     */
    public $name;
    
    /**
     * 配置标识
     * @var string
     * @busy-validate require#请输入:attribute
     * @busy-filter trim
     */
    public $type;
    
    /**
     * 系统配置
     * @var bool
     */
    public $system;
    
    /**
     * 是否加入全局配置
     * @var bool
     */
    public $append;
    
    
    /**
     * @inheritDoc
     * @param null|SystemConfigInfo $data
     */
    public function onModelSceneValidate(Model $model, Validate $validate, string $name, $data = null)
    {
        $validate->append(
            $this::type(),
            ValidateRule::regex('/^[a-zA-Z]+[a-zA-Z0-9_]*$/', ':attribute必须是英文数字下划线组合，且必须是英文开头')->unique($model)
        );
        
        if ($data instanceof SystemConfigInfo && $data->system) {
            $this->setSystem(true);
        }
        
        if ($name == SystemConfig::SCENE_CREATE) {
            $this->setId(0);
            $this->setContent([]);
            $this->retain($validate, [
                $this::name(),
                $this::type(),
                $this::content(),
                $this::system(),
                $this::append(),
            ]);
            
            return true;
        } elseif ($name == SystemConfig::SCENE_UPDATE) {
            if (!$this->append) {
                $this->setAppend(false);
            }
            
            $this->retain($validate, [
                $this::id(),
                $this::name(),
                $this::type(),
                $this::content(),
                $this::system(),
                $this::append()
            ]);
            
            return true;
        }
        
        return false;
    }
}