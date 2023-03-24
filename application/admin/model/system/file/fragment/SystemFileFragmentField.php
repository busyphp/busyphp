<?php

namespace BusyPHP\app\admin\model\system\file\fragment;

use BusyPHP\helper\TransHelper;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\ValueBindField;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * SystemFileFragmentField
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/8 9:15 PM SystemFileFragmentField.php $
 * @method static Entity id(mixed $op = null, mixed $condition = null) ID
 * @method static Entity userId(mixed $op = null, mixed $condition = null) 用户ID
 * @method static Entity fileId(mixed $op = null, mixed $condition = null) 附件ID
 * @method static Entity path(mixed $op = null, mixed $condition = null) 碎片名称
 * @method static Entity createTime(mixed $op = null, mixed $condition = null) 创建时间
 * @method static Entity number(mixed $op = null, mixed $condition = null) 分块数
 * @method static Entity size(mixed $op = null, mixed $condition = null) 碎片大小
 * @method static Entity merging(mixed $op = null, mixed $condition = null) 是否正在合并中
 * @method $this setId(mixed $id) 设置ID
 * @method $this setUserId(mixed $userId) 设置用户ID
 * @method $this setFileId(mixed $fileId) 设置附件ID
 * @method $this setPath(mixed $path) 设置碎片名称
 * @method $this setCreateTime(mixed $createTime) 设置创建时间
 * @method $this setNumber(mixed $number) 设置分块数
 * @method $this setSize(mixed $size) 设置碎片大小
 * @method $this setMerging(mixed $merging) 设置是否正在合并中
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
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
     * @var bool
     */
    public $merging;
    
    #[Ignore]
    #[ValueBindField([self::class, 'createTime'])]
    #[Filter([TransHelper::class, 'date'])]
    public $formatCreateTime;
}