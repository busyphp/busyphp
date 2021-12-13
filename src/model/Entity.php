<?php

namespace BusyPHP\model;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\helper\StringHelper;
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
    
    
    /**
     * 快速实例化
     * @param string $field
     * @return Entity
     */
    public static function init(string $field) : self
    {
        return new static($field);
    }
    
    
    /**
     * Entity constructor.
     * @param string $field
     */
    public function __construct(string $field)
    {
        $field = trim($field);
        if (!$field) {
            throw new ParamInvalidException('$field');
        }
        
        $this->field($field);
    }
    
    
    /**
     * 设置表达式, 支持 %s 变量为字段名称
     * @param string|null $exp <p>
     * string: 设置值<br />
     * null: 获取值
     * </p>
     * @return Entity|string
     */
    public function exp(?string $exp = null)
    {
        if (!$exp) {
            return $this->exp;
        }
        
        $this->exp = $exp;
        
        return $this;
    }
    
    
    /**
     * 设置/获取 数据表名
     * @param string|null $table <p>
     * string: 设置值<br />
     * null: 获取值
     * </p>
     * @return Entity|string
     */
    public function table(?string $table = null)
    {
        if (!$table) {
            return $this->table;
        }
        
        $this->table = $table;
        
        return $this;
    }
    
    
    /**
     * 设置/获取 查询条件/更新条件
     * @param string|null $op <p>
     * string: 设置值<br />
     * null: 获取值
     * </p>
     * @return Entity|string
     */
    public function op(?string $op = null)
    {
        if (!$op) {
            return $this->op;
        }
        
        $this->op = $op;
        
        return $this;
    }
    
    
    /**
     * 设置value转为{@see Raw}对象 或 获取value是否转为raw对象
     * @param bool|null $raw <p>
     * string: 设置值<br />
     * null: 获取值
     * </p>
     * @return Entity|bool
     */
    public function raw($raw = true) : self
    {
        if (is_null($raw)) {
            return $this->raw;
        }
        
        $this->raw = $raw;
        
        return $this;
    }
    
    
    /**
     * 设置/获取 字段别名
     * @param string|null $alias 别名 <p>
     * string: 设置值<br />
     * null: 获取值
     * </p>
     * @return Entity|string
     */
    public function as(?string $alias = null)
    {
        if (!$alias) {
            return $this->as;
        }
        
        $this->as = $alias;
        
        return $this;
    }
    
    
    /**
     * 设置/获取 字段名
     * @param string|true|false|null $field <p>
     * true: 返回下划线风格的字段名称<br />
     * false: 返回驼峰风格的字段名称<br />
     * string: 设置字段名<br />
     * null: 返回构建的查询字段
     * </p>
     * @return Entity|string
     */
    public function field($field = null)
    {
        if ($field && is_string($field)) {
            $this->field = StringHelper::snake($field);
            
            return $this;
        }
        
        // 返回下划线风格
        if (true === $field) {
            return $this->field;
        }
        
        //
        // 返回驼峰格式
        elseif (false === $field) {
            return StringHelper::camel($this->field);
        }
        
        // 返回查询字段名
        return $this->build();
    }
    
    
    /**
     * 设置/获取 查询值/更新值
     * @param mixed|null $value 获取值  <p>
     * mixed: 设置值<br />
     * null: 获取值
     * </p>
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
            $field = "{$this->table}.{$field}";
        }
        
        // 表达式
        if ($this->exp) {
            if (false !== strpos($this->exp, '%s')) {
                $field = sprintf($this->exp, $field);
            } else {
                $field = "{$this->exp}({$field})";
            }
        }
        
        // 别名
        if ($this->as) {
            return "{$field} AS {$this->as}";
        }
        
        return $field;
    }
    
    
    public function __toString()
    {
        return $this->build();
    }
}