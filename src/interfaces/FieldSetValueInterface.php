<?php

namespace BusyPHP\interfaces;

use BusyPHP\model\Field;

/**
 * Field类 执行 {@see Field::setPropertyValue()} 时的处理接口
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/10/4 2:08 PM FieldSetValueInterface.php $
 */
interface FieldSetValueInterface
{
    /**
     * 执行 {@see Field::setPropertyValue()} 时处理属性值
     * @param string $field 真实字段名
     * @param string $property 类属性名称
     * @param array  $attrs 类属性的注释属性
     * @param mixed  $value 属性值
     * @return mixed 处理后的属性值
     */
    public function onSetValue(string $field, string $property, array $attrs, $value);
}