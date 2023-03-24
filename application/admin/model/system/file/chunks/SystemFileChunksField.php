<?php

namespace BusyPHP\app\admin\model\system\file\chunks;

use BusyPHP\helper\TransHelper;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\ValueBindField;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * SystemFileChunksField
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/8 9:18 PM SystemFileChunksField.php $
 * @method static Entity id(mixed $op = null, mixed $condition = null) ID MD5[碎片ID+分块序号]
 * @method static Entity fragmentId(mixed $op = null, mixed $condition = null) 碎片ID
 * @method static Entity number(mixed $op = null, mixed $condition = null) 分块序号
 * @method static Entity path(mixed $op = null, mixed $condition = null) 存储路径
 * @method static Entity createTime(mixed $op = null, mixed $condition = null) 上传时间
 * @method static Entity size(mixed $op = null, mixed $condition = null) 块大小
 * @method $this setId(mixed $id) 设置ID MD5[碎片ID+分块序号]
 * @method $this setFragmentId(mixed $fragmentId) 设置碎片ID
 * @method $this setNumber(mixed $number) 设置分块序号
 * @method $this setPath(mixed $path) 设置存储路径
 * @method $this setCreateTime(mixed $createTime) 设置上传时间
 * @method $this setSize(mixed $size) 设置块大小
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
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
    
    /**
     * 格式化的创建时间
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'createTime'])]
    #[Filter([TransHelper::class, 'date'])]
    public $formatCreateTime;
    
    /**
     * 目录名称
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'fragmentId'])]
    #[Filter([SystemFileChunks::class, 'buildDir'])]
    public $dirname;
    
    /**
     * 文件名称
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'number'])]
    #[Filter([SystemFileChunks::class, 'buildName'])]
    public $basename;
    
    /**
     * 文件路径
     * @var string
     */
    #[Ignore]
    public $path;
    
    
    protected function onParseAfter() : void
    {
        $this->path = $this->dirname . '/' . $this->basename;
    }
}