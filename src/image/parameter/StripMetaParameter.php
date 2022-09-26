<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

/**
 * 去除图片元数据(含EXIF)参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 3:51 PM StripMetaParameter.php $
 */
class StripMetaParameter extends BaseParameter
{
    protected static $parameterName = '去除元数据';
    
    /** @var bool */
    private $stripMeta;
    
    
    public function __construct(bool $stripMeta = true)
    {
        $this->stripMeta = $stripMeta;
    }
    
    
    /**
     * @return bool
     */
    public function isStripMeta() : bool
    {
        return $this->stripMeta;
    }
    
    
    /**
     * @param bool $stripMeta
     * @return $this
     */
    public function setStripMeta(bool $stripMeta) : self
    {
        $this->stripMeta = $stripMeta;
        
        return $this;
    }
    
    
    public static function __make()
    {
        return new self(false);
    }
}