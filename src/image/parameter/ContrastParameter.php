<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

/**
 * 对比度参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 3:41 PM ContrastParameter.php $
 */
class ContrastParameter extends BaseParameter
{
    protected static $parameterName = '对比度';
    
    /** @var int */
    private $contrast;
    
    
    /**
     * @param int $contrast 对比度，范围-100至100
     */
    public function __construct(int $contrast)
    {
        $this->contrast = $contrast;
    }
    
    
    /**
     * 获取对比度
     * @return int
     */
    public function getContrast() : int
    {
        return min(max($this->contrast, -100), 100);
    }
    
    
    /**
     * 设置对比度，范围-100至100
     * @param int $contrast
     * @return $this
     */
    public function setContrast(int $contrast) : self
    {
        $this->contrast = $contrast;
        
        return $this;
    }
    
    
    public static function __make()
    {
        return new self(0);
    }
}