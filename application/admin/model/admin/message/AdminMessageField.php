<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\message;

use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\Json;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\Validator;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * 后台消息模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午2:30 AdminMessageField.php $
 * @method static Entity id(mixed $op = null, mixed $condition = null) id
 * @method static Entity userId(mixed $op = null, mixed $condition = null) 管理员ID
 * @method static Entity createTime(mixed $op = null, mixed $condition = null) 创建时间
 * @method static Entity read(mixed $op = null, mixed $condition = null) 是否已读
 * @method static Entity readTime(mixed $op = null, mixed $condition = null) 阅读时间
 * @method static Entity content(mixed $op = null, mixed $condition = null) 消息内容
 * @method static Entity description(mixed $op = null, mixed $condition = null) 消息备注
 * @method static Entity url(mixed $op = null, mixed $condition = null) 操作链接
 * @method static Entity icon(mixed $op = null, mixed $condition = null) 图标
 * @method static Entity attrs(mixed $op = null, mixed $condition = null) 自定义标签属性
 * @method static Entity iconColor() 图标颜色
 * @method $this setId(mixed $id) 设置id
 * @method $this setUserId(mixed $userId) 设置管理员ID
 * @method $this setCreateTime(mixed $createTime) 设置创建时间
 * @method $this setRead(mixed $read) 设置是否已读
 * @method $this setReadTime(mixed $readTime) 设置阅读时间
 * @method $this setContent(mixed $content) 设置消息内容
 * @method $this setDescription(mixed $description) 设置消息备注
 * @method $this setUrl(mixed $url) 设置操作链接
 * @method $this setIcon(mixed $icon) 设置图标
 * @method $this setAttrs(mixed $attrs) 设置自定义标签属性
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
class AdminMessageField extends Field
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
     * 创建时间
     * @var int
     */
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
     * @var string
     */
    #[Validator(name: Validator::REQUIRE)]
    public $content;
    
    /**
     * 消息备注
     * @var string
     */
    public $description;
    
    /**
     * 操作链接
     * @var string
     */
    public $url;
    
    /**
     * 图标
     * @var array
     */
    #[Json]
    public $icon;
    
    /**
     * 自定义标签属性
     * @var array
     */
    #[Json]
    public $attrs;
    
    /**
     * 图标颜色
     * @var string
     */
    #[Ignore]
    public $iconColor;
    
    
    protected function onParseAfter()
    {
        $color = '';
        if (count($this->icon) > 1) {
            [$icon, $color] = $this->icon;
        } else {
            [$icon] = $this->icon;
        }
        
        $this->iconColor = $color;
        $this->icon      = $icon;
    }
}