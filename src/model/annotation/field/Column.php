<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;

/**
 * 字段注解
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/18 21:14 Column.php $
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    // +----------------------------------------------------
    // + 字段类型
    // +----------------------------------------------------
    /** @var string 自动 */
    public const TYPE_DEFAULT = 'default';
    
    /** @var string 字符串 */
    public const TYPE_STRING = 'string';
    
    /** @var string 整数 */
    public const TYPE_INT = 'int';
    
    /** @var string 浮点 */
    public const TYPE_FLOAT = 'float';
    
    /** @var string 布尔 */
    public const TYPE_BOOL = 'bool';
    
    /** @var string 时间戳 */
    public const TYPE_TIMESTAMP = 'timestamp';
    
    /** @var string 年月日时分秒 */
    public const TYPE_DATETIME = 'datetime';
    
    /** @var string 年月日 */
    public const TYPE_DATE = 'date';
    
    // +----------------------------------------------------
    // + 特征
    // +----------------------------------------------------
    /** @var int 自动写入创建时间字段注解 */
    public const FEATURE_CREATE_TIME = 1;
    
    /** @var int 自动写入更新时间字段注解 */
    public const FEATURE_UPDATE_TIME = 2;
    
    /** @var int 定义软删除字段 */
    public const FEATURE_SOFT_DELETE = 3;
    
    private string $field;
    
    private bool   $primary;
    
    private string $type;
    
    private string $title;
    
    private bool   $readonly;
    
    private int    $feature;
    
    
    /**
     * 构造函数
     * @param string $field 真实字段名
     * @param bool   $primary 是否主键
     * @param string $type 真实字段类型
     * @param string $title 字段标题
     * @param bool   $readonly 是否只读字段
     * @param int    $feature 字段特征
     */
    public function __construct(string $field = '', bool $primary = false, string $type = self::TYPE_DEFAULT, string $title = '', bool $readonly = false, int $feature = 0)
    {
        $this->field    = $field;
        $this->primary  = $primary;
        $this->type     = $type;
        $this->title    = $title;
        $this->readonly = $readonly;
        $this->feature  = $feature;
    }
    
    
    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
    
    
    /**
     * @return string
     */
    public function getField() : string
    {
        return $this->field;
    }
    
    
    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }
    
    
    /**
     * @return bool
     */
    public function isPrimary() : bool
    {
        return $this->primary;
    }
    
    
    /**
     * @return bool
     */
    public function isReadonly() : bool
    {
        return $this->readonly;
    }
    
    
    /**
     * @return int
     */
    public function getFeature() : int
    {
        return $this->feature;
    }
}