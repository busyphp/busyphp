<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\token;

use BusyPHP\helper\TransHelper;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\ValueBindField;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * 系统用户通行秘钥模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/5/30 13:49 SystemTokenField.php $
 * @method static Entity id(mixed $op = null, mixed $condition = null) id
 * @method static Entity userType(mixed $op = null, mixed $condition = null) 用户类型
 * @method static Entity userId(mixed $op = null, mixed $condition = null) 用户ID
 * @method static Entity token(mixed $op = null, mixed $condition = null) 密钥
 * @method static Entity type(mixed $op = null, mixed $condition = null) 登录类型
 * @method static Entity createTime(mixed $op = null, mixed $condition = null) 创建时间
 * @method static Entity loginTime(mixed $op = null, mixed $condition = null) 本次登录时间
 * @method static Entity loginIp(mixed $op = null, mixed $condition = null) 本次登录IP
 * @method static Entity lastTime(mixed $op = null, mixed $condition = null) 上次登录时间
 * @method static Entity lastIp(mixed $op = null, mixed $condition = null) 上次登录IP
 * @method static Entity loginTotal(mixed $op = null, mixed $condition = null) 登录次数
 * @method $this setId(mixed $id) 设置id
 * @method $this setUserType(mixed $userType) 设置用户类型
 * @method $this setUserId(mixed $userId) 设置用户ID
 * @method $this setToken(mixed $token) 设置密钥
 * @method $this setType(mixed $type) 设置登录类型
 * @method $this setCreateTime(mixed $createTime) 设置创建时间
 * @method $this setLoginTime(mixed $loginTime) 设置本次登录时间
 * @method $this setLoginIp(mixed $loginIp) 设置本次登录IP
 * @method $this setLastTime(mixed $lastTime) 设置上次登录时间
 * @method $this setLastIp(mixed $lastIp) 设置上次登录IP
 * @method $this setLoginTotal(mixed $loginTotal) 设置登录次数
 */
class SystemTokenField extends Field
{
    /**
     * id
     * @var string
     */
    public $id;
    
    /**
     * 用户类型
     * @var int
     */
    public $userType;
    
    /**
     * 用户ID
     * @var int
     */
    public $userId;
    
    /**
     * 密钥
     * @var string
     */
    public $token;
    
    /**
     * 登录类型
     * @var int
     */
    public $type;
    
    /**
     * 创建时间
     * @var int
     */
    public $createTime;
    
    /**
     * 本次登录时间
     * @var int
     */
    public $loginTime;
    
    /**
     * 本次登录IP
     * @var string
     */
    public $loginIp;
    
    /**
     * 上次登录时间
     * @var int
     */
    public $lastTime;
    
    /**
     * 上次登录IP
     * @var string
     */
    public $lastIp;
    
    /**
     * 登录次数
     * @var int
     */
    public $loginTotal;
    
    /**
     * 格式化的本次登录时间
     * @var string
     */
    #[Ignore]
    #[ValueBindField(field: [self::class, 'loginTime'])]
    #[Filter(filter: [TransHelper::class, 'date'])]
    public $formatLoginTime;
    
    /**
     * 格式化的创建时间
     * @var string
     */
    #[Ignore]
    #[ValueBindField(field: [self::class, 'createTime'])]
    #[Filter(filter: [TransHelper::class, 'date'])]
    public $formatCreateTime;
    
    /**
     * 格式化的最后登录时间
     * @var string
     */
    #[Ignore]
    #[ValueBindField(field: [self::class, 'lastTime'])]
    #[Filter(filter: [TransHelper::class, 'date'])]
    public $formatLastTime;
    
    /**
     * 登录类型名称
     * @var string
     */
    #[Ignore]
    #[ValueBindField(field: [self::class, 'type'])]
    #[Filter(filter: [SystemToken::class, 'getTypeMap'])]
    public $typeName;
    
    /**
     * 用户类型名称
     * @var string
     */
    #[Ignore]
    #[ValueBindField(field: [self::class, 'userType'])]
    #[Filter(filter: [SystemToken::class, 'getUserTypeMap'])]
    public $userTypeName;
}