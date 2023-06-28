<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\message;

use BusyPHP\helper\TransHelper;
use BusyPHP\interfaces\ModelValidateInterface;
use BusyPHP\Model;
use BusyPHP\model\annotation\field\AutoTimestamp;
use BusyPHP\model\annotation\field\Column;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\Json;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\Validator;
use BusyPHP\model\annotation\field\ValueBindField;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use think\Validate;
use think\validate\ValidateRule;

/**
 * 后台消息模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午2:30 AdminMessageField.php $
 * @method static Entity id(mixed $op = null, mixed $condition = null) id
 * @method static Entity userId(mixed $op = null, mixed $condition = null) 用户ID
 * @method static Entity userType(mixed $op = null, mixed $condition = null) 用户类型
 * @method static Entity type(mixed $op = null, mixed $condition = null) 消息类型
 * @method static Entity business(mixed $op = null, mixed $condition = null) 业务参数
 * @method static Entity createTime(mixed $op = null, mixed $condition = null) 创建时间
 * @method static Entity read(mixed $op = null, mixed $condition = null) 是否已读
 * @method static Entity readTime(mixed $op = null, mixed $condition = null) 阅读时间
 * @method static Entity content(mixed $op = null, mixed $condition = null) 消息内容
 * @method static Entity subject(mixed $op = null, mixed $condition = null) 消息描述
 * @method $this setId(mixed $id, bool|ValidateRule[] $validate = false) 设置id
 * @method $this setUserId(mixed $userId, bool|ValidateRule[] $validate = false) 设置用户ID
 * @method $this setUserType(mixed $userType, bool|ValidateRule[] $validate = false) 设置用户类型
 * @method $this setType(mixed $type, bool|ValidateRule[] $validate = false) 设置消息类型
 * @method $this setBusiness(mixed $type, bool|ValidateRule[] $validate = false) 设置业务参数
 * @method $this setCreateTime(mixed $createTime, bool|ValidateRule[] $validate = false) 设置创建时间
 * @method $this setRead(mixed $read, bool|ValidateRule[] $validate = false) 设置是否已读
 * @method $this setReadTime(mixed $readTime, bool|ValidateRule[] $validate = false) 设置阅读时间
 * @method $this setContent(mixed $content, bool|ValidateRule[] $validate = false) 设置消息内容
 * @method $this setSubject(mixed $subject, bool|ValidateRule[] $validate = false) 设置消息描述
 */
#[AutoTimestamp(type: AutoTimestamp::TYPE_INT), ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
class AdminMessageField extends Field implements ModelValidateInterface
{
    /**
     * id
     * @var int
     */
    public $id;
    
    /**
     * 用户ID
     * @var int
     */
    #[Validator(name: Validator::REQUIRE, msg: '必须指定消息接收者')]
    #[Validator(name: Validator::GT, rule: 0, msg: '必须指定消息接收者')]
    public $userId;
    
    /**
     * 用户类型
     * @var int
     */
    #[Validator(name: Validator::REQUIRE)]
    #[Validator(name: Validator::GT)]
    public $userType;
    
    /**
     * 消息类型
     * @var int
     */
    public $type;
    
    /**
     * 业务参数
     * @var string
     */
    public $business;
    
    /**
     * 创建时间
     * @var int
     */
    #[Column(feature: Column::FEATURE_CREATE_TIME)]
    public $createTime;
    
    /**
     * 是否已读
     * @var bool
     */
    public $read;
    
    /**
     * 阅读时间
     * @var int
     */
    public $readTime;
    
    /**
     * 消息内容
     * @var AdminMessageContent
     */
    #[Validator(name: Validator::REQUIRE)]
    #[Json]
    public $content;
    
    /**
     * 消息描述
     * @var string
     */
    #[Filter('trim')]
    public $subject;
    
    /**
     * 类型名称
     * @var string|null
     */
    #[Ignore]
    #[ValueBindField([self::class, 'type'])]
    #[Filter([AdminMessage::class, 'getTypeNameMap'])]
    public $typeName;
    
    /**
     * 类型配置
     * @var array{name:string}|null
     */
    #[Ignore]
    #[ValueBindField([self::class, 'type'])]
    #[Filter([AdminMessage::class, 'getTypeMap'])]
    public $typeInfo;
    
    /**
     * 格式化的创建时间
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'createTime'])]
    #[Filter([TransHelper::class, 'date'])]
    public $formatCreateTime;
    
    /**
     * 格式化的阅读时间
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'readTime'])]
    #[Filter([TransHelper::class, 'date'])]
    public $formatReadTime;
    
    
    protected function onParseAfter()
    {
        $this->content = AdminMessageContent::parse($this->content ?: [])->parseAdminOperate($this);
        $this->subject = $this->subject ?: $this->content->title ?: $this->content->desc;
    }
    
    
    /**
     * @inheritDoc
     * @param AdminMessage $model
     */
    public function onModelValidate(Model $model, Validate $validate, string $scene, $data = null)
    {
        $validate->append($this::content(), ValidateRule::init()->closure(function() {
            if (!$this->content instanceof AdminMessageContent) {
                return sprintf('%s field must be an a object "%s"', $this::content(), AdminMessageContent::class);
            }
            
            return true;
        }));
        
        if ($scene == $model::SCENE_CREATE) {
            $this->retain($validate, [
                $this::userId(),
                $this::userType(),
                $this::type(),
                $this::business(),
                $this::content(),
                $this::subject()
            ]);
            
            return true;
        }
        
        return false;
    }
}