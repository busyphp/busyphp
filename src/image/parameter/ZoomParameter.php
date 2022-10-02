<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\image\parameter\concern\ColorConcern;
use BusyPHP\image\parameter\concern\WidthHeightConcern;

/**
 * 缩放参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 2:44 PM ZoomParameter.php $
 * @method getColor() 获取填充颜色
 * @method setColor(string $color) 设置填充颜色
 */
class ZoomParameter extends BaseParameter
{
    protected static $parameterName = '缩放';
    
    /** @var int 保持比例缩放 */
    const TYPE_DEFAULT = 0;
    
    /** @var int 保持比例并缩放到指定尺寸矩形内 */
    const TYPE_FILL = 1;
    
    /** @var int 忽略比例缩放到指定尺寸 */
    const TYPE_LOSE = 2;
    
    use WidthHeightConcern;
    use ColorConcern;
    
    /** @var bool */
    private $enlarge = false;
    
    /** @var int */
    private $type;
    
    
    /**
     * @param int $width 宽
     * @param int $height 高
     * @param int $type 缩放方式
     */
    public function __construct(int $width = 0, int $height = 0, int $type = self::TYPE_DEFAULT)
    {
        $this->setWidth($width);
        $this->setHeight($height);
        $this->setType($type);
    }
    
    
    /**
     * 小图不够是否放大
     * @return bool
     */
    public function isEnlarge() : bool
    {
        return $this->enlarge;
    }
    
    
    /**
     * 设置小图不够是否放大，类型为 {@see ZoomParameter::TYPE_DEFAULT} 有效
     * @param bool $enlarge
     * @return $this
     */
    public function setEnlarge(bool $enlarge) : self
    {
        $this->enlarge = $enlarge;
        
        return $this;
    }
    
    
    /**
     * 获取缩放方式
     * @return int
     */
    public function getType() : int
    {
        return $this->type;
    }
    
    
    /**
     * 设置缩放方式
     * @param int $type
     * @return $this
     */
    public function setType(int $type) : self
    {
        $this->type = $type;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function verification()
    {
        if (($this->type == self::TYPE_FILL || $this->type == self::TYPE_LOSE) && ($this->width <= 0 || $this->height <= 0)) {
            throw new ParamInvalidException('zoom width and height');
        }
        
        if ($this->width <= 0 && $this->height <= 0) {
            throw new ParamInvalidException('zoom width or height');
        }
    }
    
    
    /**
     * 获取缩放方式集合
     * @param int|null $type
     * @return array|string
     */
    public static function getTypes(?int $type = null)
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstMap(self::class, 'TYPE_', [], ClassHelper::ATTR_NAME), $type);
    }
}