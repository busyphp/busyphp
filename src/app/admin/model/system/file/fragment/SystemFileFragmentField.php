<?php

namespace BusyPHP\app\admin\model\system\file\fragment;

use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * SystemFileFragmentField
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/8 9:15 PM SystemFileFragmentField.php $
 * @method static Entity id($op = null, $value = null) ID
 * @method static Entity userId($op = null, $value = null) 用户ID
 * @method static Entity fileId($op = null, $value = null) 附件ID
 * @method static Entity path($op = null, $value = null) 碎片名称
 * @method static Entity createTime($op = null, $value = null) 创建时间
 * @method static Entity number($op = null, $value = null) 分块数
 * @method static Entity size($op = null, $value = null) 碎片大小
 * @method static Entity merging($op = null, $value = null) 是否正在合并中
 */
class SystemFileFragmentField extends Field
{
    /**
     * ID
     * @var int
     */
    public $id;
    
    /**
     * 用户ID
     * @var int
     */
    public $userId;
    
    /**
     * 附件ID
     * @var int
     */
    public $fileId;
    
    /**
     * 碎片名称
     * @var string
     */
    public $path;
    
    /**
     * 创建时间
     * @var int
     */
    public $createTime;
    
    /**
     * 分块数
     * @var int
     */
    public $number;
    
    /**
     * 碎片大小
     * @var int
     */
    public $size;
    
    /**
     * 是否正在合并中
     * @var int
     */
    public $merging;
}