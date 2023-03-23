<?php
declare(strict_types = 1);

namespace BusyPHP\image\traits;

/**
 * 旋转角度
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 3:29 PM Rotate.php $
 */
trait Rotate
{
    /** @var int */
    private $rotate = 0;
    
    
    /**
     * 获取旋转角度
     * @return int
     */
    public function getRotate() : int
    {
        return min(max($this->rotate, 0), 360);
    }
    
    
    /**
     * 设置旋转角度，范围0-360
     * @param int $rotate
     * @return static
     */
    public function setRotate(int $rotate) : static
    {
        $this->rotate = $rotate;
        
        return $this;
    }
}