<?php
declare(strict_types = 1);

namespace BusyPHP\model;

use Closure;

/**
 * JOIN条件类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/19 19:41 JoinClause.php $
 */
class JoinClause
{
    protected array $condition = [];
    
    
    /**
     * JOIN ON AND
     * @param string|Entity|Closure $field 字段
     * @param string|null           $op 等式
     * @param mixed                 $condition 条件
     * @param string                $logic 逻辑 AND OR
     * @return $this
     */
    public function on(string|Entity|Closure $field, string|null $op = null, mixed $condition = null, string $logic = 'AND') : static
    {
        if ($field instanceof Closure) {
            $this->condition[$logic][] = $field;
        } else {
            if ($field instanceof Entity) {
                if ($op = $field->getOp()) {
                    $value = $field->getValue();
                    if ($value instanceof Entity) {
                        $this->condition[$logic][] = [$field->field(), $op, $value->field()];
                    } else {
                        $up = strtoupper($op);
                        if (in_array($up, ['NULL', 'NOTNULL', 'NOT NULL'], true)) {
                            $value = str_replace('NOTNULL', 'NOT NULL', $up);
                            $op    = 'IS';
                        }
                        $this->condition[$logic][] = [$field->field(), $op, $value];
                    }
                    
                    return $this;
                }
                
                $field = $field->field();
            }
            
            if ($condition instanceof Entity) {
                $condition = $condition->field();
            }
            $this->condition[$logic][] = [$field, $op, $condition];
        }
        
        return $this;
    }
    
    
    /**
     * JOIN ON OR
     * @param string|Entity|Closure $field 字段
     * @param string|null           $op 等式
     * @param mixed                 $condition 条件
     * @return $this
     */
    public function orOn(string|Entity|Closure $field, string|null $op = null, mixed $condition = null) : static
    {
        return $this->on($field, $op, $condition, 'OR');
    }
    
    
    /**
     * 创建新实例
     * @return static
     */
    public function newJoin() : static
    {
        return new static();
    }
    
    
    /**
     * 生成条件
     * @return string
     */
    public function build() : string
    {
        $condition = '';
        foreach ($this->condition as $logic => $values) {
            $str       = implode('', $this->parseConditionItem($logic, $values));
            $condition .= empty($condition) ? substr($str, strlen($logic) + 2) : $str;
        }
        
        return $condition;
    }
    
    
    /**
     * 分析单元条件
     * @param string $logic
     * @param array  $values
     * @return array
     */
    protected function parseConditionItem(string $logic, array $values) : array
    {
        $conditions = [];
        foreach ($values as $item) {
            if ($item instanceof Closure) {
                $item($join = $this->newJoin());
                if ($val = $join->build()) {
                    $conditions[] = sprintf(' %s ( %s )', $logic, $val);
                }
            } else {
                $conditions[] = sprintf(' %s %s %s %s', $logic, $item[0], $item[1], $item[2]);
            }
        }
        
        return $conditions;
    }
}