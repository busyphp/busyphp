<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * 值绑定注解类，属性的值通过该属性所在的 {@see Field} 字段自动补充
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/13 09:12 BindField.php $
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ValueBindField
{
    /**
     * @var callable|string
     */
    private mixed $field;
    
    /**
     * @var string
     */
    private string $real;
    
    
    public function __construct(callable|string $field)
    {
        $this->field = $field;
    }
    
    
    /**
     * @return callable|string
     */
    public function getField() : mixed
    {
        if (!isset($this->real)) {
            if ($obj = Entity::tryCallable($this->field)) {
                $this->real = (string) $obj;
            } else {
                $this->real = $this->field;
            }
        }
        
        return $this->real;
    }
}