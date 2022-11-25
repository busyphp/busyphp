<?php
declare(strict_types = 1);

namespace BusyPHP\image\driver;

use BusyPHP\exception\ClassNotImplementsException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\FileHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\image\concern\ResponseConcern;
use BusyPHP\image\Driver;
use BusyPHP\image\driver\local\LocalImageStyleManagerInterface;
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
use BusyPHP\image\parameter\ProcessParameter;
use BusyPHP\image\parameter\QualityParameter;
use BusyPHP\image\parameter\RadiusParameter;
use BusyPHP\image\parameter\RotateParameter;
use BusyPHP\image\parameter\SharpenParameter;
use BusyPHP\image\parameter\StripMetaParameter;
use BusyPHP\image\parameter\TextParameter;
use BusyPHP\image\parameter\UrlParameter;
use BusyPHP\image\parameter\ZoomParameter;
use BusyPHP\image\result\ExifResult;
use BusyPHP\image\result\ImageStyleResult;
use BusyPHP\image\result\InfoResult;
use BusyPHP\image\result\PrimaryColorResult;
use BusyPHP\image\result\ProcessResult;
use BusyPHP\image\result\SaveResult;
use Intervention\Image\AbstractFont;
use Intervention\Image\Constraint;
use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Gd\Color;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Size;
use think\Container;
use think\exception\FileException;
use think\facade\Route;
use think\file\UploadedFile;
use think\route\Url;
use Throwable;

/**
 * 本地图片处理驱动
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/9 2:29 PM Local.php $
 * @link https://image.intervention.io/v2
 */
class Local extends Driver
{
    use ResponseConcern;
    
    /** @var string[] 位置映射 */
    public static $gravityMap = [
        BaseParameter::GRAVITY_TOP_LEFT      => 'top-left',
        BaseParameter::GRAVITY_TOP_CENTER    => 'top',
        BaseParameter::GRAVITY_TOP_RIGHT     => 'top-right',
        BaseParameter::GRAVITY_LEFT_CENTER   => 'left',
        BaseParameter::GRAVITY_CENTER        => 'center',
        BaseParameter::GRAVITY_RIGHT_CENTER  => 'right',
        BaseParameter::GRAVITY_BOTTOM_LEFT   => 'bottom-left',
        BaseParameter::GRAVITY_BOTTOM_CENTER => 'bottom',
        BaseParameter::GRAVITY_BOTTOM_RIGHT  => 'bottom-right',
    ];
    
    /** @var ImageManager */
    protected $manager;
    
    /** @var LocalImageStyleManagerInterface */
    protected $style;
    
    protected $config = [
        'font_list'           => [],
        'image_style_manager' => ''
    ];
    
    
    /**
     * 获取支持的字体
     * @return array
     */
    public function getFontList() : array
    {
        return $this->config['font_list'] ?? [];
    }
    
    
    /**
     * 获取默认字体
     * @return string
     */
    public function getDefaultFontPath() : string
    {
        return '';
    }
    
    
    /**
     * 返回不支持的参数模板
     * @return class-string<BaseParameter>[]
     */
    public function getNotSupportParameters() : array
    {
        return [
            InterlaceParameter::class
        ];
    }
    
    
    /**
     * 获取有效的数字
     * @param int        $number
     * @param int|string $default
     * @param int        $min
     * @return int|string
     */
    public static function handleNumber(int $number, $default = 0, int $min = 0)
    {
        return $number > $min ? $number : $default;
    }
    
    
    /**
     * 实例Font对象
     * @param Image  $image
     * @param string $text
     * @return AbstractFont
     */
    public static function instanceFont(Image $image, string $text = '') : AbstractFont
    {
        $fontClass = "\Intervention\Image\\{$image->getDriver()->getDriverName()}\Font";
        
        return new $fontClass($text);
    }
    
    
    /**
     * 实例化ImageManager对象
     * @return ImageManager
     */
    protected function manager() : ImageManager
    {
        if (!$this->manager) {
            $driver = 'gd';
            if (extension_loaded('imagick')) {
                $driver = 'imagick';
            }
            $this->manager = new ImageManager(['driver' => $driver]);
        }
        
        return $this->manager;
    }
    
    
    /**
     * 处理路径
     * @param string $path
     * @return string
     */
    protected function path(string $path) : string
    {
        return $this->driver->path($path);
    }
    
    
    /**
     * 处理图片
     * @param ProcessParameter $parameter
     * @return array{image: Image, quality:int|null, format: string|null}
     */
    protected function handle(ProcessParameter $parameter) : array
    {
        $image     = $this->manager()->make($this->path($parameter->getOldPath()));
        $quality   = null;
        $format    = null;
        $stripMeta = false;
        
        $parameters = $parameter->getParameters();
        if ($style = $parameter->getStyle()) {
            $parameters = $this->getStyleByCache($style)->getUrlParameter()->getParameters();
        }
        
        foreach ($parameters as $item) {
            $item->verification();
            switch (true) {
                // 等比例缩放
                case $item instanceof ZoomParameter:
                    $image->filter(new class($item) implements FilterInterface {
                        /**
                         * @var ZoomParameter
                         */
                        private $parameter;
                        
                        
                        public function __construct(ZoomParameter $parameter)
                        {
                            $this->parameter = $parameter;
                        }
                        
                        
                        public function applyFilter(Image $image) : Image
                        {
                            switch ($this->parameter->getType()) {
                                // 不保持比例
                                case ZoomParameter::TYPE_LOSE:
                                    $image->resize(
                                        $this->parameter->getWidth(),
                                        $this->parameter->getHeight()
                                    );
                                break;
                                // 缩放为指定宽高矩形内的最大图片
                                case ZoomParameter::TYPE_FILL:
                                    $image->resize(
                                        $this->parameter->getWidth(),
                                        $this->parameter->getHeight(),
                                        function(Constraint $constraint) {
                                            $constraint->upsize();
                                            $constraint->aspectRatio();
                                        }
                                    );
                                    $image->resizeCanvas(
                                        $this->parameter->getWidth(),
                                        $this->parameter->getHeight(),
                                        'center',
                                        false,
                                        $this->parameter->getColor()
                                    );
                                break;
                                // 普通缩放
                                default:
                                    $image->resize(
                                        Local::handleNumber($this->parameter->getWidth(), null),
                                        Local::handleNumber($this->parameter->getHeight(), null),
                                        function(Constraint $constraint) {
                                            $constraint->aspectRatio();
                                            
                                            // 小图不够放大
                                            if (!$this->parameter->isEnlarge()) {
                                                $constraint->upsize();
                                            }
                                        }
                                    );
                            }
                            
                            return $image;
                        }
                    });
                break;
                // 缩放裁剪
                case $item instanceof CropParameter:
                    $image->filter(new class($item) implements FilterInterface {
                        /**
                         * @var CropParameter
                         */
                        private $parameter;
                        
                        
                        public function __construct(CropParameter $parameter)
                        {
                            $this->parameter = $parameter;
                        }
                        
                        
                        public function applyFilter(Image $image) : Image
                        {
                            switch ($this->parameter->getType()) {
                                case CropParameter::TYPE_CUT:
                                    return $image->crop(
                                        Local::handleNumber($this->parameter->getWidth(), $image->getWidth()),
                                        Local::handleNumber($this->parameter->getHeight(), $image->getHeight()),
                                        $this->parameter->getDx(),
                                        $this->parameter->getDy()
                                    );
                                case CropParameter::TYPE_CROP:
                                    return $image->fit(
                                        Local::handleNumber($this->parameter->getWidth(), $image->getWidth()),
                                        Local::handleNumber($this->parameter->getHeight(), $image->getHeight())
                                    );
                            }
                            
                            return $image;
                        }
                    });
                break;
                // 圆角裁剪
                case $item instanceof RadiusParameter:
                    $image->filter(new class($item) implements FilterInterface {
                        /**
                         * @var RadiusParameter
                         */
                        private $parameter;
                        
                        
                        public function __construct(RadiusParameter $parameter)
                        {
                            $this->parameter = $parameter;
                        }
                        
                        
                        public function applyFilter(Image $image) : Image
                        {
                            $radius = $this->parameter->getRadius();
                            if ($radius <= 0) {
                                return $image;
                            }
                            
                            if ($this->parameter->isInside()) {
                                $image->fit($radius * 2, $radius * 2);
                            }
                            
                            $image->roundCorners($this->parameter->getRx(), $this->parameter->getRy());
                            
                            return $image;
                        }
                    });
                break;
                // 旋转角度
                case $item instanceof RotateParameter:
                    $image->filter(new class($item) implements FilterInterface {
                        /**
                         * @var RotateParameter
                         */
                        private $parameter;
                        
                        
                        public function __construct(RotateParameter $parameter)
                        {
                            $this->parameter = $parameter;
                        }
                        
                        
                        public function applyFilter(Image $image) : Image
                        {
                            return $image->rotate(-$this->parameter->getRotate(), $this->parameter->getColor());
                        }
                    });
                break;
                // 自动旋转
                case $item instanceof AutoOrientParameter:
                    $image->filter(new class implements FilterInterface {
                        public function applyFilter(Image $image) : Image
                        {
                            return $image->orientate();
                        }
                    });
                break;
                // 对比度
                case $item instanceof ContrastParameter:
                    $image->filter(new class($item) implements FilterInterface {
                        /**
                         * @var ContrastParameter
                         */
                        private $parameter;
                        
                        
                        public function __construct(ContrastParameter $parameter)
                        {
                            $this->parameter = $parameter;
                        }
                        
                        
                        public function applyFilter(Image $image) : Image
                        {
                            return $image->contrast($this->parameter->getContrast());
                        }
                    });
                break;
                // 锐化
                case $item instanceof SharpenParameter:
                    $image->filter(new class($item) implements FilterInterface {
                        /**
                         * @var SharpenParameter
                         */
                        private $parameter;
                        
                        
                        public function __construct(SharpenParameter $parameter)
                        {
                            $this->parameter = $parameter;
                        }
                        
                        
                        public function applyFilter(Image $image) : Image
                        {
                            return $image->sharpen($this->parameter->getSharpen());
                        }
                    });
                break;
                // 高斯模糊
                case $item instanceof BlurParameter:
                    $image->filter(new class($item) implements FilterInterface {
                        /**
                         * @var BlurParameter
                         */
                        private $parameter;
                        
                        
                        public function __construct(BlurParameter $parameter)
                        {
                            $this->parameter = $parameter;
                        }
                        
                        
                        public function applyFilter(Image $image) : Image
                        {
                            return $image->blur($this->parameter->getRadius());
                        }
                    });
                break;
                // 亮度
                case $item instanceof BrightParameter:
                    $image->filter(new class($item) implements FilterInterface {
                        /**
                         * @var BrightParameter
                         */
                        private $parameter;
                        
                        
                        public function __construct(BrightParameter $parameter)
                        {
                            $this->parameter = $parameter;
                        }
                        
                        
                        public function applyFilter(Image $image) : Image
                        {
                            return $image->brightness($this->parameter->getBright());
                        }
                    });
                break;
                // 灰度
                case $item instanceof GrayscaleParameter:
                    $image->filter(new class implements FilterInterface {
                        public function applyFilter(Image $image) : Image
                        {
                            return $image->greyscale();
                        }
                    });
                break;
                // 翻转颜色
                case $item instanceof InvertParameter:
                    $image->filter(new class implements FilterInterface {
                        public function applyFilter(Image $image) : Image
                        {
                            return $image->invert();
                        }
                    });
                break;
                // 图像翻转
                case $item instanceof FlipParameter:
                    switch ($item->getFlip()) {
                        case FlipParameter::FLIP_HORIZONTAL:
                            $image->filter(new class implements FilterInterface {
                                public function applyFilter(Image $image) : Image
                                {
                                    return $image->flip('h');
                                }
                            });
                        break;
                        case FlipParameter::FLIP_VERTICAL:
                            $image->filter(new class implements FilterInterface {
                                public function applyFilter(Image $image) : Image
                                {
                                    return $image->flip('v');
                                }
                            });
                        break;
                    }
                break;
                // 伽马校正
                case $item instanceof GammaParameter:
                    $image->filter(new class($item) implements FilterInterface {
                        /**
                         * @var GammaParameter
                         */
                        private $parameter;
                        
                        
                        public function __construct(GammaParameter $parameter)
                        {
                            $this->parameter = $parameter;
                        }
                        
                        
                        public function applyFilter(Image $image) : Image
                        {
                            return $image->gamma(round(($this->parameter->getGamma() / 100 * 1.5) + 0.8, 2));
                        }
                    });
                break;
                // 像素化处理
                case $item instanceof PixelateParameter:
                    $image->filter(new class($item) implements FilterInterface {
                        /**
                         * @var PixelateParameter
                         */
                        private $parameter;
                        
                        
                        public function __construct(PixelateParameter $parameter)
                        {
                            $this->parameter = $parameter;
                        }
                        
                        
                        public function applyFilter(Image $image) : Image
                        {
                            return $image->pixelate($this->parameter->getPixelate());
                        }
                    });
                break;
                // 质量
                case $item instanceof QualityParameter:
                    if ($item->getQuality() != 90) {
                        $quality = $item->getQuality();
                    }
                break;
                // 格式
                case $item instanceof FormatParameter:
                    if ($item->getFormat() && $item->getFormat() != $image->extension) {
                        $format = $item->getFormat();
                    }
                break;
                // 去除元数据(GD库本身就会移除元信息)
                case $item instanceof StripMetaParameter:
                    $stripMeta = true;
                break;
                // 图片
                case $item instanceof ImageParameter:
                    $image->filter(new class($item) implements FilterInterface {
                        /**
                         * @var ImageParameter
                         */
                        private $parameter;
                        
                        
                        public function __construct(ImageParameter $parameter)
                        {
                            $this->parameter = $parameter;
                        }
                        
                        
                        public function applyFilter(Image $image) : Image
                        {
                            $water       = $image->getDriver()->init($this->parameter->getImage());
                            $width       = $image->width();
                            $height      = $image->height();
                            $waterWidth  = $water->width();
                            $waterHeight = $water->height();
                            
                            // 角度
                            $rotate = $this->parameter->getRotate();
                            if ($rotate > 0 && $rotate <= 360) {
                                // 计算旋转后的水印尺寸
                                $radian      = $rotate * pi() / 180.0;
                                $cos         = cos($radian);
                                $sin         = sin($radian);
                                $waterWidth  = round(abs($waterWidth * $cos) + abs($waterHeight * $sin));
                                $waterHeight = round(abs($waterWidth * $sin) + abs($waterHeight * $cos));
                                
                                $water->rotate(-$rotate);
                            }
                            
                            // 透明
                            if ($this->parameter->getOpacity() > 0 && $this->parameter->getOpacity() <= 100) {
                                $water->opacity($this->parameter->getOpacity());
                            }
                            
                            // 铺满
                            if ($this->parameter->isOverspread()) {
                                $x = 0;
                                while ($x < $width) {
                                    $y = 0;
                                    while ($y < $height) {
                                        $image->insert($water, 'top-left', $x, $y);
                                        $y += $waterHeight;
                                    }
                                    
                                    $x += $waterWidth;
                                }
                            } else {
                                $image->insert(
                                    $water,
                                    Local::$gravityMap[$this->parameter->getGravity()] ?? 'top-left',
                                    $this->parameter->getDx(),
                                    $this->parameter->getDy()
                                );
                            }
                            
                            return $image;
                        }
                    });
                break;
                // 文字
                case $item instanceof TextParameter:
                    $image->filter(new class($item) implements FilterInterface {
                        /**
                         * @var TextParameter
                         */
                        private $parameter;
                        
                        
                        public function __construct(TextParameter $parameter)
                        {
                            $this->parameter = $parameter;
                        }
                        
                        
                        public function applyFilter(Image $image) : Image
                        {
                            $width    = $image->width();
                            $height   = $image->height();
                            $color    = new Color($this->parameter->getColor());
                            $color    = $color->format('array');
                            $color[3] = $this->parameter->getOpacity() / 100;
                            
                            // 预计算水印尺寸
                            $font = Local::instanceFont($image, $this->parameter->getText());
                            $font->size($this->parameter->getFontsize());
                            $font->file(app()->getPublicPath('汉仪元隆黑60W.ttf'));
                            $font->angle($this->parameter->getRotate());
                            $textBox     = $font->getBoxSize();
                            $waterWidth  = $textBox['width'];
                            $waterHeight = $textBox['height'];
                            
                            // 铺满
                            if ($this->parameter->isOverspread()) {
                                $rotate = $this->parameter->getRotate();
                                if ($rotate > 0 && $rotate <= 360) {
                                    // 计算旋转后的水印尺寸
                                    $radian      = $rotate * pi() / 180.0;
                                    $cos         = cos($radian);
                                    $sin         = sin($radian);
                                    $waterWidth  = round(abs($waterWidth * $cos) + abs($waterHeight * $sin));
                                    $waterHeight = round(abs($waterWidth * $sin) + abs($waterHeight * $cos));
                                }
                                
                                $x = 0;
                                while ($x < $width) {
                                    $y = 0;
                                    while ($y < $height) {
                                        $image->text($this->parameter->getText(), $x, $y, function(AbstractFont $font) use ($color) {
                                            $font->file(app()->getPublicPath('汉仪元隆黑60W.ttf'));
                                            $font->size($this->parameter->getFontsize());
                                            $font->color($color);
                                            $font->angle(-$this->parameter->getRotate());
                                            $font->align('left');
                                            $font->valign('top');
                                        });
                                        
                                        $y += $waterHeight;
                                    }
                                    
                                    $x += $waterWidth;
                                }
                            } else {
                                $position = Local::$gravityMap[$this->parameter->getGravity()] ?? 'top-left';
                                
                                /** @var Size $imageSize */
                                $imageSize = $image->getSize();
                                $imageSize->align($position, $this->parameter->getDx(), $this->parameter->getDy());
                                $textSize = new Size($waterWidth, $waterHeight);
                                $textSize->align($position);
                                $point = $imageSize->relativePosition($textSize);
                                
                                $image->text($this->parameter->getText(), $point->x, $point->y, function(AbstractFont $font) use ($color) {
                                    $font->file(app()->getPublicPath('汉仪元隆黑60W.ttf'));
                                    $font->size($this->parameter->getFontsize());
                                    $font->color($color);
                                    $font->angle(-$this->parameter->getRotate());
                                    $font->align('left');
                                    $font->valign('top');
                                });
                            }
                            
                            return $image;
                        }
                    });
                break;
            }
        }
        
        // 删除元数据
        if ($stripMeta) {
            $image->stripMeta();
        }
        
        return [
            'image'   => $image,
            'quality' => is_null($quality) ? 90 : $quality,
            'format'  => $format
        ];
    }
    
    
    /**
     * 处理图片
     * @param ProcessParameter $parameter
     * @return ProcessResult
     */
    public function process(ProcessParameter $parameter) : ProcessResult
    {
        $result  = $this->handle($parameter);
        $image   = $result['image'];
        $quality = $result['quality'];
        $format  = $result['format'];
        $encoder = $image->encode($result['format'], $quality);
        $data    = $encoder->getEncoded();
        
        $processResult = new ProcessResult();
        $processResult->setData($data);
        $processResult->setFormat(strtolower($format ?: pathinfo($parameter->getOldPath(), PATHINFO_EXTENSION)) ?: 'jpeg');
        $processResult->setMimetype(finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $data) ?: 'image/jpeg');
        $processResult->setWidth($encoder->getWidth());
        $processResult->setHeight($encoder->getHeight());
        $processResult->setFilesize(strlen($data));
        $image->destroy();
        
        return $processResult;
    }
    
    
    /**
     * 处理并保存
     * @param ProcessParameter $parameter
     * @return SaveResult
     */
    public function save(ProcessParameter $parameter) : SaveResult
    {
        $result  = $this->handle($parameter);
        $image   = $result['image'];
        $quality = $result['quality'];
        $image->save($this->path($parameter->getNewPath()), $quality, $result['format']);
        
        $saveResult = new SaveResult();
        $saveResult->setFormat($image->extension);
        $saveResult->setWidth($image->width());
        $saveResult->setHeight($image->height());
        $saveResult->setSize($image->filesize());
        $saveResult->setQuality($quality);
        
        $image->destroy();
        
        return $saveResult;
    }
    
    
    /**
     * 生成在线处理URL
     * @param UrlParameter $parameter
     * @return Url
     */
    public function url(UrlParameter $parameter) : Url
    {
        return Route::buildUrl('/general/image/' . $parameter->getOldPath(), [
            'process' => self::convertParameterToProcessRule($parameter)
        ])->suffix(false);
    }
    
    
    /**
     * 获取图片信息
     * @param string $path 图片路径
     * @return InfoResult
     */
    public function getInfo(string $path) : InfoResult
    {
        $path  = $this->path($path);
        $image = $this->manager()->make($path);
        $res   = new InfoResult();
        $res->setWidth($image->width());
        $res->setHeight($image->height());
        $res->setSize($image->filesize());
        $res->setFormat($image->extension);
        $res->setFrameCount(0);
        $res->setMd5(hash_file('md5', $path));
        $image->destroy();
        
        return $res;
    }
    
    
    /**
     * 获取图片EXIF
     * @param string $path 图片路径
     * @return ExifResult
     */
    public function getExif(string $path) : ExifResult
    {
        $image = $this->manager()->make($this->path($path));
        $raw   = $image->exif();
        $image->destroy();
        
        $res = new ExifResult($this, $raw);
        $res->setOrientation($raw['Orientation'] ?? 0);
        $res->setXResolution($raw['XResolution'] ?? '');
        $res->setYResolution($raw['YResolution'] ?? '');
        
        return $res;
    }
    
    
    /**
     * 获取图片主色调
     * @param string $path 图片路径
     * @return PrimaryColorResult
     */
    public function getPrimaryColor(string $path) : PrimaryColorResult
    {
        $image = $this->manager()->make($this->path($path));
        $rgb   = $image->primaryColor();
        $image->destroy();
        
        $res = new PrimaryColorResult();
        $res->setRgb($rgb);
        
        return $res;
    }
    
    
    /**
     * 上传水印图片
     * @param UploadedFile $file
     * @return string 水印图片URL
     * @throws Throwable
     */
    public function uploadWatermark(UploadedFile $file) : string
    {
        FileHelper::checkImage($file->getPathname(), $file->getOriginalExtension());
        if (!in_array(strtolower($file->getOriginalExtension()), ['png', 'jpeg', 'jpg', 'gif'])) {
            throw new FileException('仅支持png,jpeg,jpg,gif');
        }
        
        $date = date('YmdHis');
        $path = "system/watermark/$date.png";
        $info = pathinfo($this->path($path));
        $file->move($info['dirname'], $info['basename']);
        
        return $this->driver->url($path);
    }
    
    
    /**
     * 添加图片样式
     * @param string $name 样式名称
     * @param array  $content 样式规则
     */
    public function createStyle(string $name, array $content)
    {
        $this->style()->createImageStyle($name, $content);
        $this->clearSelectStyleCache();
    }
    
    
    /**
     * 更新图片样式
     * @param string $name 样式名称
     * @param array  $content 样式规则
     */
    public function updateStyle(string $name, array $content)
    {
        $this->style()->updateImageStyle($name, $content);
        $this->clearSelectStyleCache();
    }
    
    
    /**
     * 删除图片样式
     * @param string $name 样式名称
     */
    public function deleteStyle(string $name)
    {
        $this->style()->deleteImageStyle($name);
        $this->clearSelectStyleCache();
    }
    
    
    /**
     * 获取图片样式
     * @param string $name
     * @return ImageStyleResult
     */
    public function getStyle(string $name) : ImageStyleResult
    {
        return $this->style()->getImageStyle($name);
    }
    
    
    /**
     * 查询图片样式
     * @return ImageStyleResult[]
     */
    public function selectStyle() : array
    {
        return $this->style()->selectImageStyle();
    }
    
    
    /**
     * 获取图片样式管理类
     * @return LocalImageStyleManagerInterface
     */
    protected function style() : LocalImageStyleManagerInterface
    {
        if (!$this->style) {
            $class = trim($this->config['image_style_manager'] ?? '');
            $class = $class ?: '\BusyPHP\app\admin\model\system\file\image\SystemFileImageStyle';
            if (!is_subclass_of($class, LocalImageStyleManagerInterface::class)) {
                throw new ClassNotImplementsException($class, LocalImageStyleManagerInterface::class);
            }
            
            $this->style = Container::getInstance()->make($class, [], true);
        }
        
        return $this->style;
    }
    
    
    /**
     * TODO 寻找一个合适的位置放置该方法
     * 将 ProcessParameter 转为 本地处理规则
     * @param ProcessParameter $parameter
     * @return string
     */
    public static function convertParameterToProcessRule(ProcessParameter $parameter) : string
    {
        $process = [];
        $format  = null;
        $strip   = false;
        foreach ($parameter->getParameters() as $item) {
            switch (true) {
                // 等比例缩放
                case $item instanceof ZoomParameter:
                    $args = ["{$item->getWidth()}x{$item->getHeight()}", "t/{$item->getType()}"];
                    if (BaseParameter::DEFAULT_COLOR != $color = $item->getColor()) {
                        $color  = TransHelper::base64encodeUrl($color);
                        $args[] = "c/$color";
                    }
                    if ($item->isEnlarge()) {
                        $args[] = "e/1";
                    }
                    $process['zoom'][] = $args;
                break;
                // 缩放裁剪
                case $item instanceof CropParameter:
                    switch ($item->getType()) {
                        case CropParameter::TYPE_CUT:
                            $args = ["{$item->getWidth()}x{$item->getHeight()}"];
                            if (0 != $x = $item->getDx()) {
                                $args[] = "dx/$x";
                            }
                            if (0 != $y = $item->getDy()) {
                                $args[] = "dy/$y";
                            }
                            $args[]            = "t/{$item->getType()}";
                            $process['crop'][] = $args;
                        break;
                        case CropParameter::TYPE_CROP:
                            $process['crop'][] = [
                                "{$item->getWidth()}x{$item->getHeight()}",
                                "t/{$item->getType()}"
                            ];
                        break;
                    }
                break;
                // 圆角裁剪
                case $item instanceof RadiusParameter:
                    $args = ["{$item->getRx()}x{$item->getRy()}"];
                    if ($item->isInside()) {
                        $args[] = "i/1";
                    }
                    $process['radius'][] = $args;
                break;
                // 旋转角度
                case $item instanceof RotateParameter:
                    $args = [$item->getRotate()];
                    if (BaseParameter::DEFAULT_COLOR != $color = $item->getColor()) {
                        $color  = TransHelper::base64encodeUrl($color);
                        $args[] = "c/$color";
                    }
                    $process['rotate'][] = $args;
                break;
                // 自动旋转
                case $item instanceof AutoOrientParameter:
                    $process['orient'][] = [];
                break;
                // 对比度
                case $item instanceof ContrastParameter:
                    $process['contrast'][] = [$item->getContrast()];
                break;
                // 锐化
                case $item instanceof SharpenParameter:
                    $process['sharpen'][] = [$item->getSharpen()];
                break;
                // 高斯模糊
                case $item instanceof BlurParameter:
                    $process['blur'][] = ["{$item->getRadius()}x{$item->getSigma()}"];
                break;
                // 亮度
                case $item instanceof BrightParameter:
                    $process['bright'][] = [$item->getBright()];
                break;
                // 灰度
                case $item instanceof GrayscaleParameter:
                    $process['gray'][] = [];
                break;
                // 翻转颜色
                case $item instanceof InvertParameter:
                    $process['invert'][] = [];
                break;
                // 图片反转
                case $item instanceof FlipParameter:
                    switch ($item->getFlip()) {
                        case FlipParameter::FLIP_HORIZONTAL:
                            $process['flip'][] = [1];
                        break;
                        case FlipParameter::FLIP_VERTICAL:
                            $process['flip'][] = [2];
                        break;
                    }
                break;
                // 伽马校正
                case $item instanceof GammaParameter:
                    $process['gamma'][] = [$item->getGamma()];
                break;
                // 像素化处理
                case $item instanceof PixelateParameter:
                    if (0 < $value = $item->getPixelate()) {
                        $process['pixel'][] = [$value];
                    }
                break;
                // 质量
                case $item instanceof QualityParameter:
                    if (0 < $value = $item->getQuality()) {
                        $process['quality'][] = [$value, "t/{$item->getType()}"];
                    }
                break;
                // 格式
                case $item instanceof FormatParameter:
                    $format = $item->getFormat();
                break;
                // 去除元数据(GD库本身就会移除元信息)
                case $item instanceof StripMetaParameter:
                    $strip = true;
                break;
                // 图片
                case $item instanceof ImageParameter:
                    if ($value = $item->getImage()) {
                        $args = [TransHelper::base64encodeUrl($value)];
                        if (0 < $value = $item->getRotate()) {
                            $args[] = "r/$value";
                        }
                        if (0 < $value = $item->getOpacity()) {
                            $args[] = "a/$value";
                        }
                        if ($value = $item->getGravity()) {
                            $args[] = "g/$value";
                        }
                        if (0 != $value = $item->getDx()) {
                            $args[] = "x/$value";
                        }
                        if (0 != $value = $item->getDy()) {
                            $args[] = "y/$value";
                        }
                        if ($item->isOverspread()) {
                            $args[] = "f/1";
                        }
                        $process['water/1'][] = $args;
                    }
                break;
                // 文字
                case $item instanceof TextParameter:
                    if ($value = $item->getText()) {
                        $args = [TransHelper::base64encodeUrl($value)];
                        if ($value = $item->getFont()) {
                            $value  = TransHelper::base64encodeUrl($value);
                            $args[] = "t/$value";
                        }
                        if (BaseParameter::DEFAULT_COLOR != $value = $item->getColor()) {
                            $value  = TransHelper::base64encodeUrl($value);
                            $args[] = "c/$value";
                        }
                        if (0 < $value = $item->getRotate()) {
                            $args[] = "r/$value";
                        }
                        if (0 < $value = $item->getOpacity()) {
                            $args[] = "a/$value";
                        }
                        if (0 < $value = $item->getFontsize()) {
                            $args[] = "s/$value";
                        }
                        if ($value = $item->getGravity()) {
                            $args[] = "g/$value";
                        }
                        if (0 != $value = $item->getDx()) {
                            $args[] = "x/$value";
                        }
                        if (0 != $value = $item->getDy()) {
                            $args[] = "y/$value";
                        }
                        if (0 < $value = $item->getShadow()) {
                            $args[] = "d/$value";
                        }
                        if ($item->isOverspread()) {
                            $args[] = "f/1";
                        }
                        $process['water/2'][] = $args;
                    }
                break;
            }
        }
        
        if ($parameter instanceof UrlParameter) {
            if ($style = $parameter->getStyle()) {
                $process['style'][] = [$style];
            }
            if ($parameter->isDownload()) {
                $args = [1];
                if ($filename = $parameter->getFilename()) {
                    $filename = TransHelper::base64encodeUrl($filename);
                    $args[]   = "f/$filename";
                }
                $process['down'][] = $args;
            }
            
            if ($lifetime = $parameter->getLifetime() > 0) {
                $process['cache'][] = $lifetime;
            }
        }
        
        if ($strip) {
            $process['strip'][] = [];
        }
        if ($format) {
            $process['format'][] = [$format];
        }
        
        $value = [];
        foreach ($process as $key => $items) {
            foreach ($items as $item) {
                $value[] = $key . '/' . implode('/', $item);
            }
        }
        
        return implode(',', $value);
    }
    
    
    /**
     * TODO 寻找一个合适的位置放置该方法
     * 将 本地处理规则 转为 UrlParameter
     * @param string $process 规则字符串
     * @param string $path 要处理的图片地址
     * @return UrlParameter
     */
    public static function convertProcessRuleToParameter(string $process, string $path = '') : UrlParameter
    {
        $parameter = new UrlParameter($path);
        $process   = explode(',', trim($process)) ?: [];
        foreach ($process as $item) {
            $item = trim($item);
            if (!$item) {
                continue;
            }
            
            $args  = ArrayHelper::split('/', $item, 2);
            $key   = strtolower(trim(array_shift($args)));
            $value = trim(array_shift($args));
            if (!$key) {
                continue;
            }
            
            switch ($key) {
                // 等比例缩放
                case 'zoom':
                    [$width, $height] = ArrayHelper::split('x', $value, 2);
                    $width  = intval($width);
                    $height = intval($height);
                    $map    = ArrayHelper::oneToTwo($args);
                    switch ($map->get('t', 0, 'intval')) {
                        // 不保持比例
                        case ZoomParameter::TYPE_LOSE:
                            $parameter->zoomLose($width, $height);
                        break;
                        // 缩放为指定宽高矩形内的最大图片
                        case ZoomParameter::TYPE_FILL:
                            $parameter->zoomFill($width, $height, TransHelper::base64decodeUrl($map->get('c', '', 'trim')));
                        break;
                        // 普通缩放
                        default:
                            $parameter->zoom($width, $height, $map->get('e', 0, 'intval') > 0);
                    }
                break;
                // 缩放裁剪
                case 'crop':
                    [$width, $height] = ArrayHelper::split('x', $value, 2);
                    $width  = intval($width);
                    $height = intval($height);
                    $map    = ArrayHelper::oneToTwo($args);
                    switch ($map->get('t', 0, 'intval')) {
                        case CropParameter::TYPE_CUT:
                            $parameter->cut($width, $height, $map->get('dx', 0, 'intval'), $map->get('dy', 0, 'intval'));
                        break;
                        case CropParameter::TYPE_CROP:
                            $parameter->crop($width, $height);
                        break;
                    }
                break;
                // 圆角裁剪
                case 'radius':
                    [$rx, $ry] = ArrayHelper::split('x', $value, 2);
                    $map = ArrayHelper::oneToTwo($args);
                    $parameter->radiusXY(intval($rx), intval($ry), $map->get('i', 0, 'intval') > 0);
                break;
                // 旋转角度
                case 'rotate':
                    $map = ArrayHelper::oneToTwo($args);
                    $parameter->rotate(intval($value), TransHelper::base64decodeUrl($map->get('c', '', 'trim')));
                break;
                // 自动旋转
                case 'orient':
                    $parameter->autoOrient();
                break;
                // 对比度
                case 'contrast':
                    $parameter->contrast(intval($value));
                break;
                // 锐化
                case 'sharpen':
                    if (0 != $sharpen = intval($value)) {
                        $parameter->sharpen($sharpen);
                    }
                break;
                // 高斯模糊
                case 'blur':
                    [$radius, $sigma] = ArrayHelper::split('x', $value, 2);
                    $parameter->blur(intval($radius), intval($sigma));
                break;
                // 亮度
                case 'bright':
                    $parameter->bright(intval($value));
                break;
                // 灰度
                case 'gray':
                    $parameter->grayscale();
                break;
                // 颜色反转
                case 'invert':
                    $parameter->invert();
                break;
                // 图片反转
                case 'flip':
                    switch (intval($value)) {
                        case 1:
                            $parameter->flip(FlipParameter::FLIP_HORIZONTAL);
                        break;
                        case 2:
                            $parameter->flip(FlipParameter::FLIP_VERTICAL);
                        break;
                    }
                break;
                // 伽马校正
                case 'gamma':
                    $parameter->gamma(intval($value));
                break;
                // 像素化
                case 'pixel':
                    $parameter->pixelate(intval($value));
                break;
                // 质量
                case 'quality':
                    $map = ArrayHelper::oneToTwo($args);
                    $parameter->quality(intval($value), $map->get('t', 0, 'intval'));
                break;
                // 格式
                case 'format':
                    $parameter->format($value);
                break;
                // 移除元数据
                case 'strip':
                    $parameter->stripMeta();
                break;
                // 水印
                case 'water':
                    $value = intval($value);
                    if (count($args) >= 3) {
                        // 图片水印
                        if ($value == 1) {
                            $image = trim(TransHelper::base64decodeUrl(array_shift($args)));
                            if ($image) {
                                $map            = ArrayHelper::oneToTwo($args);
                                $imageParameter = new ImageParameter($image);
                                $imageParameter->setDx($map->get('x', 0, 'intval'));
                                $imageParameter->setDy($map->get('y', 0, 'intval'));
                                if (0 < $rotate = $map->get('r', 0, 'intval')) {
                                    $imageParameter->setRotate($rotate);
                                }
                                if (0 < $opacity = $map->get('a', 0, 'intval')) {
                                    $imageParameter->setOpacity($opacity);
                                }
                                if ($gravity = $map->get('g', '', 'trim')) {
                                    $imageParameter->setGravity($gravity);
                                }
                                if ($map->get('f', 0, 'intval') > 0) {
                                    $imageParameter->setOverspread(true);
                                }
                                
                                $parameter->image($imageParameter);
                            }
                        }
                        
                        //
                        // 文字水印
                        elseif ($value == 2) {
                            $text = trim(TransHelper::base64decodeUrl(array_shift($args)));
                            $map  = ArrayHelper::oneToTwo($args);
                            if ($text) {
                                $textParameter = new TextParameter($text);
                                $textParameter->setDx($map->get('x', 0, 'intval'));
                                $textParameter->setDy($map->get('y', 0, 'intval'));
                                $textParameter->setColor(TransHelper::base64decodeUrl($map->get('c', '', 'trim')));
                                if (0 < $rotate = $map->get('r', 0, 'intval')) {
                                    $textParameter->setRotate($rotate);
                                }
                                if (0 < $opacity = $map->get('a', 0, 'intval')) {
                                    $textParameter->setOpacity($opacity);
                                }
                                if ($map->get('f', 0, 'intval') > 0) {
                                    $textParameter->setOverspread(true);
                                }
                                if (0 < $shadow = $map->get('d', 0, 'intval')) {
                                    $textParameter->setShadow($shadow);
                                }
                                if (0 < $fontsize = $map->get('s', 0, 'intval')) {
                                    $textParameter->setFontsize($fontsize);
                                }
                                if ($gravity = $map->get('g', '', 'trim')) {
                                    $textParameter->setGravity($gravity);
                                }
                                if ($font = $map->get('t', '', 'trim')) {
                                    $textParameter->setFont(TransHelper::base64decodeUrl($font));
                                }
                                $parameter->text($textParameter);
                            }
                        }
                    }
                break;
                // 样式
                case 'style':
                    if ($value) {
                        $parameter->style($value);
                    }
                break;
                // 下载
                case 'down':
                    if (intval($value) > 0) {
                        $map      = ArrayHelper::oneToTwo($args);
                        $filename = TransHelper::base64decodeUrl($map->get('f', '', 'trim'));
                        $parameter->download($filename ?: '');
                    }
                break;
                // 缓存
                case 'cache':
                    if (0 < $value = intval($value)) {
                        $parameter->cache($value);
                    }
                break;
            }
        }
        
        return $parameter;
    }
}