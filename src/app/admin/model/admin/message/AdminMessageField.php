<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\admin\message;

use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * 后台消息模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午2:30 AdminMessageField.php $
 * @method static Entity id($op = null, $value = null)
 * @method static Entity userId($op = null, $value = null) 管理员ID
 * @method static Entity createTime($op = null, $value = null) 创建时间
 * @method static Entity read($op = null, $value = null) 是否已读
 * @method static Entity readTime($op = null, $value = null) 阅读时间
 * @method static Entity content($op = null, $value = null) 消息内容
 * @method static Entity description($op = null, $value = null) 消息备注
 * @method static Entity url($op = null, $value = null) 操作链接
 * @method static Entity icon($op = null, $value = null) 图标
 */
class AdminMessageField extends Field
{
    /**
     * @var int
     */
    public $id;
    
    /**
     * 管理员ID
     * @var int
     */
    public $userId;
    
    /**
     * 创建时间
     * @var int
     */
    public $createTime;
    
    /**
     * 是否已读
     * @var int
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
     * @var string
     */
    public $icon;
}