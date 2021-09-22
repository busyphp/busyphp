<?php

namespace BusyPHP\app\admin\model\system\file;

use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * 文件管理模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/6 下午2:54 下午 SystemFileField.php $
 * @method static Entity id($op = null, $value = null) ID
 * @method static Entity createTime($op = null, $value = null) 上传时间
 * @method static Entity userId($op = null, $value = null) 会员ID
 * @method static Entity type($op = null, $value = null) 文件类型
 * @method static Entity classType($op = null, $value = null) 文件分类
 * @method static Entity classValue($op = null, $value = null) 文件分类对应的业务值
 * @method static Entity client($op = null, $value = null) 上传客户端
 * @method static Entity url($op = null, $value = null) 文件地址
 * @method static Entity urlHash($op = null, $value = null) URL HASH
 * @method static Entity path($op = null, $value = null) 文件路径
 * @method static Entity disk($op = null, $value = null) 磁盘名称
 * @method static Entity size($op = null, $value = null) 文件大小(字节)
 * @method static Entity mimeType($op = null, $value = null) 文件MimeType
 * @method static Entity extension($op = null, $value = null) 文件扩展名
 * @method static Entity name($op = null, $value = null) 文件名
 * @method static Entity hash($op = null, $value = null) 文件的哈希值
 * @method static Entity width($op = null, $value = null) 文件宽度(像素)
 * @method static Entity height($op = null, $value = null) 文件高度(像素)
 */
class SystemFileField extends Field
{
    /**
     * ID
     * @var int
     */
    public $id;
    
    /**
     * 上传时间
     * @var int
     */
    public $createTime;
    
    /**
     * 会员ID
     * @var int
     */
    public $userId;
    
    /**
     * 文件类型
     * @var string
     */
    public $type;
    
    /**
     * 文件分类
     * @var string
     */
    public $classType;
    
    /**
     * 文件分类对应的业务值
     * @var string
     */
    public $classValue;
    
    /**
     * 上传客户端
     * @var int
     */
    public $client;
    
    /**
     * 文件地址
     * @var string
     */
    public $url;
    
    /**
     * URL HASH
     * @var string
     */
    public $urlHash;
    
    /**
     * 文件路径
     * @var string
     */
    public $path;
    
    /**
     * 磁盘名称
     * @var string
     */
    public $disk;
    
    /**
     * 文件大小(字节)
     * @var int
     */
    public $size;
    
    /**
     * 文件MimeType
     * @var string
     */
    public $mimeType;
    
    /**
     * 文件扩展名
     * @var string
     */
    public $extension;
    
    /**
     * 文件名
     * @var string
     */
    public $name;
    
    /**
     * 文件的哈希值
     * @var string
     */
    public $hash;
    
    /**
     * 文件宽度(像素)
     * @var int
     */
    public $width;
    
    /**
     * 文件高度(像素)
     * @var int
     */
    public $height;
}