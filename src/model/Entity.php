<?php

namespace BusyPHP\model;

use Closure;
use think\db\Raw;

/**
 * 模型字段实体
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/25 下午上午10:55 Entity.php $
 */
class Entity
{
    /**
     * 服务注入
     * @var Closure[]
     */
    protected static $maker = [];
    
    /**
     * 类属性名称
     * @var string
     */
    private $name;
    
    /**
     * 字段名
     * @var string
     */
    private $field;
    
    /**
     * 查询值或更新值
     * @var string
     */
    private $value;
    
    /**
     * 数据表名
     * @var string
     */
    private $table = '';
    
    /**
     * 查询条件或更新条件
     * @var string
     */
    private $op = '';
    
    /**
     * 查询字段表达式
     * @var string
     */
    private $exp = '';
    
    /**
     * value输出为Raw对象
     * @var bool
     */
    private $raw = false;
    
    /**
     * 字段别名
     * @var string
     */
    private $as = '';
    
    
    /**
     * 设置服务注入
     * @param Closure $maker
     * @return void
     */
    public static function maker(Closure $maker)
    {
        static::$maker[] = $maker;
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
                    $field[$key] = $item->build();
                }
            }
        } elseif ($field instanceof Entity) {
            $field = $field->build();
        }
        
        return $field;
    }
    
    
    /**
     * Entity constructor.
     * @param string $name 类属性名称
     * @param string $field 字段名称
     */
    public function __construct(string $name, string $field)
    {
        $this->name  = $name;
        $this->field = $field;
        
        if (!empty(static::$maker)) {
            foreach (static::$maker as $maker) {
                call_user_func($maker, $this);
            }
        }
    }
    
    
    /**
     * 设置表达式, 支持 %s 变量为字段名称
     * @param string $exp 表达式，如：sum 或 sum(%s)
     * @return $this
     */
    public function exp(string $exp) : self
    {
        $this->exp = $exp;
        
        return $this;
    }
    
    
    /**
     * 设置数据表名
     * @param string $table
     * @return $this
     */
    public function table(string $table) : self
    {
        $this->table = $table;
        
        return $this;
    }
    
    
    /**
     * 设置value是否转为{@see Raw}对象
     * @return $this
     */
    public function raw($raw = true) : self
    {
        $this->raw = $raw;
        
        return $this;
    }
    
    
    /**
     * 设置字段别名
     * @param string $alias
     * @return $this
     */
    public function as(string $alias) : self
    {
        $this->as = $alias;
        
        return $this;
    }
    
    
    /**
     * 获取字段名
     * @return string
     */
    public function field() : string
    {
        return $this->field;
    }
    
    
    /**
     * 获取属性名
     * @return string
     */
    public function name() : string
    {
        return $this->name;
    }
    
    
    /**
     * 设置或获取等式
     * @param string|null $op 设为null则获取，否则设置
     * @return $this|string
     */
    public function op(string $op = null)
    {
        if (!$op) {
            return $this->op;
        }
        
        $this->op = $op;
        
        return $this;
    }
    
    
    /**
     * 设置或获取值
     * @param mixed|null $value 传null则获取，否则设置
     * @return Entity|Raw|string
     */
    public function value($value = null)
    {
        if (is_null($value)) {
            if ($this->raw && !$this->value instanceof Raw) {
                return new Raw($this->value);
            }
            
            return $this->value;
        }
        
        if (is_bool($value)) {
            $this->value = $value ? 1 : 0;
        } else {
            $this->value = $value;
        }
        
        return $this;
    }
    
    
    /**
     * 构建查询字段
     * @return string
     */
    public function build() : string
    {
        $field = $this->field;
        
        // 表名
        if ($this->table) {
            $field = $this->table . '.' . $field;
        }
        
        // 表达式
        if ($this->exp) {
            if (false !== strpos($this->exp, '%s')) {
                $field = sprintf($this->exp, $field);
            } else {
                $field = sprintf('%s(%s)', $this->exp, $field);
            }
        }
        
        // 别名
        if ($this->as) {
            return sprintf('%s AS %s', $field, $this->as);
        }
        
        return $field;
    }
    
    
    /**
     * 重置
     * @return $this
     */
    public function reset() : self
    {
        $this->exp   = '';
        $this->table = '';
        $this->as    = '';
        $this->op    = '';
        $this->raw   = false;
        $this->value = null;
    }
    
    
    public function __toString()
    {
        return $this->build();
    }
}