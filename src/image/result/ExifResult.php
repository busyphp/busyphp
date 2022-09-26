<?php
declare(strict_types = 1);

namespace BusyPHP\image\result;

use BusyPHP\image\Driver;

/**
 * ExifResult
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 1:38 PM ExifResult.php $
 */
class ExifResult
{
    /** @var mixed */
    private $raw;
    
    /** @var Driver */
    private $fromDriver;
    
    /** @var int */
    private $orientation;
    
    /** @var string */
    private $xResolution;
    
    /** @var string */
    private $yResolution;
    
    
    public function __construct(Driver $fromDriver, $raw)
    {
        $this->fromDriver = $fromDriver;
        $this->raw        = $raw;
    }
    
    
    /**
     * 获取源数据
     * @return mixed
     */
    public function getRaw()
    {
        return $this->raw;
    }
    
    
    /**
     * 获取驱动
     * @return Driver
     */
    public function getFromDriver() : Driver
    {
        return $this->fromDriver;
    }
    
    
    /**
     * 获取图片方向
     * @return int 返回0则无法获取
     */
    public function getOrientation() : int
    {
        return $this->orientation ?: 0;
    }
    
    
    /**
     * 设置图片方向
     * @param int $orientation
     */
    public function setOrientation(int $orientation) : void
    {
        $this->orientation = $orientation;
    }
    
    
    /**
     * 获取X轴分辨率
     * @return string
     */
    public function getXResolution() : string
    {
        return $this->xResolution ?: '';
    }
    
    
    /**
     * 设置X轴分辨率
     * @param string $xResolution
     */
    public function setXResolution(string $xResolution) : void
    {
        $this->xResolution = $xResolution;
    }
    
    
    /**
     * 获取Y轴分辨率
     * @return string
     */
    public function getYResolution() : string
    {
        return $this->yResolution ?: '';
    }
    
    
    /**
     * 设置Y轴分辨率
     * @param string $yResolution
     */
    public function setYResolution(string $yResolution) : void
    {
        $this->yResolution = $yResolution;
    }
}