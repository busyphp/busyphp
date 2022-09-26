<?php
declare(strict_types = 1);

namespace BusyPHP\image\result;

/**
 * PrimaryColorResult
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 2:06 PM PrimaryColorResult.php $
 */
class PrimaryColorResult
{
    /** @var string */
    private $rgb;
    
    
    /**
     * 获取主色
     * @return string
     */
    public function getRgb() : string
    {
        return strtoupper($this->rgb ?: '');
    }
    
    
    /**
     * 设置主色
     * @param string $rgb
     */
    public function setRgb(string $rgb) : void
    {
        $this->rgb = $rgb;
    }
}