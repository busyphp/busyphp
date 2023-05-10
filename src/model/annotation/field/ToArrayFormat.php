<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;
use BusyPHP\helper\StringHelper;
use BusyPHP\model\Field;

/**
 * 整体输出格式转换，用于 {@see Field::toArray()} 对键名格式控制
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/9 09:21 ToArrayFormat.php $
 * @see Field
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ToArrayFormat
{
    /** @var int 按属性名称原样输出 */
    public const TYPE_PROPERTY = 1;
    
    /** @var int 按字段名输出 */
    public const TYPE_FIELD = 2;
    
    /** @var int 驼峰转下划线输出 */
    public const TYPE_SNAKE = 4;
    
    /** @var int 下划线转驼峰输出 */
    public const TYPE_CAMEL = 8;
    
    private int $type;
    
    
    public function __construct(int $type = self::TYPE_PROPERTY)
    {
        $this->type = $type;
    }
    
    
    /**
     * 构造键名称
     * @param string $property 属性名称
     * @param string $field 字段名称
     * @return string
     */
    public function build(string $property, string $field = '') : string
    {
        $field = $field ?: $property;
        
        return match ($this->type) {
            self::TYPE_FIELD                                         => $field,
            self::TYPE_FIELD | self::TYPE_SNAKE                      => StringHelper::snake($field),
            self::TYPE_FIELD | self::TYPE_CAMEL                      => StringHelper::camel($field),
            self::TYPE_SNAKE, self::TYPE_PROPERTY | self::TYPE_SNAKE => StringHelper::snake($property),
            self::TYPE_CAMEL, self::TYPE_PROPERTY | self::TYPE_CAMEL => StringHelper::camel($property),
            default                                                  => $property,
        };
    }
}