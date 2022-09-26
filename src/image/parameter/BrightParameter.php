<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

/**
 * 图片亮度参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 3:40 PM BrightParameter.php $
 */
class BrightParameter extends BaseParameter
{
    protected static $parameterName = '亮度';
    
    /** @var int */
    private $bright;
    
    
    /**
     * @param int $bright 亮度，范围-100至100
     */
    public function __construct(int $bright)
    {
        $this->bright = $bright;
    }
    
    
    /**
     * 获取亮度
     * @return int
     */
    public function getBright() : int
    {
        return min(max($this->bright, -100), 100);
    }
    
    
    /**
     * 设置亮度，范围-100至100
     * @param int $bright
     * @return $this
     */
    public function setBright(int $bright) : self
    {
        $this->bright = $bright;
        
        return $this;
    }
    
    
    public static function __make()
    {
        return new self(0);
    }
}