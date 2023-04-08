<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\export;

use BusyPHP\model\annotation\field\Export;
use BusyPHP\model\Entity;

/**
 * 导出列配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/6 12:05 Column.php $
 */
class Column
{
    /** @var string 强制转为字符串 */
    public const FILTER_STRING = ':string';
    
    /** @var string 将时间强制转为EXCEL格式 */
    public const FILTER_DATE = ':date';
    
    /**
     * 字母
     * @var string
     */
    protected string $letter;
    
    /**
     * 字段名称
     * @var string|Entity
     */
    protected string|Entity $field;
    
    /**
     * 显示名称
     * @var string
     */
    protected string $name;
    
    /**
     * 过滤方式
     * @var callable|string|null
     */
    protected mixed $filter;
    
    /**
     * 数字格式化选项
     * @var string
     */
    protected string $numberFormat = '';
    
    /**
     * 是否图片
     * @var bool
     */
    protected bool $image = false;
    
    
    /**
     * 快速实例化
     * @param string               $letter 所在列字母
     * @param Entity|string        $field 数据字段名称，支持 "." 链接访问下级数据
     * @param string               $name 显示名称
     * @param callable|string|null $filter 过滤器
     * @return static
     */
    public static function init(string $letter, Entity|string $field, string $name = '', callable|string|null $filter = null) : static
    {
        return new static($letter, $field, $name, $filter);
    }
    
    
    /**
     * 构造函数
     * @param string               $letter 所在列字母
     * @param Entity|string        $field 数据字段名称
     * @param string               $name 显示名称
     * @param callable|string|null $filter 过滤器
     */
    public function __construct(string $letter, Entity|string $field, string $name = '', callable|string|null $filter = null)
    {
        $this->letter = strtoupper(trim($letter));
        $this->field  = $field;
        $this->name   = trim($name);
        $this->filter = $filter;
        
        $title = '';
        if ($this->field instanceof Entity) {
            $attr  = $this->field->getPropertyAttr();
            $title = $attr['title'];
            
            // 使用导出配置
            /** @var Export $export */
            $export = $attr['export'];
            if ($export) {
                $title = $export->getName();
                if (!$this->letter && $letter = $export->getLetter()) {
                    $this->letter = $letter;
                }
                if (!$this->filter) {
                    $this->filter = $export->getFilter();
                }
                $this->numberFormat($export->getNumberFormat());
                $this->image($export->isImage());
            }
            $this->field = $this->field->name();
        }
        
        if ($this->name === '') {
            $this->name = $title === '' ? $this->field : $title;
        }
    }
    
    
    /**
     * 获取显示名称
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    
    /**
     * 获取字段名称
     * @return string
     */
    public function getField() : string
    {
        return $this->field;
    }
    
    
    /**
     * 获取过滤器
     * @return callable|string|null
     */
    public function getFilter() : callable|string|null
    {
        return $this->filter;
    }
    
    
    /**
     * @param string $letter
     * @return static
     */
    public function setLetter(string $letter) : static
    {
        $this->letter = $letter;
        
        return $this;
    }
    
    
    /**
     * 获取列
     * @return string
     */
    public function getLetter() : string
    {
        return $this->letter;
    }
    
    
    /**
     * 设置数字格式化方式
     * @param string $numberFormat
     * @return static
     */
    public function numberFormat(string $numberFormat) : static
    {
        $this->numberFormat = $numberFormat;
        
        return $this;
    }
    
    
    /**
     * 获取数字格式化方式
     * @return string
     */
    public function getNumberFormat() : string
    {
        return $this->numberFormat;
    }
    
    
    /**
     * 设置该列是否图片
     * @param bool $image
     * @return $this
     */
    public function image(bool $image) : static
    {
        $this->image = $image;
        
        return $this;
    }
    
    
    /**
     * 是否图片
     * @return bool
     */
    public function isImage() : bool
    {
        return $this->image;
    }
}