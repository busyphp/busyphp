<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

/**
 * 图片伽马校正参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/12 6:41 PM GammaParameter.php $
 */
class GammaParameter extends BaseParameter
{
    protected static $parameterName = '伽马校正';
    
    /** @var int */
    private $gamma;
    
    
    /**
     * @param int $gamma
     */
    public function __construct(int $gamma)
    {
        $this->gamma = $gamma;
    }
    
    
    /**
     * 获取伽马校正参数
     * @return int
     */
    public function getGamma() : int
    {
        return min(max($this->gamma, 0), 100);
    }
    
    
    /**
     * 设置伽马校正参数，范围0-100
     * @param int $gamma
     * @return $this
     */
    public function setGamma(int $gamma) : self
    {
        $this->gamma = $gamma;
        
        return $this;
    }
    
    
    public static function __make()
    {
        return new self(0);
    }
}