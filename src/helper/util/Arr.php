<?php

namespace BusyPHP\helper\util;

use BusyPHP\model\Field;
use think\Collection;

/**
 * 数组操作类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/16 下午4:27 下午 Arr.php $
 */
class Arr extends \think\helper\Arr
{
    /** 升序 */
    const ORDER_BY_ASC = 'asc';
    
    /** 降序 */
    const ORDER_BY_DESC = 'desc';
    
    /** 自然排序 */
    const ORDER_BY_NAT = 'nat';
    
    
    /**
     * 把返回的数据集转换成Tree
     * @param array  $list 要转换的数据集
     * @param string $pkKey 主键字段
     * @param string $parentKey parent标记字段
     * @param string $childKey 子节点字段
     * @param int    $root parent字段依据，默认为0则代表是跟节点
     * @return array
     */
    public static function listToTree($list, $pkKey = 'id', $parentKey = 'parent_id', $childKey = 'child', $root = 0)
    {
        // 创建Tree
        $tree = [];
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = [];
            foreach ($list as $key => $data) {
                $refer[$data[$pkKey]] =& $list[$key];
            }
            
            
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$parentKey];
                if ($root == $parentId) {
                    $tree[] = &$list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent              = &$refer[$parentId];
                        $parent[$childKey][] = &$list[$key];
                    }
                }
            }
        }
        
        return $tree;
    }
    
    
    /**
     * 对二维数组进行排序
     * @param array  $list 要排序的二维数据
     * @param string $field 排序依据的字段
     * @param string $orderBy 排序方式，默认位升序
     * @return array
     */
    public static function listSortBy($list, $field, $orderBy = 'asc')
    {
        if (is_array($list)) {
            $refer = $resultSet = [];
            foreach ($list as $i => $data) {
                $refer[$i] = &$data[$field];
            }
            switch ($orderBy) {
                // 正向排序
                case self::ORDER_BY_ASC:
                    asort($refer);
                break;
                
                // 逆向排序
                case self::ORDER_BY_DESC:
                    arsort($refer);
                break;
                
                // 自然排序
                case self::ORDER_BY_NAT:
                    natcasesort($refer);
                break;
            }
            foreach ($refer as $key => $val) {
                $resultSet[] = &$list[$key];
            }
            
            return $resultSet;
        }
        
        return [];
    }
    
    
    /**
     * 对二维数组进行搜索
     * @param array        $list 数据列表
     * @param string|array $condition 查询条件，支持 array('name'=>$value) 或者 name=$value
     * @return array
     */
    public static function listSearch($list, $condition)
    {
        if (is_string($condition)) {
            parse_str($condition, $condition);
        }
        // 返回的结果集合
        $resultSet = [];
        foreach ($list as $key => $data) {
            $find = false;
            foreach ($condition as $field => $value) {
                if (isset($data[$field])) {
                    if (0 === strpos($value, '/')) {
                        $find = preg_match($value, $data[$field]);
                    } elseif ($data[$field] == $value) {
                        $find = true;
                    }
                }
            }
            if ($find) {
                $resultSet[] =   &$list[$key];
            }
        }
        
        return $resultSet;
    }
    
    
    /**
     * 将列表数据通过某字段值作为主键重新整理
     * @param array|Collection $list 列表
     * @param string           $key 字段名称
     * @return array
     */
    public static function listByKey($list, $key)
    {
        $list    = is_array($list) || $list instanceof Collection ? $list : [];
        $newList = [];
        foreach ($list as $r) {
            if (is_object($r)) {
                $newList[$r->{$key}] = $r;
            } else {
                $newList[$r[$key]] = $r;
            }
        }
        
        return $newList;
    }
    
    
    /**
     * 对树状结构数据进行排序
     * @param array  $tree 树状结构数据
     * @param string $sortKey 排序依据字段
     * @param string $order 排序方式
     * @param string $childKey 子节点字段
     * @param int    $level 层级
     * @return array
     */
    public static function sortTree($tree, $sortKey = 'sort', $order = self::ORDER_BY_ASC, $childKey = 'child', $level = 1)
    {
        foreach ($tree as $i => $r) {
            $r['level'] = $level;
            if (isset($r[$childKey]) && count($r[$childKey]) > 0) {
                $r[$childKey] = self::sortTree($r[$childKey], $sortKey, $order, $childKey, $level + 1);
            } else {
                $r[$childKey] = [];
            }
            
            $tree[$i] = $r;
        }
        
        return self::listSortBy($tree, $sortKey, $order);
    }
    
    
    /**
     * 将一个数组平均拆分
     * @param array $list 要拆分的数组
     * @param int   $size 拆分成几个组
     * @return array
     */
    public static function averageSplit($list, $size)
    {
        $list    = is_array($list) ? $list : [];
        $length  = count($list);
        $average = floor($length / $size);
        $surplus = $length % $size;
        $start   = 0;
        $array   = [];
        for ($i = 0; $i < $size; $i++) {
            $end       = $i < $surplus ? $average + 1 : $average;
            $array[$i] = array_slice($list, $start, $end);
            $start     = $start + $end;
        }
        
        return $array;
    }
}