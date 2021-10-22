<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\logs;

use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * 系统操作日志字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午5:01 SystemLogsField.php $
 * @method static Entity id($op = null, $value = null) ID
 * @method static Entity createTime($op = null, $value = null) 操作时间
 * @method static Entity type($op = null, $value = null) 操作类型
 * @method static Entity name($op = null, $value = null) 操作名称
 * @method static Entity userId($op = null, $value = null) 操作用户ID
 * @method static Entity username($op = null, $value = null) 操作用户名
 * @method static Entity classType($op = null, $value = null) 日志分类
 * @method static Entity classValue($op = null, $value = null) 日志分类业务参数
 * @method static Entity client($op = null, $value = null) 操作客户端
 * @method static Entity ip($op = null, $value = null) 客户端IP
 * @method static Entity method($op = null, $value = null) 请求方式
 * @method static Entity url($op = null, $value = null) 请求URL
 * @method static Entity params($op = null, $value = null) 请求参数
 * @method static Entity headers($op = null, $value = null) 请求头
 * @method static Entity result($op = null, $value = null) 操作结果
 */
class SystemLogsField extends Field
{
    /**
     * ID
     * @var int
     */
    public $id;
    
    /**
     * 操作时间
     * @var int
     */
    public $createTime;
    
    /**
     * 操作类型
     * @var int
     */
    public $type;
    
    /**
     * 操作名称
     * @var string
     */
    public $name;
    
    /**
     * 操作用户ID
     * @var int
     */
    public $userId;
    
    /**
     * 操作用户名
     * @var string
     */
    public $username;
    
    /**
     * 日志分类
     * @var int
     */
    public $classType;
    
    /**
     * 日志分类业务参数
     * @var string
     */
    public $classValue;
    
    /**
     * 操作客户端
     * @var string
     */
    public $client;
    
    /**
     * 客户端IP
     * @var string
     */
    public $ip;
    
    /**
     * 请求方式
     * @var string
     */
    public $method;
    
    /**
     * 请求URL
     * @var string
     */
    public $url;
    
    /**
     * 请求参数
     * @var mixed
     */
    public $params;
    
    /**
     * 请求头
     * @var mixed
     */
    public $headers;
    
    /**
     * 操作结果
     * @var string
     */
    public $result;
}