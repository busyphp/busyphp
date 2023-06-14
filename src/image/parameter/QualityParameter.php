<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;

/**
 * 图片质量参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 3:33 PM QualityParameter.php $
 */
class QualityParameter extends BaseParameter
{
    /** @var int 绝对质量 */
    const TYPE_ABSOLUTE = 0;
    
    /** @var int 相对质量 */
    const TYPE_RELATIVE = 1;
    
    /** @var int 最低质量 */
    const TYPE_MIN = 2;
    
    protected static $parameterName = '质量转换';
    
    /** @var int */
    private $quality;
    
    /** @var int */
    private $type;
    
    
    /**
     * @param int $quality 图形质量，范围0-100
     * @param int $type 是否使用质量类型
     */
    public function __construct(int $quality, int $type = self::TYPE_ABSOLUTE)
    {
        $this->quality = $quality;
        $this->type    = $type;
    }
    
    
    /**
     * 获取图形质量
     * @return int
     */
    public function getQuality() : int
    {
        return min(max($this->quality, 0), 100);
    }
    
    
    /**
     * 设置图形质量，范围0-100
     * @param int $quality
     * @return self
     */
    public function setQuality(int $quality) : self
    {
        $this->quality = $quality;
        
        return $this;
    }
    
    
    /**
     * 获取质量类型
     * @return int
     */
    public function getType() : int
    {
        return $this->type;
    }
    
    
    /**
     * 设置是否使用绝对质量
     * @param int $type
     * @return self
     */
    public function setType(int $type) : self
    {
        $this->type = $type;
        
        return $this;
    }
    
    
    /**
     * 获取支持的质量类型
     * @param int|null $type
     * @return array|string|null
     */
    public static function getTypeMap(int $type = null) : array|string|null
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstAttrs(self::class, 'TYPE_', ClassHelper::ATTR_NAME), $type);
    }
    
    
    public static function __make()
    {
        return new self(90);
    }
}