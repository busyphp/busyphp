<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\logs;

use BusyPHP\helper\AppHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\Json;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\ValueBindField;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * 系统操作日志字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午5:01 SystemLogsField.php $
 * @method static Entity id(mixed $op = null, mixed $condition = null) ID
 * @method static Entity createTime(mixed $op = null, mixed $condition = null) 操作时间
 * @method static Entity type(mixed $op = null, mixed $condition = null) 操作类型
 * @method static Entity level(mixed $op = null, mixed $condition = null) 日志级别
 * @method static Entity name(mixed $op = null, mixed $condition = null) 操作名称
 * @method static Entity userId(mixed $op = null, mixed $condition = null) 操作用户ID
 * @method static Entity username(mixed $op = null, mixed $condition = null) 操作用户名
 * @method static Entity classType(mixed $op = null, mixed $condition = null) 日志分类
 * @method static Entity classValue(mixed $op = null, mixed $condition = null) 日志分类业务参数
 * @method static Entity client(mixed $op = null, mixed $condition = null) 操作客户端
 * @method static Entity ip(mixed $op = null, mixed $condition = null) 客户端IP
 * @method static Entity method(mixed $op = null, mixed $condition = null) 请求方式
 * @method static Entity url(mixed $op = null, mixed $condition = null) 请求URL
 * @method static Entity params(mixed $op = null, mixed $condition = null) 请求参数
 * @method static Entity headers(mixed $op = null, mixed $condition = null) 请求头
 * @method static Entity result(mixed $op = null, mixed $condition = null) 操作结果
 * @method static Entity formatCreateTime();
 * @method static Entity typeName();
 * @method static Entity clientName();
 * @method $this setId(mixed $id) 设置ID
 * @method $this setCreateTime(mixed $createTime) 设置操作时间
 * @method $this setType(mixed $type) 设置操作类型
 * @method $this setLevel(mixed $level) 设置日志级别
 * @method $this setName(mixed $name) 设置操作名称
 * @method $this setUserId(mixed $userId) 设置操作用户ID
 * @method $this setUsername(mixed $username) 设置操作用户名
 * @method $this setClassType(mixed $classType) 设置日志分类
 * @method $this setClassValue(mixed $classValue) 设置日志分类业务参数
 * @method $this setClient(mixed $client) 设置操作客户端
 * @method $this setIp(mixed $ip) 设置客户端IP
 * @method $this setMethod(mixed $method) 设置请求方式
 * @method $this setUrl(mixed $url) 设置请求URL
 * @method $this setParams(mixed $params) 设置请求参数
 * @method $this setHeaders(mixed $headers) 设置请求头
 * @method $this setResult(mixed $result) 设置操作结果
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
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
     * 日志级别
     * @var int
     */
    public $level;
    
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
     * @var string
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
     * @var array
     */
    #[Json]
    public $params;
    
    /**
     * 请求头
     * @var array
     */
    #[Json]
    public $headers;
    
    /**
     * 操作结果
     * @var string
     */
    public $result;
    
    /**
     * 格式化的时间
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'createTime'])]
    #[Filter([TransHelper::class, 'date'])]
    public $formatCreateTime;
    
    /**
     * 操作类型名称
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'type'])]
    #[Filter([SystemLogs::class, 'getTypes'])]
    public $typeName;
    
    /**
     * 客户端名称
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'client'])]
    #[Filter([AppHelper::class, 'getName'])]
    public $clientName;
}