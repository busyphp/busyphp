<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\notice\data;

use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\Field;

/**
 * 消息属性结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/6/26 22:22 Attr.php $
 * @method $this setName(string $name) 设置属性名称
 * @method $this setValue(string $value) 设置属性值
 * @method $this setColor(string $color) 设置属性颜色
 */
#[ToArrayFormat(type: ToArrayFormat::TYPE_SNAKE)]
class Attr extends Field
{
    /**
     * 属性名称
     * @var string
     */
    public $name = '';
    
    /**
     * 属性值
     * @var string
     */
    public $value = '';
    
    /**
     * 属性颜色
     * @var string
     */
    public $color = '';
    
    
    /**
     * 构建
     * @param string $name 属性名称
     * @param string $value 属性值
     * @param string $color 属性颜色
     * @return static
     */
    public static function build(string $name, string $value, string $color = '') : static
    {
        $obj = static::init();
        $obj->setName($name);
        $obj->setValue($value);
        $obj->setColor($color);
        
        return $obj;
    }
}