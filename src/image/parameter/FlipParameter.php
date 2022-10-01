<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;

/**
 * 图片反转参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/14 9:08 AM FlipParameter.php $
 */
class FlipParameter extends BaseParameter
{
    /**
     * 水平翻转
     * @var string
     */
    public const FLIP_HORIZONTAL = 'horizontal';
    
    /**
     * 垂直翻转
     * @var string
     */
    public const FLIP_VERTICAL = 'vertical';
    
    protected static $parameterName = '镜像反转';
    
    /** @var string */
    private $flip;
    
    
    /**
     * 构造器
     * @param string $flip 反转方式
     */
    public function __construct(string $flip)
    {
        $this->flip = $flip;
    }
    
    
    /**
     * 获取图像翻转方式
     * @return string
     */
    public function getFlip() : string
    {
        return $this->flip;
    }
    
    
    /**
     * 设置图像翻转方式
     * @param string $flip
     * @return $this
     */
    public function setFlip(string $flip) : self
    {
        $this->flip = $flip;
        
        return $this;
    }
    
    
    /**
     * 获取反转方式集合
     * @param $format
     * @return array|string
     */
    public static function getFlips($format = null)
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstMap(self::class, 'FLIP_', ClassHelper::CONST_MAP_NAME), $format);
    }
    
    
    public static function __make()
    {
        return new self('');
    }
}