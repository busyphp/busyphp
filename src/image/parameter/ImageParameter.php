<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\image\parameter\concern\DxDyConcern;
use BusyPHP\image\parameter\concern\GravityConcern;
use BusyPHP\image\parameter\concern\OpacityConcern;
use BusyPHP\image\parameter\concern\RotateConcern;

/**
 * 图片处理系统添加图片模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/9 7:40 PM ImageWatermarkTemplate.php $
 */
class ImageParameter extends BaseParameter
{
    use DxDyConcern;
    use RotateConcern;
    use GravityConcern;
    use OpacityConcern;
    
    protected static $parameterName = '图片水印';
    
    /**
     * 图片路径
     * @var string
     */
    private $image;
    
    /**
     * 是否铺满
     * @var bool
     */
    private $overspread = false;
    
    
    /**
     * 构造器
     * @param string $image 图片路径
     */
    public function __construct(string $image)
    {
        $this->image = $image;
    }
    
    
    /**
     * 获取图片路径
     * @return string
     */
    public function getImage() : string
    {
        return $this->image ?: '';
    }
    
    
    /**
     * 设置图片路径
     * @param string $image
     * @return $this
     */
    public function setImage(string $image) : self
    {
        $this->image = $image;
        
        return $this;
    }
    
    
    /**
     * 是否铺满
     * @return bool
     */
    public function isOverspread() : bool
    {
        return $this->overspread;
    }
    
    
    /**
     * 设置是否铺满
     * @param bool $overspread
     * @return $this
     */
    public function setOverspread(bool $overspread) : self
    {
        $this->overspread = $overspread;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function verification()
    {
        if (!$this->image) {
            throw new ParamInvalidException('image');
        }
    }
    
    
    public static function __make()
    {
        return new self('');
    }
}