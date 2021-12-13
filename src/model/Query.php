<?php
declare (strict_types = 1);

namespace BusyPHP\model;

use BusyPHP\Model;
use PDOStatement;

/**
 * 扩展查询方法，主要用于兼容TP3.1语法
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/27 下午5:56 下午 Query.php $
 */
class Query extends \think\db\Query
{
    /**
     * 模型字段实体条件
     * @param mixed ...$entity
     * @return $this
     */
    public function whereEntity(...$entity) : self
    {
        foreach ($entity as $item) {
            if ($item instanceof Entity) {
                $this->where($item->field(), $item->op(), $item->value());
            }
        }
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    protected function parseWhereExp(string $logic, $field, $op, $condition, array $param = [], bool $strict = false)
    {
        return parent::parseWhereExp($logic, Entity::parse($field), $op, $condition, $param, $strict);
    }
    
    
    /**
     * @inheritDoc
     */
    public function join($join, string $condition = null, string $type = 'INNER', array $bind = []) : self
    {
        if ($join instanceof Model) {
            $model = $join;
            $join  = [$model->getTable() => $model->getJoinAlias()];
            $model->removeOption();
        } elseif (is_string($join) && is_subclass_of($join, Model::class)) {
            /** @var Model $model */
            $model = call_user_func([$join, 'init']);
            $join  = [$model->getTable() => $model->getJoinAlias()];
            $model->removeOption();
        }
        
        return parent::join($join, $condition, $type, $bind);
    }
    
    
    /**
     * 指定排序 order('id','desc') 或者 order(['id'=>'desc','create_time'=>'desc'])
     * @param mixed  $field 排序字段
     * @param string $order 排序
     * @return $this
     */
    public function order($field, string $order = '')
    {
        return parent::order(Entity::parse($field), $order);
    }
    
    
    /**
     * 指定查询字段
     * @param mixed $field 字段信息
     * @return $this
     */
    public function field($field)
    {
        return parent::field(Entity::parse($field));
    }
    
    
    /**
     * 执行查询但只返回PDOStatement对象
     * todo 目前使用where指定条件不会解析参数
     * @return PDOStatement
     */
    public function getPdo() : PDOStatement
    {
        $this->options['distinct'] = $this->options['distinct'] ?? false;
        $this->options['extra']    = $this->options['extra'] ?? '';
        $this->options['join']     = $this->options['join'] ?? [];
        $this->options['where']    = $this->options['where'] ?? [];
        $this->options['having']   = $this->options['having'] ?? '';
        $this->options['order']    = $this->options['order'] ?? [];
        $this->options['limit']    = $this->options['limit'] ?? '';
        $this->options['union']    = $this->options['union'] ?? [];
        $this->options['comment']  = $this->options['comment'] ?? '';
        $this->options['table']    = $this->options['table'] ?? $this->getTable();
        
        return parent::getPdo();
    }
    
    
    /**
     * 时间戳范围条件
     * @param string|Entity $field 字段
     * @param string|int    $startOrTimeRange 开始时间或时间范围
     * @param string|int    $endOrSpace 结束时间或时间范围分隔符
     * @param bool          $split 是否分割传入的时间范围
     * @return $this
     */
    public function whereTimeIntervalRange($field, $startOrTimeRange = 0, $endOrSpace = 0, $split = false) : self
    {
        $field = (string) $field;
        if ($split && $endOrSpace) {
            [$start, $end] = explode($endOrSpace, $startOrTimeRange);
            $start = (int) strtotime($start);
            $end   = (int) strtotime($end);
        } else {
            $start = (int) (!is_numeric($startOrTimeRange) ? strtotime($startOrTimeRange) : $startOrTimeRange);
            $end   = (int) (!is_numeric($endOrSpace) ? strtotime($endOrSpace) : $endOrSpace);
        }
        
        if ($start > 0 && $end > 0) {
            if ($end >= $start) {
                $this->whereBetweenTime($field, $start, $end);
            } else {
                $this->whereBetweenTime($field, $end, $start);
            }
        } elseif ($start > 0) {
            $this->where($field, '>=', $start);
        } elseif ($end > 0) {
            $this->where($field, '<=', $end);
        }
        
        return $this;
    }
}