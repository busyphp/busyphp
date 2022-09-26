<?php

namespace BusyPHP\app\admin\model\system\file\chunks;

use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * SystemFileChunksField
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/8 9:18 PM SystemFileChunksField.php $
 * @method static Entity id($op = null, $value = null) ID MD5(碎片ID+分块序号)
 * @method static Entity fragmentId($op = null, $value = null) 碎片ID
 * @method static Entity number($op = null, $value = null) 分块序号
 * @method static Entity createTime($op = null, $value = null) 上传时间
 * @method static Entity size($op = null, $value = null) 块大小
 */
class SystemFileChunksField extends Field
{
    /**
     * ID MD5(碎片ID+分块序号)
     * @var string
     */
    public $id;
    
    /**
     * 碎片ID
     * @var int
     */
    public $fragmentId;
    
    /**
     * 分块序号
     * @var int
     */
    public $number;
    
    /**
     * 上传时间
     * @var int
     */
    public $createTime;
    
    /**
     * 块大小
     * @var int
     */
    public $size;
}