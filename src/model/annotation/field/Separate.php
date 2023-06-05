<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\model\Field;

/**
 * 字段数据切割注解类，用于 {@see Field} 中虚拟setter方法对数据按规则切割，或 {@see Field::getModelData()} 对数据按规则组合
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/12/15 16:36 Separate.php $
 * @see Field
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Separate extends Format
{
    private string $separator;
    
    private bool   $full;
    
    private bool   $unique;
    
    private mixed  $filter;
    
    
    /**
     * 构造函数
     * @param string        $separator 分隔符
     * @param bool          $full 左右是否包含分隔符
     * @param bool          $unique 是否去重
     * @param callable|bool $filter 去空，true则去除空字符，false则不去除任何字符，设置回调则按 {@see array_filter} 闭包规则执行
     */
    public function __construct(string $separator = ',', bool $full = true, bool $unique = true, callable|bool $filter = false)
    {
        $this->separator = $separator;
        $this->full      = $full;
        $this->unique    = $unique;
        $this->filter    = $filter;
    }
    
    
    /**
     * 获取分割符号
     * @return string
     */
    public function getSeparator() : string
    {
        return $this->separator;
    }
    
    
    /**
     * 设置分割符号
     * @param string $separator
     * @return $this
     */
    public function setSeparator(string $separator) : self
    {
        $this->separator = $separator;
        
        return $this;
    }
    
    
    /**
     * 左右是否包含分隔符
     * @return bool
     */
    public function isFull() : bool
    {
        return $this->full;
    }
    
    
    /**
     * 设置左右是否包含分隔符
     * @param bool $full
     * @return $this
     */
    public function setFull(bool $full) : self
    {
        $this->full = $full;
        
        return $this;
    }
    
    
    /**
     * 是否去重
     * @return bool
     */
    public function isUnique() : bool
    {
        return $this->unique;
    }
    
    
    /**
     * 设置是否去重
     * @param bool $unique
     * @return $this
     */
    public function setUnique(bool $unique) : self
    {
        $this->unique = $unique;
        
        return $this;
    }
    
    
    /**
     * 去空规则
     * @return bool|callable
     */
    public function getFilter() : bool|callable
    {
        return $this->filter;
    }
    
    
    /**
     * 设置去空 true则去除空字符，false则不去除任何字符，设置回调则按 {@see array_filter} 闭包规则执行
     * @param bool $filter
     * @return $this
     */
    public function setFilter(callable|bool $filter) : self
    {
        $this->filter = $filter;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function encode(mixed $data) : string
    {
        if (!is_array($data)) {
            return (string) $data;
        }
        
        $data = implode($this->separator, $this->unique($this->filter($data)));
        if ($this->full) {
            $data = $this->separator . $data . $this->separator;
        }
        
        return $data;
    }
    
    
    /**
     * @inheritDoc
     */
    public function decode(string $data) : array
    {
        return $this->unique($this->filter(explode($this->separator, $data) ?: []));
    }
    
    
    /**
     * 去重
     * @param array $data
     * @return array
     */
    protected function unique(array $data) : array
    {
        if ($this->unique) {
            $data = array_unique($data);
            $data = array_values($data);
        }
        
        return $data;
    }
    
    
    /**
     * 过滤
     * @param array $data
     * @return array
     */
    protected function filter(array $data) : array
    {
        if ($this->filter === true) {
            $data = array_filter($data, function($item) {
                $item = trim($item);
                if ($item === '') {
                    return false;
                }
                
                return true;
            });
        } elseif (is_callable($this->filter)) {
            $data = call_user_func($this->filter, $data);
        }
        
        return $data;
    }
}