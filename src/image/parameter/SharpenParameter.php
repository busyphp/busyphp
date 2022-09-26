<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

/**
 * 图片锐化参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 3:43 PM SharpenParameter.php $
 */
class SharpenParameter extends BaseParameter
{
    protected static $parameterName = '锐化';
    
    /** @var int */
    private $sharpen;
    
    
    /**
     * @param int $sharpen 锐化值，范围0-100
     */
    public function __construct(int $sharpen)
    {
        $this->sharpen = $sharpen;
    }
    
    
    /**
     * 获取锐化值
     * @return int
     */
    public function getSharpen() : int
    {
        return min(max($this->sharpen, 0), 100);
    }
    
    
    /**
     * 设置锐化值，范围0-100
     * @param int $sharpen
     * @return $this
     */
    public function setSharpen(int $sharpen) : self
    {
        $this->sharpen = $sharpen;
        
        return $this;
    }
    
    
    public static function __make()
    {
        return new self(0);
    }
}