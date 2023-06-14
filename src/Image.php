<?php
declare(strict_types = 1);

namespace BusyPHP;

use BusyPHP\helper\FilesystemHelper;
use BusyPHP\image\Driver;
use BusyPHP\image\parameter\AutoOrientParameter;
use BusyPHP\image\parameter\BaseParameter;
use BusyPHP\image\parameter\BlurParameter;
use BusyPHP\image\parameter\BrightParameter;
use BusyPHP\image\parameter\ContrastParameter;
use BusyPHP\image\parameter\CropParameter;
use BusyPHP\image\parameter\FlipParameter;
use BusyPHP\image\parameter\FormatParameter;
use BusyPHP\image\parameter\GammaParameter;
use BusyPHP\image\parameter\GrayscaleParameter;
use BusyPHP\image\parameter\ImageParameter;
use BusyPHP\image\parameter\InterlaceParameter;
use BusyPHP\image\parameter\InvertParameter;
use BusyPHP\image\parameter\PixelateParameter;
use BusyPHP\image\parameter\QualityParameter;
use BusyPHP\image\parameter\RadiusParameter;
use BusyPHP\image\parameter\RotateParameter;
use BusyPHP\image\parameter\SharpenParameter;
use BusyPHP\image\parameter\StripMetaParameter;
use BusyPHP\image\parameter\TextParameter;
use BusyPHP\image\parameter\ZoomParameter;
use BusyPHP\image\result\ProcessResult;
use BusyPHP\image\result\SaveResult;
use ReflectionException;
use think\facade\Filesystem;
use think\filesystem\Driver as FilesystemDriver;
use think\Response;
use think\route\Url;

/**
 * 图片处理类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/10 2:18 PM Image.php $
 */
class Image
{
    /**
     * 要处理的图片路径
     * @var string
     */
    protected string $path;
    
    /**
     * 要处理的图片参数
     * @var BaseParameter[]
     */
    protected array $parameters = [];
    
    /**
     * 图片样式
     * @var string
     */
    protected string $style = '';
    
    /**
     * 是否下载
     * @var bool
     */
    protected bool $download = false;
    
    /**
     * 缓存时长
     * @var int
     */
    protected int $lifetime = 0;
    
    /**
     * 下载文件名
     * @var string
     */
    protected string $filename = '';
    
    /**
     * 磁盘系统
     * @var FilesystemDriver|null
     */
    protected ?FilesystemDriver $disk = null;
    
    
    /**
     * 构造函数
     * @param string $path 图片路径
     */
    public function __construct(string $path = '')
    {
        $this->path($path);
    }
    
    
    public function __sleep() : array
    {
        return ['path', 'parameters', 'style', 'download', 'lifetime', 'filename'];
    }
    
    
    /**
     * 设置图片路径
     * @param string $path
     * @return static
     */
    public function path(string $path) : static
    {
        $this->path = $path;
        
        return $this;
    }
    
    
    /**
     * 获取被处理的图片路径
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }
    
    
    /**
     * 添加参数模板
     * @param BaseParameter $parameter
     * @return static
     */
    public function add(BaseParameter $parameter) : static
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
     * @return static
     */
    public function crop(int $width, int $height) : static
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
     * @return static
     */
    public function cut(int $width, int $height, int $dx = 0, int $dy = 0) : static
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
     * @return static
     */
    public function zoom(int $width, int $height, bool $enlarge = false) : static
    {
        $zoom = new ZoomParameter($width, $height, ZoomParameter::TYPE_DEFAULT);
        $zoom->setEnlarge($enlarge);
        
        return $this->add($zoom);
    }
    
    
    /**
     * 不保持比例强制缩放
     * @param int $width 宽
     * @param int $height 高
     * @return static
     */
    public function zoomLose(int $width, int $height) : static
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
     * @return static
     */
    public function zoomFill(int $width, int $height, string $color = BaseParameter::DEFAULT_COLOR) : static
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
     * @return static
     */
    public function radius(int $radius, bool $inside = false) : static
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
     * @return static
     */
    public function radiusXY(int $rx, int $ry, bool $inside = false) : static
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
     * @return static
     */
    public function rotate(int $rotate, string $color = '') : static
    {
        if ($rotate > 0 && $rotate < 360) {
            return $this->add(new RotateParameter($rotate, $color));
        }
        
        return $this;
    }
    
    
    /**
     * 根据原图 EXIF 信息将图片自适应旋转回正
     * @return static
     */
    public function autoOrient() : static
    {
        return $this->add(new AutoOrientParameter());
    }
    
    
    /**
     * 格式转换
     * @param string $format 可为：jpg，bmp，gif，png，webp
     * @return static
     */
    public function format(string $format) : static
    {
        $format = strtolower($format);
        if (in_array($format, array_keys(FormatParameter::getFormatMap()))) {
            return $this->add(new FormatParameter($format));
        }
        
        return $this;
    }
    
    
    /**
     * 质量变换
     * @param int $quality 取值范围0 - 100
     * @param int $type 质量类型
     * @return static
     */
    public function quality(int $quality, int $type = QualityParameter::TYPE_ABSOLUTE) : static
    {
        if ($quality > 0 && $quality <= 100) {
            return $this->add(new QualityParameter($quality, $type));
        }
        
        return $this;
    }
    
    
    /**
     * 渐进显示
     * @return static
     */
    public function interlace() : static
    {
        return $this->add(new InterlaceParameter());
    }
    
    
    /**
     * 高斯模糊
     * @param int $radius 模糊半径，范围0-100
     * @param int $sigma 正态分布的标准差，范围0-100
     * @return static
     */
    public function blur(int $radius, int $sigma) : static
    {
        if ($radius > 0 && $radius <= 100) {
            return $this->add(new BlurParameter($radius, $sigma));
        }
        
        return $this;
    }
    
    
    /**
     * 伽马校正，范围0-100
     * @param int $gamma
     * @return static
     */
    public function gamma(int $gamma) : static
    {
        if ($gamma >= -100 && $gamma <= 100 && $gamma != 0) {
            return $this->add(new GammaParameter($gamma));
        }
        
        return $this;
    }
    
    
    /**
     * 像素化，0以上
     * @param int $pixelate
     * @return static
     */
    public function pixelate(int $pixelate) : static
    {
        if ($pixelate > 0) {
            return $this->add(new PixelateParameter($pixelate));
        }
        
        return $this;
    }
    
    
    /**
     * 翻转颜色
     * @return static
     */
    public function invert() : static
    {
        return $this->add(new InvertParameter());
    }
    
    
    /**
     * 图片反转
     * @param string $flip
     * @return static
     */
    public function flip(string $flip) : static
    {
        if (in_array($flip, array_keys(FlipParameter::getFlipMap()))) {
            return $this->add(new FlipParameter($flip));
        }
        
        return $this;
    }
    
    
    /**
     * 亮度
     * @param int $bright 亮度，范围-100至100
     * @return static
     */
    public function bright(int $bright) : static
    {
        if ($bright >= -100 && $bright <= 100 && $bright != 0) {
            return $this->add(new BrightParameter($bright));
        }
        
        return $this;
    }
    
    
    /**
     * 对比度
     * @param int $contrast 对比度，范围-100至100
     * @return static
     */
    public function contrast(int $contrast) : static
    {
        if ($contrast >= -100 && $contrast <= 100 && $contrast != 0) {
            return $this->add(new ContrastParameter($contrast));
        }
        
        return $this;
    }
    
    
    /**
     * 锐化
     * @param int $sharpen 锐化值，范围0-100
     * @return static
     */
    public function sharpen(int $sharpen) : static
    {
        if ($sharpen >= -100 && $sharpen <= 100 && $sharpen != 0) {
            return $this->add(new SharpenParameter($sharpen));
        }
        
        return $this;
    }
    
    
    /**
     * 灰度图
     * @return static
     */
    public function grayscale() : static
    {
        return $this->add(new GrayscaleParameter());
    }
    
    
    /**
     * 添加指定图片到被处理的图片中
     * @param ImageParameter $parameter
     * @return static
     */
    public function image(ImageParameter $parameter) : static
    {
        if ($parameter->getImage()) {
            return $this->add($parameter);
        }
        
        return $this;
    }
    
    
    /**
     * 添加指定文字到被处理的图片中
     * @param TextParameter $parameter
     * @return static
     */
    public function text(TextParameter $parameter) : static
    {
        if ($parameter->getText()) {
            return $this->add($parameter);
        }
        
        return $this;
    }
    
    
    /**
     * 去除图片元信息(含EXIF信息)
     * @return static
     */
    public function stripMeta() : static
    {
        return $this->add(new StripMetaParameter());
    }
    
    
    /**
     * 设置图片处理样式
     * @param string $style
     * @return static
     */
    public function style(string $style) : static
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
                break;
            }
        }
        
        return $format ?: pathinfo($this->getPath(), PATHINFO_EXTENSION);
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
     * 设置是否下载 与 {@see Image::cache()} 互斥
     * @param string $filename 文件名
     * @return static
     */
    public function download(string $filename = '') : static
    {
        $this->download = true;
        $this->lifetime = 0;
        $this->filename = $filename;
        
        return $this;
    }
    
    
    /**
     * 设置缓存多少秒 与 {@see Image::download()} 互斥
     * @param int $lifetime 过期秒数
     * @return static
     */
    public function cache(int $lifetime) : static
    {
        $this->download = false;
        $this->lifetime = $lifetime;
        
        return $this;
    }
    
    
    /**
     * 是否下载图片
     * @return bool
     */
    public function isDownload() : bool
    {
        return $this->download;
    }
    
    
    /**
     * 下载的图片名称
     * @return string
     */
    public function getFilename() : string
    {
        return $this->filename;
    }
    
    
    /**
     * 获取缓存秒数
     * @return int
     */
    public function getLifetime() : int
    {
        return max($this->lifetime, 0);
    }
    
    
    /**
     * 指定磁盘
     * @param string|FilesystemDriver $disk
     * @return $this
     */
    public function disk(string|FilesystemDriver $disk) : static
    {
        if ($disk instanceof FilesystemDriver) {
            $this->disk = $disk;
        } else {
            $this->disk = Filesystem::disk($disk);
        }
        
        return $this;
    }
    
    
    /**
     * 获取图片处理驱动
     * @return Driver
     */
    public function getDriver() : Driver
    {
        if (!$this->disk) {
            foreach (Filesystem::getConfig('disks') as $disk => $config) {
                $disk = Filesystem::disk($disk);
                if (null !== $path = $disk->matchRelativePathByURL($this->path)) {
                    $this->path($path);
                    $this->disk = $disk;
                    break;
                }
            }
        }
        if (!$this->disk) {
            $this->disk = FilesystemHelper::public();
        }
        
        return $this->disk->image();
    }
    
    
    /**
     * 处理图片
     * @return ProcessResult
     */
    public function process() : ProcessResult
    {
        return $this->getDriver()->process($this);
    }
    
    
    /**
     * 处理并保存
     * @param string $destination 保存的图片路径，留空覆盖原图
     * @return SaveResult
     */
    public function save(string $destination = '') : SaveResult
    {
        return $this->getDriver()->save($this, $destination);
    }
    
    
    /**
     * 处理并响应
     * @return Response
     */
    public function response() : Response
    {
        return $this->getDriver()->response($this);
    }
    
    
    /**
     * 生成在线处理URL
     * @return Url
     */
    public function url() : Url
    {
        return $this->getDriver()->url($this);
    }
    
    
    /**
     * 获取支持的参数模版结构
     * @return array<string, array{key: string, name: string, attr: array<string,mixed>}>
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