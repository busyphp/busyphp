<?php

namespace BusyPHP\model;

use BusyPHP\helper\StringHelper;
use think\db\Raw;


/**
 * 模型字段实体
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午上午10:55 Entity.php $
 */
class Entity
{
    /**
     * 字段
     * @var string
     */
    private $field;
    
    /**
     * 别名
     * @var string
     */
    private $alias;
    
    /**
     * 条件
     * @var string
     */
    private $op;
    
    /**
     * 值
     * @var string
     */
    private $value;
    
    /**
     * 自定义表达式
     * @var string
     */
    private $exp;
    
    /**
     * value输出为Raw对象
     * @var bool
     */
    private $valueToRaw = false;
    
    /**
     * 字段别名
     * @var string
     */
    private $as;
    
    
    /**
     * 设置表达式
     * @param string $exp
     * @return Entity
     */
    public function setExp(string $exp) : self
    {
        $this->exp = $exp;
        
        return $this;
    }
    
    
    /**
     * 设置别名
     * @param string $alias
     * @return Entity
     */
    public function setAlias(string $alias) : self
    {
        $this->alias = $alias;
        
        return $this;
    }
    
    
    /**
     * 设置字段名称
     * @param string $field
     * @return Entity
     */
    public function setField(string $field) : self
    {
        $this->field = $field;
        
        return $this;
    }
    
    
    /**
     * 设置条件
     * @param string $op
     * @return Entity
     */
    public function setOp(string $op) : self
    {
        $this->op = $op;
        
        return $this;
    }
    
    
    /**
     * 设置值
     * @param mixed $value
     * @return Entity
     */
    public function setValue($value) : self
    {
        if (is_bool($value)) {
            $this->value = $value ? 1 : 0;
        } else {
            $this->value = $value;
        }
        
        return $this;
    }
    
    
    /**
     * 设置value为Raw对象
     * @param bool $raw
     * @return Entity
     */
    public function setValueToRaw(bool $raw) : self
    {
        $this->valueToRaw = $raw;
        
        return $this;
    }
    
    
    /**
     * 设置字段别名
     * @param string $alias 别名
     * @return Entity
     */
    public function setAs(string $alias) : self
    {
        $this->as = $alias;
        
        return $this;
    }
    
    
    /**
     * 返回字段名称
     * @param bool $raw 是否返回真实的字段名称，不含别名
     * @param bool $camel 返回的字段是否转为驼峰首字母小写格式
     * @return string
     */
    public function field(bool $raw = false, bool $camel = false) : string
    {
        if ($raw) {
            return $camel ? StringHelper::camel($this->field) : $this->field;
        }
        
        return $this->__toString();
    }
    
    
    /**
     * 返回等式
     * @return string
     */
    public function op()
    {
        return $this->op;
    }
    
    
    /**
     * 返回值
     * @return mixed
     */
    public function value()
    {
        if ($this->valueToRaw) {
            return new Raw($this->value);
        }
        
        return $this->value;
    }
    
    
    public function __toString()
    {
        $field = $this->field;
        
        if ($this->alias) {
            $field = "{$this->alias}.{$field}";
        }
        
        if ($this->exp) {
            $field = "{$this->exp}({$field})";
        }
        
        if ($this->as) {
            return "{$field} AS {$this->as}";
        }
        
        return $field;
    }
    
    
    /**
     * 解析field
     * @param mixed $field
     * @return array|string
     */
    public static function parse($field)
    {
        if (is_array($field)) {
            foreach ($field as $key => $item) {
                if ($item instanceof Entity) {
                    $field[$key] = $item->field();
                }
            }
        } elseif ($field instanceof Entity) {
            $field = $field->field();
        }
        
        return $field;
    }
}