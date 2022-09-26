<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

/**
 * 图片翻转颜色参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/12 6:35 PM InvertParameter.php $
 */
class InvertParameter extends BaseParameter
{
    protected static $parameterName = '颜色反转';
    
    /** @var bool */
    private $invert;
    
    
    public function __construct(bool $invert = true)
    {
        $this->invert = $invert;
    }
    
    
    /**
     * @param bool $invert
     * @return $this
     */
    public function setInvert(bool $invert) : self
    {
        $this->invert = $invert;
        
        return $this;
    }
    
    
    /**
     * @return bool
     */
    public function isInvert() : bool
    {
        return $this->invert;
    }
    
    
    public static function __make()
    {
        return new self(false);
    }
}