<?php

namespace BusyPHP\helper\util;

/**
 * Map数据接口基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2017 busy^life <busy.life@qq.com>
 * @version $Id: 2017-08-14 下午10:26 Map.php busy^life $
 * @deprecated 不建议使用，请在注释中使用 @return 类名[] 比这个更好
 */
class Map
{
    protected $list  = array();
    private   $index = -1;
    
    
    /**
     * 快速实例化
     * @return $this
     */
    public static function init()
    {
        $className = get_called_class();
        
        return new $className();
    }
    
    
    /**
     * 获取列表数据
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }
    
    
    /**
     * 获取长度
     * @return int
     */
    public function length()
    {
        return count($this->list);
    }
    
    
    /**
     * 添加参数
     * @param mixed $object
     * @return $this
     */
    public function add($object)
    {
        $this->list[] = $object;
        
        return $this;
    }
    
    
    /**
     * 取值
     * @param int $index
     * @return mixed
     */
    public function get($index)
    {
        return $this->list[$index];
    }
    
    
    /**
     * 设置值
     * @param int   $index
     * @param mixed $object
     * @return $this
     */
    public function set($index, $object)
    {
        $this->list[$index] = $object;
        
        return $this;
    }
    
    
    /**
     * 删除某数据
     * @param $index
     * @return $this
     */
    public function remove($index)
    {
        unset($this->list[$index]);
        
        return $this;
    }
    
    
    /**
     * 清理所有数据
     * @return $this
     */
    public function clear()
    {
        $this->list = array();
        
        return $this;
    }
    
    
    /**
     * 指针移动
     */
    public function fetch()
    {
        $this->index++;
        if (!isset($this->list[$this->index])) {
            $this->index = -1;
            
            return false;
        }
        
        return $this->list[$this->index];
    }
    
    
    /**
     * 获取当前下标
     * @return int
     */
    public function index()
    {
        return $this->index;
    }
    
    
    /**
     * 对二维数组进行排序
     * @param array  $list 数组
     * @param string $byField 要排序的字段
     * @param int    $byOrder 正序还是倒序 SORT_ASC SORT_DESC
     * @return array
     */
    public static function sort($list, $byField, $byOrder = SORT_ASC)
    {
        $fields[] = array();
        foreach ($list as $i => $r) {
            $fields[$i] = floatval($r[$byField]);
        }
        array_multisort($fields, $byOrder, $list);
        
        return $list;
    }
    
    
    public function __toString()
    {
        return serialize($this->list);
    }
}