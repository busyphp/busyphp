<?php
declare(strict_types = 1);

namespace BusyPHP\image\traits;

use BusyPHP\image\parameter\BaseParameter;

/**
 * 位置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 2:41 PM Gravity.php $
 */
trait Gravity
{
    /** @var string */
    private $gravity = '';
    
    
    /**
     * 获取位置
     * @return string
     */
    public function getGravity() : string
    {
        return strtoupper($this->gravity ?: BaseParameter::GRAVITY_TOP_LEFT);
    }
    
    
    /**
     * 设置位置
     * @param string $gravity
     * @return static
     */
    public function setGravity(string $gravity) : static
    {
        $this->gravity = $gravity;
        
        return $this;
    }
}