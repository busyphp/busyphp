<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter\concern;

/**
 * 宽高
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 2:40 PM WidthHeightConcern.php $
 */
trait WidthHeightConcern
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
     * @return $this
     */
    public function setWidth(int $width) : self
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
     * @return $this
     */
    public function setHeight(int $height) : self
    {
        $this->height = $height;
        
        return $this;
    }
}