<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\ClassHelper;

/**
 * 格式转换参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 3:31 PM FormatParameter.php $
 */
class FormatParameter extends BaseParameter
{
    // +----------------------------------------------------
    // + 格式
    // +----------------------------------------------------
    /**
     * png格式
     * @var string
     */
    const FORMAT_PNG = 'png';
    
    /**
     * gif格式
     * @var string
     */
    const FORMAT_GIF = 'gif';
    
    /**
     * bmp格式
     * @var string
     */
    const FORMAT_BMP = 'bmp';
    
    /**
     * jpeg格式
     * @var string
     */
    const FORMAT_JPEG = 'jpeg';
    
    /**
     * jpg格式
     * @var string
     */
    const FORMAT_JPG = 'jpg';
    
    /**
     * webp格式
     * @var string
     */
    const FORMAT_WEBP = 'webp';
    
    protected static $parameterName = '格式转换';
    
    /** @var string */
    private $format;
    
    
    /**
     * @param string $format 格式
     */
    public function __construct(string $format)
    {
        $this->format = $format;
    }
    
    
    /**
     * 获取格式
     * @return string
     */
    public function getFormat() : string
    {
        return strtolower($this->format);
    }
    
    
    /**
     * 设置格式
     * @param string $format
     * @return self
     */
    public function setFormat(string $format) : self
    {
        $this->format = $format;
        
        return $this;
    }
    
    
    /**
     * 获取支持的格式集合
     * @param $format
     * @return array|string
     */
    public static function getFormats($format = null)
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstAttrs(self::class, 'FORMAT_', ClassHelper::ATTR_NAME), $format);
    }
    
    
    public static function __make()
    {
        return new self('');
    }
}