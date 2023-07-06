<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\notice\data;

use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\Field;
use stdClass;

/**
 * 后台操作方法结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/6/27 17:43 AdminOperate.php $
 * @method $this setType(int $type) 设置操作类型
 * @method $this setValue(string $value) 设置操作值
 * @method $this setAttrs(array $attrs) 设置操作属性
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
class AdminOperate extends Field
{
    /**
     * 操作类型
     * @var int
     */
    public $type = Operate::TYPE_NONE;
    
    /**
     * 操作值
     * @var string
     */
    public $value = '';
    
    /**
     * 操作属性
     * @var array
     */
    public $attrs = [];
    
    
    /**
     * 设置data-toggle操作
     * @param string $name toggle名称
     * @param array  $attrs toggle属性
     * @return $this
     */
    public function setToggleAttrs(string $name, array $attrs) : static
    {
        $this->attrs = array_merge($attrs, [
            'data-toggle' => $name
        ]);
        
        return $this;
    }
    
    
    /**
     * 设置模态框操作
     * @param array $attrs 属性
     * @return $this
     */
    public function setModalAttrs(array $attrs = []) : static
    {
        $this->attrs = array_merge($attrs, [
            'data-toggle' => 'busy-modal',
            'data-url'    => $this->value
        ]);
        
        return $this;
    }
    
    
    /**
     * 添加属性
     * @param string $name 属性名称
     * @param mixed  $value 属性值
     * @return $this
     */
    public function addAttr(string $name, mixed $value) : static
    {
        $this->attrs[$name] = $value;
        
        return $this;
    }
    
    
    public function toArray() : array
    {
        $this->attrs = $this->attrs ?: new stdClass();
        
        return parent::toArray();
    }
    
    
    /**
     * 构建
     * @param int    $type 操作类型
     * @param string $value 操作值
     * @return static
     */
    public static function build(int $type = Operate::TYPE_NONE, string $value = '') : static
    {
        $obj        = static::init();
        $obj->type  = $type;
        $obj->value = $value;
        
        return $obj;
    }
}