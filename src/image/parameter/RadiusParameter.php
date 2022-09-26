<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

use BusyPHP\exception\ParamInvalidException;

/**
 * 圆角裁剪参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 3:18 PM RadiusParameter.php $
 */
class RadiusParameter extends BaseParameter
{
    protected static $parameterName = '圆角裁剪';
    
    /** @var bool */
    private $inside;
    
    /** @var int */
    private $rx;
    
    /** @var int */
    private $ry;
    
    
    /**
     * @param int  $rx X轴圆角半径
     * @param int  $ry Y轴圆角半径
     * @param bool $inside 是否内切圆角
     */
    public function __construct(int $rx, int $ry, bool $inside = false)
    {
        $this->rx     = $rx;
        $this->ry     = $ry;
        $this->inside = $inside;
    }
    
    
    /**
     * 获取圆角半径
     * @return int
     */
    public function getRadius() : int
    {
        if ($this->rx > 0) {
            return $this->rx;
        }
        
        return $this->ry ?: 0;
    }
    
    
    /**
     * 是否内切圆角
     * @return bool
     */
    public function isInside() : bool
    {
        return $this->inside;
    }
    
    
    /**
     * 设置是否内切圆角
     * @param bool $inside
     * @return $this
     */
    public function setInside(bool $inside) : self
    {
        $this->inside = $inside;
        
        return $this;
    }
    
    
    /**
     * 获取X轴圆角
     * @return int
     */
    public function getRx() : int
    {
        return $this->rx > 0 ? $this->rx : $this->ry;
    }
    
    
    /**
     * 设置X轴圆角
     * @param int $rx
     * @return $this
     */
    public function setRx(int $rx) : self
    {
        $this->rx = $rx;
        
        return $this;
    }
    
    
    /**
     * 获取Y轴圆角
     * @return int
     */
    public function getRy() : int
    {
        return $this->ry > 0 ? $this->ry : $this->rx;
    }
    
    
    /**
     * 设置Y轴圆角
     * @param int $ry
     * @return $this
     */
    public function setRy(int $ry) : self
    {
        $this->ry = $ry;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function verification()
    {
        if ($this->rx <= 0 && $this->ry <= 0) {
            throw new ParamInvalidException('radius x or y');
        }
    }
    
    
    public static function __make()
    {
        return new self(0, 0);
    }
}