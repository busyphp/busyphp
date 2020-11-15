<?php
declare (strict_types = 1);

namespace BusyPHP\model;

use BusyPHP\Model;
use PDOStatement;
use think\facade\Db;

/**
 * 扩展查询方法，主要用于兼容TP3.1语法
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/27 下午5:56 下午 Query.php $
 */
class Query extends \think\db\Query
{
    /**
     * 数据库查询表达式
     * @var array
     */
    protected $whereComparison = [
        'eq'         => '=',
        'neq'        => '<>',
        'gt'         => '>',
        'egt'        => '>=',
        'lt'         => '<',
        'elt'        => '<=',
        'notlike'    => 'NOT LIKE',
        'like'       => 'LIKE',
        'in'         => 'IN',
        'notin'      => 'NOT IN',
        'between'    => 'BETWEEN',
        'notbetween' => 'NOT BETWEEN',
    ];
    
    
    /**
     * Limit操作
     * @param mixed $limit 支持传 start,length 格式的字符串
     * @param int   $length
     * @return $this
     */
    public function limiting($limit, int $length = null)
    {
        if (false !== strpos("{$limit}", ',')) {
            [$limit, $length] = explode(',', $limit, 2);
            $length = intval($length);
        }
        
        $limit = intval($limit);
        $this->limit($limit, $length);
        
        return $this;
    }
    
    
    /**
     * 单个条件查询
     * @param string|int|array $key 条件字段
     * @param mixed            $value 条件值
     * @return $this
     */
    public function one($key, $value = null)
    {
        if (is_null($value)) {
            $this->where($this->getPk(), '=', $key);
        } else {
            $this->where($key, '=', $value);
        }
        
        return $this;
    }
    
    
    /**
     * 查询条件兼容TP3.1的查询语句
     * @param mixed|Field $where
     * @return $this
     */
    public function whereof($where)
    {
        if ($where instanceof Field) {
            $where = $where->getWhere();
        }
        
        // 如果传入的是数组
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $this->parseWhereOfItem($key, $value);
            }
        }
        
        //
        // 传入字符串直接当成查询语句
        elseif (is_string($where)) {
            $this->whereRaw($where);
        }
        
        return $this;
    }
    
    
    /**
     * 枚举查询条件
     * @param array $enum
     * @return $this
     */
    public function whereEnum(...$enum)
    {
        foreach ($enum as $item) {
            if (!is_array($item) || count($item) != 3) {
                continue;
            }
            
            $this->where($item[0], $item[1], $item[2]);
        }
        
        return $this;
    }
    
    
    /**
     * TP6 JOIN方法 扩展
     * 支持别名通过参数传入
     * @param mixed  $join 关联的表名
     * @param string $alias 别名
     * @param string $condition 条件
     * @param string $type JOIN类型
     * @param array  $bind 参数绑定
     * @return $this
     */
    public function joining($join, string $alias = '', string $condition = '', string $type = 'INNER', array $bind = [])
    {
        // 传入的是模型
        if ($join instanceof Model) {
            $join = $join->getTableWithoutPrefix();
        }
        
        if ($alias) {
            $join .= ' ' . $alias;
        }
        
        $this->join($join, $condition, $type, $bind);
        
        return $this;
    }
    
    
    /**
     * whereof子单元分析
     * @param string $key
     * @param mixed  $val
     */
    protected function parseWhereOfItem($key, $val)
    {
        // 特殊键
        if ($key === '_string') {
            $this->whereRaw($val);
            
            return;
        }
        
        
        // 如果值是数组
        if (is_array($val)) {
            // 第一个参数是字符
            // array('eq', 1)
            if (is_string($val[0])) {
                // 比较运算
                if (preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT)$/i', $val[0])) {
                    $this->where($key, $this->whereComparison[strtolower($val[0])], $this->parseWhereOfItemValue($val[1]));
                }
                
                // LIKE
                // NOT LIKE
                elseif (preg_match('/^(NOTLIKE|LIKE)$/i', $val[0])) {
                    $comparison = $this->whereComparison[strtolower($val[0])];
                    if (is_array($val[1])) {
                        $likeLogic = isset($val[2]) ? strtoupper($val[2]) : 'OR';
                        if (in_array($likeLogic, ['AND', 'OR', 'XOR'])) {
                            $this->where($key, $comparison, $val[1], $likeLogic);
                        }
                    } else {
                        $this->where($key, $comparison, $this->parseWhereOfItemValue($val[1]));
                    }
                }
                
                // EXP
                // 表达式
                elseif ('exp' == strtolower($val[0])) {
                    $this->where($key, 'exp', $val[1]);
                }
                
                // IN
                // NOT IN
                elseif (preg_match('/IN/i', $val[0])) {
                    $val[0]     = strtolower($val[0]) == 'not in' ? 'notin' : strtolower($val[0]);
                    $comparison = $this->whereComparison[$val[0]];
                    
                    if (isset($val[2]) && 'exp' == $val[2]) {
                        $this->where($key, 'exp', $comparison . ' ' . $val[1]);
                    } else {
                        if (is_string($val[1])) {
                            $val[1] = explode(',', $val[1]);
                        }
                        $this->where($key, $comparison, implode(',', $this->parseWhereOfItemValue($val[1])));
                    }
                }
                
                // BETWEEN
                // NOT BETWEEN
                elseif (preg_match('/BETWEEN/i', $val[0])) {
                    $val[0] = strtolower($val[0]) == 'not between' ? 'notbetween' : strtolower($val[0]);
                    $this->where($key, $this->whereComparison[$val[0]], $val[1]);
                }
            }
            
            // 第一个参数是数组
            // array(array(条件1), array(条件2), ..., 关系)
            else {
                $count    = count($val);
                $whereStr = '';
                $rule     = isset($val[$count - 1]) && !is_array($val[$count - 1]) ? strtoupper($val[$count - 1]) : '';
                if (in_array($rule, ['AND', 'OR', 'XOR'])) {
                    $count = $count - 1;
                } else {
                    $rule = 'AND';
                }
                
                for ($i = 0; $i < $count; $i++) {
                    $data = is_array($val[$i]) ? $val[$i][1] : $val[$i];
                    if ('exp' == strtolower($val[$i][0])) {
                        $whereStr .= '(' . $key . ' ' . $data . ') ' . $rule . ' ';
                    } else {
                        $op       = is_array($val[$i]) ? $this->whereComparison[strtolower($val[$i][0])] : '=';
                        $whereStr .= '(' . $key . ' ' . $op . ' ' . $this->parseWhereOfItemValue($data) . ') ' . $rule . ' ';
                    }
                }
                $whereStr = substr($whereStr, 0, -4);
                $this->whereRaw($whereStr);
            }
        } else {
            $this->where($key, '=', $val);
        }
    }
    
    
    /**
     * value分析
     * @param mixed $value
     * @return string
     */
    protected function parseWhereOfItemValue($value)
    {
        if (isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp') {
            $value = Db::raw($value[1]);
        } elseif (is_array($value)) {
            $value = array_map([$this, 'parseWhereOfItemValue'], $value);
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_null($value)) {
            $value = 'null';
        }
        
        return $value;
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
}