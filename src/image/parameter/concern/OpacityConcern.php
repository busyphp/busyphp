<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter\concern;

/**
 * 透明度
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 3:53 PM OpacityConcern.php $
 */
trait OpacityConcern
{
    /** @var int */
    private $opacity = 100;
    
    
    /**
     * 获取透明度，范围0-100
     * @return int
     */
    public function getOpacity() : int
    {
        return min(max($this->opacity, 0), 100);
    }
    
    
    /**
     * 设置透明度
     * @param int $opacity
     * @return $this
     */
    public function setOpacity(int $opacity) : self
    {
        $this->opacity = $opacity;
        
        return $this;
    }
}