<?php
declare(strict_types = 1);

namespace BusyPHP\model\annotation\field;

use Attribute;

/**
 * 导出该字段注解
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/6 16:39 Export.php $
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Export
{
    /**
     * 名称
     * @var string
     */
    private string $name;
    
    /**
     * 过滤方式
     * @var callable|string|null
     */
    private mixed $filter;
    
    /**
     * 数字格式化选项
     * @var string
     */
    private string $numberFormat;
    
    /**
     * 是否图片
     * @var bool
     */
    private bool $image;
    
    /**
     * 字母
     * @var string
     */
    private string $letter;
    
    
    /**
     * 构造函数
     * @param string               $letter 字母
     * @param string               $name 标题
     * @param string|callable|null $filter 导出过滤回调
     * @param string               $numberFormat 数字格式化选项
     * @param bool                 $image 是否图片
     */
    public function __construct(string $letter = '', string $name = '', string|callable|null $filter = null, string $numberFormat = '', bool $image = false)
    {
        $this->name         = $name;
        $this->filter       = $filter;
        $this->numberFormat = $numberFormat;
        $this->image        = $image;
        $this->letter       = strtoupper($letter);
    }
    
    
    /**
     * 获取字母
     * @return string
     */
    public function getLetter() : string
    {
        return $this->letter;
    }
    
    
    /**
     * 获取名称
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    
    /**
     * 设置名称
     * @param string $name
     * @return static
     */
    public function setName(string $name) : static
    {
        if (!$this->name) {
            $this->name = $name;
        }
        
        return $this;
    }
    
    
    /**
     * 获取数据过滤方式
     * @return callable|string|null
     */
    public function getFilter() : callable|string|null
    {
        return $this->filter;
    }
    
    
    /**
     * 数字格式化选项
     * @return string
     */
    public function getNumberFormat() : string
    {
        return $this->numberFormat;
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