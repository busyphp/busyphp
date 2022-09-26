<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

/**
 * 图片渐进显示参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/15 4:41 PM InterlaceParameter.php $
 */
class InterlaceParameter extends BaseParameter
{
    protected static $parameterName = '渐进显示';
    
    /** @var bool */
    private $interlace;
    
    
    public function __construct(bool $interlace = true)
    {
        $this->interlace = $interlace;
    }
    
    
    /**
     * @param bool $interlace
     * @return $this
     */
    public function setInterlace(bool $interlace) : self
    {
        $this->interlace = $interlace;
        
        return $this;
    }
    
    
    /**
     * @return bool
     */
    public function isInterlace() : bool
    {
        return $this->interlace;
    }
    
    
    public static function __make()
    {
        return new self(false);
    }
}