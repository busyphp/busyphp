<?php
declare(strict_types = 1);

namespace BusyPHP\helper;

use ArrayAccess;
use BusyPHP\model\ArrayOption;
use think\Collection;
use think\helper\Arr;

/**
 * 数组操作辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午上午10:21 ArrayHelper.php $
 * @template T
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
     * @param array<T>      $list 要转换的数据集
     * @param string        $primaryKey 主键字段
     * @param string        $parentKey parent标记字段
     * @param string        $childKey 子节点字段
     * @param int           $root parent字段依据，默认为0则代表是跟节点
     * @param callable|null $filter 数据过滤方法，接受一个$item
     * @return array<T>
     */
    public static function listToTree(array $list, string $primaryKey = 'id', string $parentKey = 'parent_id', string $childKey = 'child', $root = 0, ?callable $filter = null) : array
    {
        // 创建Tree
        $tree = [];
        // 创建基于主键的数组引用
        $refer = [];
        foreach ($list as $key => $data) {
            if (is_object($data)) {
                $refer[$data->{$primaryKey}] = &$list[$key];
            } else {
                $refer[$data[$primaryKey]] = &$list[$key];
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
        
        return $tree;
    }
    
    
    /**
     * 把树状数据转为数据集
     * @param array<T> $tree 树状数据
     * @param string   $childKey 子节点字段
     * @param bool     $clearChild 是否清理子节点
     * @param array    $list 内部用
     * @return array<T>
     */
    public static function treeToList(array $tree, string $childKey = 'child', bool $clearChild = true, array &$list = []) : array
    {
        foreach ($tree as $item) {
            if (is_object($item)) {
                $child = $item->{$childKey} ?? [];
            } else {
                $child = $item[$childKey] ?? [];
            }
            
            static::treeToList($child, $childKey, $clearChild, $list);
            
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
     * @param array<T> $list 要排序的二维数据
     * @param string   $field 排序依据的字段
     * @param string   $orderBy 排序方式，默认位升序
     * @return array<T>
     */
    public static function listSortBy(array $list, string $field, string $orderBy = self::ORDER_BY_ASC) : array
    {
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
    
    
    /**
     * 对二维数组进行搜索
     * @param array<T>     $list 数据列表
     * @param string|array $condition 查询条件，支持 array('name'=>$value) 或者 name=$value
     * @return array<T>
     */
    public static function listSearch(array $list, string|array $condition) : array
    {
        if (is_string($condition)) {
            $result = [];
            parse_str($condition, $result);
            $condition = $result;
        }
        
        // 返回的结果集合
        $resultSet = [];
        foreach ($list as $key => $data) {
            $find = false;
            foreach ($condition as $field => $value) {
                if (isset($data[$field])) {
                    if (str_starts_with($value, '/')) {
                        $find = preg_match($value, $data[$field]);
                    } elseif ($data[$field] == $value) {
                        $find = true;
                    }
                }
            }
            if ($find) {
                $resultSet[] = &$list[$key];
            }
        }
        
        return $resultSet;
    }
    
    
    /**
     * 将列表数据通过某字段值作为主键重新整理
     * @param array<T>|Collection $list 数据
     * @param string              $key 索引键
     * @return array<T>|Collection
     */
    public static function listByKey(array|Collection $list, string $key) : Collection|array
    {
        $make = $list instanceof Collection;
        $list = array_column(is_array($list) || $make ? $list : [], null, $key);
        
        return $make ? Collection::make($list) : $list;
    }
    
    
    /**
     * 对树状结构数据进行排序
     * @param array<T> $tree 树状结构数据
     * @param string   $sortKey 排序依据字段
     * @param string   $order 排序方式
     * @param string   $childKey 子节点字段
     * @param int      $level 层级
     * @return array<T>
     */
    public static function sortTree(array $tree, string $sortKey = 'sort', string $order = self::ORDER_BY_ASC, string $childKey = 'child', int $level = 1) : array
    {
        foreach ($tree as $i => $r) {
            $r['level'] = $level;
            if (isset($r[$childKey]) && count($r[$childKey]) > 0) {
                $r[$childKey] = static::sortTree($r[$childKey], $sortKey, $order, $childKey, $level + 1);
            } else {
                $r[$childKey] = [];
            }
            
            $tree[$i] = $r;
        }
        
        return static::listSortBy($tree, $sortKey, $order);
    }
    
    
    /**
     * 将一个数组平均拆分
     * @param array<T> $list 要拆分的数组
     * @param int      $size 拆分成几个组
     * @return array<int, array<T>>
     */
    public static function averageSplit(array $list, int $size) : array
    {
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
    
    
    /**
     * 将字符串按规定分隔符拆分
     * @param string   $separator 分隔符
     * @param mixed    $string 切割的字符串
     * @param int      $minLength 最小数组长度
     * @param int|null $limit 切割限制次数
     * @return array
     */
    public static function split(string $separator, mixed $string, int $minLength, ?int $limit = null) : array
    {
        if (!is_null($limit)) {
            $arr = explode($separator, (string) $string, $limit);
        } else {
            $arr = explode($separator, (string) $string);
        }
        $length = count($arr);
        for ($i = $length; $i < $minLength; $i++) {
            $arr[$i] = '';
        }
        
        return $arr;
    }
    
    
    /**
     * 将一维数组转为二维数组
     * @param array $array 一维数组
     * @param int   $split 按每多少个数组分割
     * @param bool  $toOption 是否返回 {@see ArrayOption} 对象，键为数字会删除
     * @return array|ArrayOption
     */
    public static function oneToTwo(array $array, int $split = 2, bool $toOption = true) : ArrayOption|array
    {
        $length = count($array);
        $arr    = [];
        for ($i = 0; $i < $length; $i += $split) {
            $key = $array[$i];
            if (!is_scalar($key) || ($toOption && is_numeric($key)) || is_bool($key)) {
                continue;
            }
            
            if ($split <= 2) {
                $arr[$key] = $array[$i + 1] ?? '';
            } else {
                $arr[$key] = $arr[$key] ?? [];
                for ($n = 1; $n < $split; $n++) {
                    $arr[$key][] = $array[$i + $n] ?? '';
                }
            }
        }
        
        return $toOption ? ArrayOption::init($arr) : $arr;
    }
    
    
    /**
     * 通过键获取值，如果键为null则返回该数据
     * @param array $map
     * @param mixed $key
     * @return mixed
     */
    public static function getValueOrSelf(array $map, $key = null) : mixed
    {
        if (is_null($key)) {
            return $map;
        }
        
        return $map[$key] ?? null;
    }
    
    
    /**
     * 将数组中子数组拆解并追加到本身
     * @param array         $array 数组
     * @param string|null   $split 字符串切分符号
     * @param callable|null $filter 过滤回调
     * @return array
     */
    public static function flat(array $array, string $split = null, callable $filter = null) : array
    {
        $list = [];
        foreach ($array as $item) {
            if ($split && is_string($item) && '' !== $item) {
                $item = explode($split, $item);
            }
            
            if (is_array($item)) {
                $list = array_merge($list, $item);
            } else {
                $list[] = $item;
            }
        }
        
        if ($filter) {
            $list = call_user_func($filter, $list);
        }
        
        return $list;
    }
    
    
    /**
     * 向上递归获取上级集合
     * @param array<mixed,array|ArrayAccess> $list $key为下标的列表
     * @param array|ArrayAccess              $item 数据项
     * @param string                         $key 主键
     * @param string                         $parentKey 上级主键
     * @param array                          $gather 赋值
     * @return void
     */
    public static function upwardRecursion(array $list, $item, string $key = 'id', string $parentKey = 'parent_id', array &$gather = [])
    {
        if (isset($list[$item[$parentKey]])) {
            $newItem  = $list[$item[$parentKey]];
            $gather[] = $newItem[$key];
            static::upwardRecursion($list, $newItem, $key, $parentKey, $gather);
        }
    }
}