<?php
declare(strict_types = 1);

namespace BusyPHP\image\traits;

/**
 * 宽高
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 2:40 PM WidthHeight.php $
 */
trait WidthHeight
{
    /** @var int */
    private $width = 0;
    
    /** @var int */
    private $height = 0;
    
    
    /**
     * 获取宽
     * @return int
     */
    public function getWidth() : int
    {
        return $this->width;
    }
    
    
    /**
     * 设置宽
     * @param int $width
     * @return static
     */
    public function setWidth(int $width) : static
    {
        $this->width = $width;
        
        return $this;
    }
    
    
    /**
     * 获取高
     * @return int
     */
    public function getHeight() : int
    {
        return $this->height;
    }
    
    
    /**
     * 设置高
     * @param int $height
     * @return static
     */
    public function setHeight(int $height) : static
    {
        $this->height = $height;
        
        return $this;
    }
}