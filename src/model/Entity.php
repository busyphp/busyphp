<?php
declare(strict_types = 1);

namespace BusyPHP\model;

use BusyPHP\helper\ReflectionNamedType;
use BusyPHP\helper\StringHelper;
use BusyPHP\model\annotation\field\Export;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Format;
use BusyPHP\model\annotation\field\Import;
use BusyPHP\model\annotation\field\Validator;
use ReflectionProperty;
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
     * 类属性名称
     * @var string
     */
    private string $name;
    
    /**
     * 字段名
     * @var string
     */
    private string $field;
    
    /**
     * 是否虚拟字段
     * @var bool
     */
    private bool $virtual;
    
    /**
     * 数据表别名
     * @var string
     */
    private string $alias;
    
    /**
     * 所属字段类
     * @var class-string<Field>
     */
    private string $fieldClass;
    
    /**
     * 值
     * @var mixed
     */
    private mixed $value = null;
    
    /**
     * 数据表名
     * @var string
     */
    private string $table = '';
    
    /**
     * 查询条件或更新条件
     * @var string
     */
    private string $op = '';
    
    /**
     * 查询字段表达式
     * @var string
     */
    private string $exp = '';
    
    /**
     * value输出为Raw对象
     * @var bool
     */
    private bool $raw = false;
    
    /**
     * 字段别名
     * @var string
     */
    private string $as = '';
    
    
    /**
     * Entity constructor.
     * @param string              $name 类属性名称
     * @param string              $field 字段名称
     * @param string              $alias 数据表别名
     * @param class-string<Field> $class 所属字段类
     * @param bool                $virtual 是否虚拟字段
     */
    public function __construct(string $name, string $field, string $alias, string $class, bool $virtual = false)
    {
        $this->name       = $name;
        $this->field      = $field;
        $this->virtual    = $virtual;
        $this->alias      = $alias;
        $this->fieldClass = $class;
    }
    
    
    /**
     * 输出属性名称
     * @param bool|string $table
     * @return string|Entity
     */
    public function __invoke(bool|string $table = false) : string|self
    {
        if ($table) {
            $this->table($table === true ? $this->alias : $table);
            
            return $this;
        }
        
        return $this->name;
    }
    
    
    /**
     * 设置是否虚拟字段
     * @param bool $virtual
     * @return $this
     */
    public function virtual(bool $virtual) : self
    {
        $this->virtual = $virtual;
        
        return $this;
    }
    
    
    /**
     * 获取是否虚拟字段
     * @return bool
     */
    public function isVirtual() : bool
    {
        return $this->virtual;
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
        return $this->build();
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
     * 设置等式
     * @param string $op
     * @return $this
     */
    public function op(string $op) : self
    {
        $this->op = $op;
        
        return $this;
    }
    
    
    /**
     * 获取等式
     * @return string
     */
    public function getOp() : string
    {
        return $this->op;
    }
    
    
    /**
     * 设置值
     * @param mixed $value
     * @return $this
     */
    public function value($value) : self
    {
        $this->value = $value;
        
        return $this;
    }
    
    
    /**
     * 获取值
     * @return mixed
     */
    public function getValue() : mixed
    {
        if ($this->raw && !$this->value instanceof Raw && !is_null($this->value)) {
            return new Raw($this->value);
        }
        
        if (is_bool($this->value)) {
            return $this->value ? 1 : 0;
        }
        
        return $this->value;
    }
    
    
    /**
     * 构建查询字段
     * @return string
     */
    public function build() : string
    {
        $field = $this->field;
        
        // 虚拟字段
        if ($this->virtual) {
            $field = StringHelper::snake($field);
        }
        
        // 表名
        if ($this->table) {
            $field = $this->table . '.' . $field;
        }
        
        // 表达式
        if ($this->exp) {
            if (str_contains($this->exp, '%s')) {
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
        
        return $this;
    }
    
    
    /**
     * 获取对应字段类
     * @return class-string<Field>|Field
     */
    public function getFieldClass() : string
    {
        return $this->fieldClass;
    }
    
    
    /**
     * 获取属性
     * @return array{title: string, name: string, field: string|false, field_type: string, types: array<ReflectionNamedType>, var_type: string, filter: array<Filter>, format: ?Format, validate: array<Validator>, access: int, property: ReflectionProperty, export: ?Export, import: ?Import}
     */
    public function getPropertyAttr() : array
    {
        return $this->getFieldClass()::getPropertyAttrs($this->name);
    }
    
    
    public function __toString()
    {
        return $this->build();
    }
    
    
    /**
     * 解析field
     * @param mixed $field
     * @return array|string
     */
    public static function parse(mixed $field) : array|string
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
     * 获取属性名称
     * @param string|Entity $value
     * @return string
     */
    public static function cast(string|Entity $value) : string
    {
        if ($value instanceof Entity) {
            return $value();
        }
        
        return $value;
    }
    
    
    /**
     * 尝试执行回调并返回对象
     * @param mixed $callable
     * @param mixed ...$vars
     * @return static|null
     */
    public static function tryCallable(mixed $callable, ...$vars) : ?self
    {
        if (!is_array($callable) || !is_callable($callable) || !isset($callable[0]) || !is_subclass_of($callable[0], Field::class)) {
            return null;
        }
        
        return call_user_func($callable, ...$vars);
    }
}