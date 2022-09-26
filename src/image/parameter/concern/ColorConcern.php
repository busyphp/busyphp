<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter\concern;

use BusyPHP\image\parameter\BaseParameter;

/**
 * 颜色
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 2:49 PM ColorConcern.php $
 */
trait ColorConcern
{
    /** @var string */
    private $color = '';
    
    
    /**
     * 获取颜色
     * @return string
     */
    public function getColor() : string
    {
        return $this->color ?: BaseParameter::DEFAULT_COLOR;
    }
    
    
    /**
     * 设置颜色
     * @param string $color
     * @return $this
     */
    public function setColor(string $color) : self
    {
        $this->color = $color;
        
        return $this;
    }
}