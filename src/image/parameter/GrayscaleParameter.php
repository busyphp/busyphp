<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

/**
 * 图片灰度处理参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 3:49 PM GrayscaleParameter.php $
 */
class GrayscaleParameter extends BaseParameter
{
    protected static $parameterName = '灰度图';
    
    /** @var bool */
    private $grayscale;
    
    
    public function __construct(bool $grayscale = true)
    {
        $this->grayscale = $grayscale;
    }
    
    
    /**
     * @return bool
     */
    public function isGrayscale() : bool
    {
        return $this->grayscale;
    }
    
    
    /**
     * @param bool $grayscale
     * @return $this
     */
    public function setGrayscale(bool $grayscale) : self
    {
        $this->grayscale = $grayscale;
        
        return $this;
    }
    
    
    public static function __make()
    {
        return new self(false);
    }
}