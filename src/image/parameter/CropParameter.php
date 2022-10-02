<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\image\parameter\concern\DxDyConcern;
use BusyPHP\image\parameter\concern\WidthHeightConcern;

/**
 * 缩放裁剪参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 2:32 PM CropParameter.php $
 */
class CropParameter extends BaseParameter
{
    use WidthHeightConcern;
    use DxDyConcern;
    
    /** @var int 缩放裁剪 */
    const TYPE_CROP = 0;
    
    /** @var int 普通裁剪 */
    const TYPE_CUT = 1;
    
    protected static $parameterName = '裁剪';
    
    /** @var int */
    private $type;
    
    
    /**
     * @param int $width 宽
     * @param int $height 高
     */
    public function __construct(int $width = 0, int $height = 0, int $type = self::TYPE_CROP)
    {
        $this->setWidth($width);
        $this->setHeight($height);
        $this->setType($type);
    }
    
    
    /**
     * 设置裁剪类型
     * @param int $type
     * @return $this
     */
    public function setType(int $type) : self
    {
        $this->type = $type;
        
        return $this;
    }
    
    
    /**
     * 获取缩放类似
     * @return int
     */
    public function getType() : int
    {
        return $this->type;
    }
    
    
    /**
     * @inheritDoc
     */
    public function verification()
    {
        if ($this->width <= 0 && $this->height <= 0) {
            throw new ParamInvalidException('crop width or height');
        }
    }
    
    
    /**
     * 获取支持的方式集合
     * @param int|null $type
     * @return array|string
     */
    public static function getTypes(?int $type = null)
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstMap(self::class, 'TYPE_', ClassHelper::ATTR_NAME), $type);
    }
}