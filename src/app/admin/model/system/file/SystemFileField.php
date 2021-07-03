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
 * @method static Entity url($op = null, $value = null) 文件名
 * @method static Entity urlHash($op = null, $value = null) URL HASH
 * @method static Entity size($op = null, $value = null) 文件大小（bytes）
 * @method static Entity mimeType($op = null, $value = null)
 * @method static Entity extension($op = null, $value = null) 文件类型
 * @method static Entity name($op = null, $value = null) 文件原名
 * @method static Entity markType($op = null, $value = null) 标记类型
 * @method static Entity markValue($op = null, $value = null) 标识值
 * @method static Entity hash($op = null, $value = null) 文件的哈希验证字符串
 * @method static Entity userid($op = null, $value = null) 会员ID
 * @method static Entity isAdmin($op = null, $value = null) 后台上传
 * @method static Entity classify($op = null, $value = null) 文件分类
 * @method static Entity isThumb($op = null, $value = null) 是否缩放资源
 * @method static Entity thumbId($op = null, $value = null) 缩放资源源文件ID
 * @method static Entity thumbWidth($op = null, $value = null) 缩放宽度
 * @method static Entity thumbHeight($op = null, $value = null) 缩放资源高度
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
     * 文件名
     * @var string
     */
    public $url;
    
    /**
     * URL HASH
     * @var string
     */
    public $urlHash;
    
    /**
     * 文件大小（bytes）
     * @var int
     */
    public $size;
    
    /**
     * @var string
     */
    public $mimeType;
    
    /**
     * 文件类型
     * @var string
     */
    public $extension;
    
    /**
     * 文件原名
     * @var string
     */
    public $name;
    
    /**
     * 标记类型
     * @var string
     */
    public $markType;
    
    /**
     * 标识值
     * @var string
     */
    public $markValue;
    
    /**
     * 文件的哈希验证字符串
     * @var string
     */
    public $hash;
    
    /**
     * 会员ID
     * @var int
     */
    public $userid;
    
    /**
     * 后台上传
     * @var int
     */
    public $isAdmin;
    
    /**
     * 文件分类
     * @var string
     */
    public $classify;
    
    /**
     * 是否缩放资源
     * @var int
     */
    public $isThumb;
    
    /**
     * 缩放资源源文件ID
     * @var int
     */
    public $thumbId;
    
    /**
     * 缩放宽度
     * @var int
     */
    public $thumbWidth;
    
    /**
     * 缩放资源高度
     * @var int
     */
    public $thumbHeight;
}