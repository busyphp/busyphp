<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

use InvalidArgumentException;
use ReflectionException;

/**
 * 图片处理参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 2:18 PM ProcessParameter.php $
 */
class ProcessParameter
{
    /** @var string */
    private $oldPath;
    
    /** @var string */
    private $newPath;
    
    /** @var BaseParameter[] */
    private $parameters = [];
    
    /** @var string */
    private $style = '';
    
    
    /**
     * 构造器
     * @param string $oldPath 处理的图片路径
     * @param string $newPath 保存的图片路径
     */
    public function __construct(string $oldPath = '', string $newPath = '')
    {
        $this->oldPath = $oldPath;
        $this->newPath = $newPath ?: $oldPath;
    }
    
    
    /**
     * 获取被处理的图片路径
     * @return string
     */
    public function getOldPath() : string
    {
        if (!$this->oldPath) {
            throw new InvalidArgumentException('处理图片路径为空');
        }
        
        return $this->oldPath;
    }
    
    
    /**
     * 获取处理后的图片路径
     * @return string
     */
    public function getNewPath() : string
    {
        return $this->newPath;
    }
    
    
    /**
     * 添加参数模板
     * @param BaseParameter $parameter
     * @return $this
     */
    public function add(BaseParameter $parameter) : self
    {
        $this->parameters[] = $parameter;
        
        return $this;
    }
    
    
    /**
     * 获取参数模板集合
     * @return BaseParameter[]
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }
    
    
    /**
     * 指定尺寸进行缩放裁剪某一边可能会被裁掉
     * @param int $width 宽
     * @param int $height 高
     * @return $this
     */
    public function crop(int $width, int $height) : self
    {
        if ($width > 0 || $height > 0) {
            return $this->add(new CropParameter($width, $height, CropParameter::TYPE_CROP));
        }
        
        return $this;
    }
    
    
    /**
     * 指定尺寸进行普通裁剪
     * @param int $width 宽
     * @param int $height 高
     * @param int $dx X轴偏移
     * @param int $dy Y轴偏移
     * @return $this
     */
    public function cut(int $width, int $height, int $dx = 0, int $dy = 0) : self
    {
        if ($width > 0 || $height > 0) {
            $crop = new CropParameter($width, $height, CropParameter::TYPE_CUT);
            $crop->setDx($dx);
            $crop->setDy($dy);
            
            return $this->add($crop);
        }
        
        return $this;
    }
    
    
    /**
     * 按照尺寸进行缩放，某一项为0则自动计算
     * @param int  $width 宽
     * @param int  $height 高
     * @param bool $enlarge 小图不够是否放大
     * @return $this
     */
    public function zoom(int $width, int $height, bool $enlarge = false) : self
    {
        $zoom = new ZoomParameter($width, $height, ZoomParameter::TYPE_DEFAULT);
        $zoom->setEnlarge($enlarge);
        
        return $this->add($zoom);
    }
    
    
    /**
     * 不保持比例强制缩放
     * @param int $width 宽
     * @param int $height 高
     * @return $this
     */
    public function zoomLose(int $width, int $height) : self
    {
        if ($width > 0 && $height > 0) {
            return $this->add(new ZoomParameter($width, $height, ZoomParameter::TYPE_LOSE));
        }
        
        return $this;
    }
    
    
    /**
     * 缩放为指定宽高矩形内的最大图片
     * @param int    $width 宽
     * @param int    $height 高
     * @param string $color 填充颜色(默认为#FFFFFF)
     * @return $this
     */
    public function zoomFill(int $width, int $height, string $color = BaseParameter::DEFAULT_COLOR) : self
    {
        if ($width > 0 && $height > 0) {
            $zoom = new ZoomParameter($width, $height, ZoomParameter::TYPE_FILL);
            $zoom->setColor($color);
            
            return $this->add($zoom);
        }
        
        return $this;
    }
    
    
    /**
     * 圆角裁剪
     * @param int  $radius 圆角半径
     * @param bool $inside 是否按内圆裁剪
     * @return $this
     */
    public function radius(int $radius, bool $inside = false) : self
    {
        if ($radius > 0) {
            return $this->add(new RadiusParameter($radius, $radius, $inside));
        }
        
        return $this;
    }
    
    
    /**
     * 圆角裁剪
     * @param int  $rx
     * @param int  $ry
     * @param bool $inside
     * @return $this
     */
    public function radiusXY(int $rx, int $ry, bool $inside = false) : self
    {
        if ($rx > 0 || $ry > 0) {
            return $this->add(new RadiusParameter($rx, $ry, $inside));
        }
        
        return $this;
    }
    
    
    /**
     * 图像旋转
     * @param int    $rotate 图片顺时针旋转角度，取值范围0 - 360，默认不旋转
     * @param string $color 背景色
     * @return $this
     */
    public function rotate(int $rotate, string $color = '') : self
    {
        if ($rotate > 0 && $rotate < 360) {
            return $this->add(new RotateParameter($rotate, $color));
        }
        
        return $this;
    }
    
    
    /**
     * 根据原图 EXIF 信息将图片自适应旋转回正
     * @return $this
     */
    public function autoOrient() : self
    {
        return $this->add(new AutoOrientParameter());
    }
    
    
    /**
     * 格式转换
     * @param string $format 可为：jpg，bmp，gif，png，webp
     * @return $this
     */
    public function format(string $format) : self
    {
        $format = strtolower($format);
        if (in_array($format, array_keys(FormatParameter::getFormats()))) {
            return $this->add(new FormatParameter($format));
        }
        
        return $this;
    }
    
    
    /**
     * 质量变换
     * @param int $quality 取值范围0 - 100
     * @param int $type 质量类型
     * @return $this
     */
    public function quality(int $quality, int $type = QualityParameter::TYPE_ABSOLUTE) : self
    {
        if ($quality > 0 && $quality <= 100) {
            return $this->add(new QualityParameter($quality, $type));
        }
        
        return $this;
    }
    
    
    /**
     * 渐进显示
     * @return $this
     */
    public function interlace() : self
    {
        return $this->add(new InterlaceParameter());
    }
    
    
    /**
     * 高斯模糊
     * @param int $radius 模糊半径，范围0-100
     * @param int $sigma 正态分布的标准差，范围0-100
     * @return $this
     */
    public function blur(int $radius, int $sigma) : self
    {
        if ($radius > 0 && $radius <= 100) {
            return $this->add(new BlurParameter($radius, $sigma));
        }
        
        return $this;
    }
    
    
    /**
     * 伽马校正，范围0-100
     * @param float $gamma
     * @return $this
     */
    public function gamma(float $gamma) : self
    {
        if ($gamma >= -100 && $gamma <= 100 && $gamma != 0) {
            return $this->add(new GammaParameter($gamma));
        }
        
        return $this;
    }
    
    
    /**
     * 像素化，0以上
     * @param int $pixelate
     * @return $this
     */
    public function pixelate(int $pixelate) : self
    {
        if ($pixelate > 0) {
            return $this->add(new PixelateParameter($pixelate));
        }
        
        return $this;
    }
    
    
    /**
     * 翻转颜色
     * @return $this
     */
    public function invert() : self
    {
        return $this->add(new InvertParameter());
    }
    
    
    /**
     * 图片反转
     * @param string $flip
     * @return $this
     */
    public function flip(string $flip) : self
    {
        if (in_array($flip, array_keys(FlipParameter::getFlips()))) {
            return $this->add(new FlipParameter($flip));
        }
        
        return $this;
    }
    
    
    /**
     * 亮度
     * @param int $bright 亮度，范围-100至100
     * @return $this
     */
    public function bright(int $bright) : self
    {
        if ($bright >= -100 && $bright <= 100 && $bright != 0) {
            return $this->add(new BrightParameter($bright));
        }
        
        return $this;
    }
    
    
    /**
     * 对比度
     * @param int $contrast 对比度，范围-100至100
     * @return $this
     */
    public function contrast(int $contrast) : self
    {
        if ($contrast >= -100 && $contrast <= 100 && $contrast != 0) {
            return $this->add(new ContrastParameter($contrast));
        }
        
        return $this;
    }
    
    
    /**
     * 锐化
     * @param int $sharpen 锐化值，范围0-100
     * @return $this
     */
    public function sharpen(int $sharpen) : self
    {
        if ($sharpen >= -100 && $sharpen <= 100 && $sharpen != 0) {
            return $this->add(new SharpenParameter($sharpen));
        }
        
        return $this;
    }
    
    
    /**
     * 灰度图
     * @return $this
     */
    public function grayscale() : self
    {
        return $this->add(new GrayscaleParameter());
    }
    
    
    /**
     * 添加指定图片到被处理的图片中
     * @param ImageParameter $parameter
     * @return $this
     */
    public function image(ImageParameter $parameter) : self
    {
        if ($parameter->getImage()) {
            return $this->add($parameter);
        }
        
        return $this;
    }
    
    
    /**
     * 添加指定文字到被处理的图片中
     * @param TextParameter $parameter
     * @return $this
     */
    public function text(TextParameter $parameter) : self
    {
        if ($parameter->getText()) {
            return $this->add($parameter);
        }
        
        return $this;
    }
    
    
    /**
     * 去除图片元信息(含EXIF信息)
     * @return $this
     */
    public function stripMeta() : self
    {
        return $this->add(new StripMetaParameter());
    }
    
    
    /**
     * 设置图片处理样式
     * @param string $style
     * @return $this
     */
    public function style(string $style) : self
    {
        $this->style = $style;
        
        return $this;
    }
    
    
    /**
     * 获取图片格式
     * @return string
     */
    public function getFormat() : string
    {
        $format = null;
        foreach ($this->getParameters() as $item) {
            if ($item instanceof FormatParameter) {
                $format = $item->getFormat();
            }
        }
        
        return $format ?: pathinfo($this->getOldPath(), PATHINFO_EXTENSION);
    }
    
    
    /**
     * 获取图片处理样式
     * @return string
     */
    public function getStyle() : string
    {
        return $this->style;
    }
    
    
    /**
     * 获取支持的参数模版结构
     * @return array
     * @throws ReflectionException
     */
    public static function getParameterStruct() : array
    {
        static $data;
        
        if (!isset($data)) {
            $class = [
                ZoomParameter::class,
                CropParameter::class,
                RadiusParameter::class,
                RotateParameter::class,
                AutoOrientParameter::class,
                FlipParameter::class,
                BrightParameter::class,
                ContrastParameter::class,
                BlurParameter::class,
                SharpenParameter::class,
                GrayscaleParameter::class,
                GammaParameter::class,
                InvertParameter::class,
                PixelateParameter::class,
                InterlaceParameter::class,
                StripMetaParameter::class,
                FormatParameter::class,
                QualityParameter::class,
                ImageParameter::class,
                TextParameter::class,
            ];
            $data  = [];
            foreach ($class as $item) {
                if (is_subclass_of($item, BaseParameter::class)) {
                    $data[$item::getParameterKey()] = [
                        'key'  => $item::getParameterKey(),
                        'name' => $item::getParameterName(),
                        'attr' => $item::getParameterAttrs()
                    ];
                }
            }
        }
        
        return $data;
    }
}