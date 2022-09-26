<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

/**
 * 图片高斯模糊参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 3:37 PM BlurParameter.php $
 */
class BlurParameter extends BaseParameter
{
    protected static $parameterName = '高斯模糊';
    
    /** @var int */
    private $radius;
    
    /** @var int */
    private $sigma;
    
    
    /**
     * @param int $radius 模糊半径，范围0-100
     * @param int $sigma 正态分布的标准差，范围0-100
     */
    public function __construct(int $radius, int $sigma)
    {
        $this->radius = $radius;
        $this->sigma  = $sigma;
    }
    
    
    /**
     * 获取模糊半径
     * @return int
     */
    public function getRadius() : int
    {
        return min(max($this->radius, 0), 100);
    }
    
    
    /**
     * 设置模糊半径，范围0-100
     * @param int $radius
     * @return $this
     */
    public function setRadius(int $radius) : self
    {
        $this->radius = $radius;
        
        return $this;
    }
    
    
    /**
     * 获取正态分布的标准差
     * @return int
     */
    public function getSigma() : int
    {
        return min(max($this->sigma, 0), 100);
    }
    
    
    /**
     * 设置正态分布的标准差，范围0-100
     * @param int $sigma
     * @return $this
     */
    public function setSigma(int $sigma) : self
    {
        $this->sigma = $sigma;
        
        return $this;
    }
    
    
    public static function __make()
    {
        return new self(0, 0);
    }
}