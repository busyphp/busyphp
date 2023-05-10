<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\file\classes;

use BusyPHP\helper\StringHelper;
use BusyPHP\interfaces\FieldGetModelDataInterface;
use BusyPHP\interfaces\ModelValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\Json;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\Validator;
use BusyPHP\model\annotation\field\ValueBindField;
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
 * @method static Entity typeName() 类型名称
 * @method static Entity isFile() 是否附件
 * @method static Entity isImage() 是否图片
 * @method static Entity isVideo() 是否视频
 * @method static Entity isAudio() 是否音频
 * @method $this setId(mixed $id) 设置ID
 * @method $this setName(mixed $name) 设置分类名称
 * @method $this setVar(mixed $var) 设置分类标识
 * @method $this setType(mixed $type) 设置附件类型
 * @method $this setSort(mixed $sort) 设置自定义排序
 * @method $this setExtensions(mixed $extensions) 设置限制文件格式
 * @method $this setMaxSize(mixed $maxSize) 设置限制文件大小
 * @method $this setMimetype(mixed $mimetype) 设置限制文件mimetype
 * @method $this setStyle(mixed $style) 设置样式
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
class SystemFileClassField extends Field implements ModelValidateInterface, FieldGetModelDataInterface
{
    /**
     * ID
     * @var int
     */
    #[Validator(name: Validator::REQUIRE)]
    #[Validator(name: Validator::GT, rule: 0)]
    public $id;
    
    /**
     * 分类名称
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '请输入:attribute')]
    #[Filter(filter: 'trim')]
    public $name;
    
    /**
     * 分类标识
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '请输入:attribute')]
    #[Validator(name: Validator::NOT_IN, rule: SystemFileClass::PROTECT_VAR, msg: '该:attribute受系统保护')]
    #[Validator(name: Validator::IS_FIRST_ALPHA_NUM_DASH)]
    #[Validator(name: Validator::UNIQUE, rule: SystemFileClass::class)]
    #[Filter(filter: 'trim')]
    public $var;
    
    /**
     * 文件类型
     * @var string
     */
    #[Validator(name: Validator::REQUIRE, msg: '请选择:attribute')]
    public $type;
    
    /**
     * 自定义排序
     * @var int
     */
    public $sort;
    
    /**
     * 上传格式限制
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $extensions;
    
    /**
     * 上传大小限制
     * @var int
     */
    #[Validator(name: Validator::EGT, rule: 0)]
    public $maxSize;
    
    /**
     * Mimetype限制
     * @var string
     */
    #[Filter(filter: 'trim')]
    public $mimetype;
    
    /**
     * 样式
     * @var array
     */
    #[Json(default: '{}')]
    public $style;
    
    /**
     * 类型
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'type'])]
    #[Filter([SystemFile::class, 'getTypes'], 'name')]
    public $typeName;
    
    /**
     * 是否附件
     * @var bool
     */
    #[Ignore]
    public $isFile;
    
    /**
     * 是否图片
     * @var bool
     */
    #[Ignore]
    public $isImage;
    
    /**
     * 是否视频
     * @var bool
     */
    #[Ignore]
    public $isVideo;
    
    /**
     * 是否音频
     * @var bool
     */
    #[Ignore]
    public $isAudio;
    
    
    protected function onParseAfter()
    {
        $this->style   = $this->style ?: [];
        $this->isFile  = $this->type == SystemFile::FILE_TYPE_FILE;
        $this->isImage = $this->type == SystemFile::FILE_TYPE_IMAGE;
        $this->isVideo = $this->type == SystemFile::FILE_TYPE_VIDEO;
        $this->isAudio = $this->type == SystemFile::FILE_TYPE_AUDIO;
    }
    
    
    /**
     * @inheritDoc
     */
    public function onModelValidate(Model $model, Validate $validate, string $scene, $data = null)
    {
        $validate->append(
            $this::type(),
            ValidateRule::init()
                ->in(array_keys(SystemFile::class()::getTypes()), '请选择有效的:attribute')
        );
        
        if ($scene == SystemFileClass::SCENE_CREATE) {
            $this->exclude($validate, [
                $this::id()
            ]);
            
            return true;
        } elseif ($scene == SystemFileClass::SCENE_UPDATE) {
            $this->exclude($validate, [
                $this::var(),
            ]);
            
            return true;
        }
        
        return false;
    }
    
    
    /**
     * @inheritDoc
     */
    public function onGetModelData(string $field, string $property, array $attrs, $value)
    {
        if ($field == $this::extensions() || $field == $this::mimetype()) {
            return StringHelper::formatSplit((string) $value);
        }
        
        return $value;
    }
}