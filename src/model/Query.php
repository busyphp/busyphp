<?php
declare (strict_types = 1);

namespace BusyPHP\model;

use BusyPHP\Model;
use PDOStatement;

/**
 * 扩展查询方法
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
    public function whereEntity(...$entity)
    {
        foreach ($entity as $item) {
            if ($item instanceof Entity) {
                $value = $item->value();
                if ($value instanceof Entity) {
                    $this->whereRaw(sprintf('`%s` %s `%s`', $item->field(), $item->op(), $value->field()));
                } else {
                    $this->where($item->field(), $item->op(), $item->value());
                }
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
    public function join($join, string $condition = null, string $type = 'INNER', array $bind = [])
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
     * @inheritDoc
     */
    public function order($field, string $order = '')
    {
        return parent::order(Entity::parse($field), $order);
    }
    
    
    /**
     * @inheritDoc
     */
    public function field($field)
    {
        return parent::field(Entity::parse($field));
    }
    
    
    /**
     * @inheritDoc
     */
    public function group($group)
    {
        return parent::group(Entity::parse($group));
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
    public function whereTimeIntervalRange($field, $startOrTimeRange = 0, $endOrSpace = 0, $split = false)
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