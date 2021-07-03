<?php


namespace BusyPHP\app\admin\model\system\logs;


use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * 系统操作日志字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午下午5:01 SystemLogsField.php $
 * @method static Entity id($op = null, $value = null) ID
 * @method static Entity createTime($op = null, $value = null) 记录时间
 * @method static Entity type($op = null, $value = null) 记录类型
 * @method static Entity title($op = null, $value = null) 操作描述
 * @method static Entity path($op = null, $value = null) 操作路径
 * @method static Entity userid($op = null, $value = null) 用户ID
 * @method static Entity username($op = null, $value = null) 操作用户名
 * @method static Entity isAdmin($op = null, $value = null) 是否后台操作 1是，0不是
 * @method static Entity appName($op = null, $value = null) APP名称
 * @method static Entity content($op = null, $value = null) 操作详情
 * @method static Entity ip($op = null, $value = null) 操作IP
 * @method static Entity ua($op = null, $value = null) UserAgent
 * @method static Entity url($op = null, $value = null) 操作URL
 */
class SystemLogsField extends Field
{
    /**
     * ID
     * @var int
     */
    public $id;
    
    /**
     * 记录时间
     * @var int
     */
    public $createTime;
    
    /**
     * 记录类型
     * @var int
     */
    public $type;
    
    /**
     * 操作描述
     * @var string
     */
    public $title;
    
    /**
     * 操作路径
     * @var string
     */
    public $path;
    
    /**
     * 用户ID
     * @var int
     */
    public $userid;
    
    /**
     * 操作用户名
     * @var string
     */
    public $username;
    
    /**
     * 是否后台操作 1是，0不是
     * @var int
     */
    public $isAdmin;
    
    /**
     * APP名称
     * @var string
     */
    public $appName;
    
    /**
     * 操作详情
     * @var string
     */
    public $content;
    
    /**
     * 操作IP
     * @var string
     */
    public $ip;
    
    /**
     * UserAgent
     * @var string
     */
    public $ua;
    
    /**
     * 操作URL
     * @var string
     */
    public $url;
}