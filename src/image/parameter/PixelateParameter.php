<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

/**
 * 图片像素化参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/12 6:45 PM PixelateParameter.php $
 */
class PixelateParameter extends BaseParameter
{
    protected static $parameterName = '像素化';
    
    /** @var int */
    private $pixelate;
    
    
    public function __construct(int $pixelate)
    {
        $this->pixelate = $pixelate;
    }
    
    
    /**
     * 获取像素化值
     * @return int
     */
    public function getPixelate() : int
    {
        return $this->pixelate;
    }
    
    
    /**
     * 设置像素化值
     * @param int $pixelate
     * @return PixelateParameter
     */
    public function setPixelate(int $pixelate) : self
    {
        $this->pixelate = $pixelate;
        
        return $this;
    }
    
    
    public static function __make()
    {
        return new self(0);
    }
}