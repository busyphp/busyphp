<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\config;

use BusyPHP\interfaces\ModelValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\annotation\field\AutoTimestamp;
use BusyPHP\model\annotation\field\Column;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Json;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\Validator;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use think\Validate;

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
 * @method static Entity createTime(mixed $op = null, mixed $condition = null) 创建时间
 * @method static Entity updateTime(mixed $op = null, mixed $condition = null) 更新时间
 * @method $this setId(mixed $id) 设置ID
 * @method $this setContent(mixed $content) 设置content
 * @method $this setName(mixed $name) 设置备注
 * @method $this setType(mixed $type) 设置类型
 * @method $this setSystem(mixed $system) 设置系统配置
 * @method $this setAppend(mixed $append) 设置是否加入全局配置
 * @method $this setCreateTime(mixed $createTime) 设置创建时间
 * @method $this setUpdateTime(mixed $updateTime) 设置更新时间
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
#[AutoTimestamp(type: AutoTimestamp::TYPE_INT)]
class SystemConfigField extends Field implements ModelValidateInterface
{
    /**
     * ID
     * @var string
     */
    public $id;
    
    /**
     * content
     * @var array
     */
    #[Json]
    public $content;
    
    /**
     * 配置名称
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '请输入:attribute')]
    #[Filter(filter: 'trim')]
    public $name;
    
    /**
     * 配置标识
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '请输入:attribute')]
    #[Validator(name: Validator::IS_FIRST_ALPHA_NUM_DASH, msg: '请输入有效的:attribute')]
    #[Validator(name: Validator::UNIQUE, rule: SystemConfig::class, msg: '该配置标识已被使用')]
    #[Filter(filter: 'trim')]
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
     * @var int
     */
    #[Column(feature: Column::FEATURE_CREATE_TIME)]
    public $createTime;
    
    /**
     * @var int
     */
    #[Column(feature: Column::FEATURE_UPDATE_TIME)]
    public $updateTime;
    
    
    protected function onParseAfter()
    {
        $this->content = $this->content ?: [];
    }
    
    
    /**
     * @inheritDoc
     * @param SystemConfig      $model
     * @param SystemConfigField $data
     */
    public function onModelValidate(Model $model, Validate $validate, string $scene, $data = null)
    {
        if ($scene == $model::SCENE_CREATE) {
            $this->retain($validate, [
                $this::id(),
                $this::name(),
                $this::type(),
                $this::content(),
                $this::system(),
                $this::append(),
            ]);
            
            return true;
        } elseif ($scene == $model::SCENE_UPDATE) {
            if ($data->system) {
                $this->retain($validate, [
                    $this::id(),
                    $this::name(),
                    $this::content(),
                    $this::append()
                ]);
            } else {
                $this->retain($validate, [
                    $this::id(),
                    $this::name(),
                    $this::type(),
                    $this::content(),
                    $this::system(),
                    $this::append()
                ]);
            }
            
            return true;
        }
        
        return false;
    }
}