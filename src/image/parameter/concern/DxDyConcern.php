<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter\concern;

/**
 * X轴Y轴偏移
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 2:43 PM DxDyConcern.php $
 */
trait DxDyConcern
{
    /** @var int */
    private $dx = 0;
    
    /** @var int */
    private $dy = 0;
    
    
    /**
     * 获取X轴偏移
     * @return int
     */
    public function getDx() : int
    {
        return $this->dx;
    }
    
    
    /**
     * 设置X轴偏移
     * @param int $dx
     * @return $this
     */
    public function setDx(int $dx) : self
    {
        $this->dx = $dx;
        
        return $this;
    }
    
    
    /**
     * 获取Y轴偏移
     * @return int
     */
    public function getDy() : int
    {
        return $this->dy;
    }
    
    
    /**
     * 设置Y轴偏移
     * @param int $dy
     * @return $this
     */
    public function setDy(int $dy) : self
    {
        $this->dy = $dy;
        
        return $this;
    }
}