<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\image\traits\Color;
use BusyPHP\image\traits\DxDy;
use BusyPHP\image\traits\Gravity;
use BusyPHP\image\traits\Opacity;
use BusyPHP\image\traits\Rotate;

/**
 * 图片处理系统添加文字模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/9 7:39 PM TextWatermarkTemplate.php $
 */
class TextParameter extends BaseParameter
{
    use DxDy;
    use Gravity;
    use Rotate;
    use Color;
    use Opacity;
    
    protected static $parameterName = '文字水印';
    
    /**
     * 文字
     * @var string
     */
    private $text;
    
    /**
     * 文字大小(单位为磅)
     * @var int
     */
    private $fontsize = 0;
    
    /**
     * 文字字体
     * @var string
     */
    private $font = '';
    
    /**
     * 是否铺满
     * @var bool
     */
    private $overspread = false;
    
    /**
     * 文字阴影透明度
     * @var int
     */
    private $shadow = 0;
    
    
    /**
     * 构造器
     * @param string $text 文本
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }
    
    
    /**
     * 是否铺满
     * @return bool
     */
    public function isOverspread() : bool
    {
        return $this->overspread ?: false;
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
     * 获取文字
     * @return string
     */
    public function getText() : string
    {
        return $this->text ?: '';
    }
    
    
    /**
     * 设置文字
     * @param string $text
     * @return $this
     */
    public function setText(string $text) : self
    {
        $this->text = $text;
        
        return $this;
    }
    
    
    /**
     * 设置文字大小
     * @return int
     */
    public function getFontsize() : int
    {
        return $this->fontsize <= 0 ? 13 : $this->fontsize;
    }
    
    
    /**
     * 获取文字大小
     * @param int $fontsize
     * @return $this
     */
    public function setFontsize(int $fontsize) : self
    {
        $this->fontsize = $fontsize;
        
        return $this;
    }
    
    
    /**
     * 获取字体
     * @return string
     */
    public function getFont() : string
    {
        return $this->font;
    }
    
    
    /**
     * 设置字体
     * @param string $font
     * @return $this
     */
    public function setFont(string $font) : self
    {
        $this->font = $font;
        
        return $this;
    }
    
    
    /**
     * 获取文字阴影透明度
     * @return int
     */
    public function getShadow() : int
    {
        return min(max($this->shadow, 0), 100);
    }
    
    
    /**
     * 设置文字阴影透明度，范围0-100
     * @param int $shadow
     * @return $this
     */
    public function setShadow(int $shadow) : self
    {
        $this->shadow = $shadow;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function verification()
    {
        if (!$this->text) {
            throw new ParamInvalidException('text');
        }
    }
    
    
    public static function __make()
    {
        return new self('');
    }
}