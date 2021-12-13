<?php

namespace BusyPHP\helper;

use BusyPHP\model\Entity;
use think\Collection;
use think\Event;
use think\helper\Arr;

/**
 * 数组操作辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午上午10:21 ArrayHelper.php $
 */
class ArrayHelper extends Arr
{
    /** 升序 */
    const ORDER_BY_ASC = 'asc';
    
    /** 降序 */
    const ORDER_BY_DESC = 'desc';
    
    /** 自然排序 */
    const ORDER_BY_NAT = 'nat';
    
    
    /**
     * 把返回的数据集转换成Tree
     * @param array         $list 要转换的数据集
     * @param string|Entity $pkKey 主键字段
     * @param string|Entity $parentKey parent标记字段
     * @param string|Entity $childKey 子节点字段
     * @param int           $root parent字段依据，默认为0则代表是跟节点
     * @param callable      $filter 数据过滤方法，接受一个$item
     * @return array
     */
    public static function listToTree($list, $pkKey = 'id', $parentKey = 'parent_id', $childKey = 'child', int $root = 0, ?callable $filter = null)
    {
        $pkKey     = (string) $pkKey;
        $parentKey = (string) $parentKey;
        $childKey  = (string) $childKey;
        
        // 创建Tree
        $tree = [];
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = [];
            foreach ($list as $key => $data) {
                if (is_object($data)) {
                    $refer[$data->{$pkKey}] = &$list[$key];
                } else {
                    $refer[$data[$pkKey]] = &$list[$key];
                }
            }
            
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $isObject = is_object($data);
                $parentId = $isObject ? $data->{$parentKey} : $data[$parentKey];
                
                if ($root == $parentId) {
                    if (is_callable($filter) && false === call_user_func($filter, $data)) {
                        continue;
                    }
                    
                    $tree[] = &$list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        
                        if (is_callable($filter) && false === call_user_func($filter, $data)) {
                            continue;
                        }
                        
                        if ($isObject) {
                            $parent->{$childKey}[] = &$list[$key];
                        } else {
                            $parent[$childKey][] = &$list[$key];
                        }
                    }
                }
            }
        }
        
        return $tree;
    }
    
    
    /**
     * 把树状数据转为数据集
     * @param array        $tree 树状数据
     * @param string|Event $childKey 子节点字段
     * @param bool         $clearChild 是否清理子节点
     * @param array        $list 内部用
     * @return array
     */
    public static function treeToList($tree, $childKey = 'child', bool $clearChild = true, &$list = [])
    {
        $childKey = is_string($childKey) ? $clearChild : (string) $childKey;
        if (!is_array($tree)) {
            return $tree;
        }
        
        foreach ($tree as $item) {
            if (is_object($item)) {
                $child = $item->{$childKey} ?? [];
            } else {
                $child = $item[$childKey] ?? [];
            }
            
            self::treeToList($child, $childKey, $clearChild, $list);
            
            if ($clearChild) {
                if (is_object($item)) {
                    $item->{$childKey} = [];
                } else {
                    $item[$childKey] = [];
                }
            }
            
            $list[] = $item;
        }
        
        return $list;
    }
    
    
    /**
     * 对二维数组进行排序
     * @param array         $list 要排序的二维数据
     * @param string|Entity $field 排序依据的字段
     * @param string        $orderBy 排序方式，默认位升序
     * @return array
     */
    public static function listSortBy($list, $field, $orderBy = self::ORDER_BY_ASC)
    {
        $field = (string) $field;
        if (is_array($list)) {
            $refer = $resultSet = [];
            foreach ($list as $i => $data) {
                $refer[$i] = $data[$field];
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
     * @param string|Entity    $key 字段名称
     * @return array|Collection
     */
    public static function listByKey($list, $key)
    {
        $key          = (string) $key;
        $isCollection = $list instanceof Collection;
        $list         = is_array($list) || $isCollection ? $list : [];
        $newList      = [];
        foreach ($list as $r) {
            if (is_object($r)) {
                $newList[$r->{$key}] = $r;
            } else {
                $newList[$r[$key]] = $r;
            }
        }
        
        return $isCollection ? $list::make($newList) : $newList;
    }
    
    
    /**
     * 对树状结构数据进行排序
     * @param array         $tree 树状结构数据
     * @param string|Entity $sortKey 排序依据字段
     * @param string        $order 排序方式
     * @param string        $childKey 子节点字段
     * @param int           $level 层级
     * @return array
     */
    public static function sortTree($tree, $sortKey = 'sort', string $order = self::ORDER_BY_ASC, $childKey = 'child', int $level = 1)
    {
        $sortKey  = is_string($sortKey) ? $sortKey : (string) $sortKey;
        $childKey = is_string($childKey) ? $childKey : (string) $childKey;
        
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