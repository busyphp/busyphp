<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file\classes;

use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\interfaces\FieldObtainDataInterface;
use BusyPHP\interfaces\ModelSceneValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use BusyPHP\app\admin\model\system\file\SystemFile;
use think\Validate;
use think\validate\ValidateRule;

/**
 * 附件分类模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午4:32 SystemFileClassField.php $
 * @method static Entity id(mixed $op = null, mixed $condition = null) ID
 * @method static Entity name(mixed $op = null, mixed $condition = null) 分类名称
 * @method static Entity var(mixed $op = null, mixed $condition = null) 分类标识
 * @method static Entity type(mixed $op = null, mixed $condition = null) 附件类型
 * @method static Entity sort(mixed $op = null, mixed $condition = null) 自定义排序
 * @method static Entity extensions(mixed $op = null, mixed $condition = null) 限制文件格式
 * @method static Entity maxSize(mixed $op = null, mixed $condition = null) 限制文件大小
 * @method static Entity mimetype(mixed $op = null, mixed $condition = null) 限制文件mimetype
 * @method static Entity style(mixed $op = null, mixed $condition = null) 样式
 * @method static Entity system(mixed $op = null, mixed $condition = null) 是否系统类型
 * @method $this setId(mixed $id) 设置ID
 * @method $this setName(mixed $name) 设置分类名称
 * @method $this setVar(mixed $var) 设置分类标识
 * @method $this setType(mixed $type) 设置附件类型
 * @method $this setSort(mixed $sort) 设置自定义排序
 * @method $this setExtensions(mixed $extensions) 设置限制文件格式
 * @method $this setMaxSize(mixed $maxSize) 设置限制文件大小
 * @method $this setMimetype(mixed $mimetype) 设置限制文件mimetype
 * @method $this setStyle(mixed $style) 设置样式
 * @method $this setSystem(mixed $system) 设置是否系统类型
 */
class SystemFileClassField extends Field implements ModelSceneValidateInterface, FieldObtainDataInterface
{
    /**
     * ID
     * @var int
     * @busy-validate require
     * @busy-validate gt:0
     */
    public $id;
    
    /**
     * 分类名称
     * @var string
     * @busy-validate require#请输入:attribute
     * @busy-filter trim
     */
    public $name;
    
    /**
     * 分类标识
     * @var string
     * @busy-validate require#请输入:attribute
     * @busy-filter trim
     */
    public $var;
    
    /**
     * 文件类型
     * @var string
     * @busy-validate require#请选择:attribute
     */
    public $type;
    
    /**
     * 自定义排序
     * @var int
     */
    public $sort;
    
    /**
     * 上传格式限制
     * @var string
     * @busy-filter trim
     */
    public $extensions;
    
    /**
     * 上传大小限制
     * @var int
     * @busy-validate egt:0
     */
    public $maxSize;
    
    /**
     * Mimetype限制
     * @var string
     * @busy-filter trim
     */
    public $mimetype;
    
    /**
     * 样式
     * @var array
     * @busy-array json
     */
    public $style;
    
    /**
     * 是否系统类型
     * @var bool
     */
    public $system;
    
    
    /**
     * @inheritDoc
     */
    public function onModelSceneValidate(Model $model, Validate $validate, string $name, $data = null)
    {
        $validate
            ->append(
                $this::var(),
                ValidateRule::regex('/^[a-zA-Z]+[a-zA-Z0-9_]*$/', ':attribute必须是英文数字下划线组合，且必须是英文开头')->unique($model)
            )
            ->append($this::type(), ValidateRule::in(array_keys(SystemFile::getClass()::getTypes()), '请选择有效的:attribute'));
        
        if ($data instanceof SystemFileClassInfo && $data->system) {
            $this->setSystem(true);
        }
        
        if ($name == SystemFileClass::SCENE_CREATE) {
            $this->setId(0);
            $this->retain($validate, [
                $this::name(),
                $this::var(),
                $this::type(),
                $this::system()
            ]);
            
            return true;
        } elseif ($name == SystemFileClass::SCENE_UPDATE) {
            $this->retain($validate, [
                $this::id(),
                $this::name(),
                $this::var(),
                $this::type(),
                $this::system()
            ]);
            
            return true;
        } elseif ($name == SystemFileClass::SCENE_USER_SET) {
            $this->retain($validate, [
                $this::id(),
                $this::extensions(),
                $this::maxSize(),
                $this::mimetype(),
                $this::style()
            ]);
            
            return true;
        }
        
        return false;
    }
    
    
    /**
     * @inheritDoc
     */
    public function onObtainData(string $field, string $property, array $attrs, $value)
    {
        if ($field == $this::extensions() || $field == $this::mimetype()) {
            return StorageSetting::parseExtensions($value);
        }
        
        return $value;
    }
}