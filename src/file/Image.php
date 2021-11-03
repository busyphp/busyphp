<?php
declare(strict_types = 1);

namespace BusyPHP\file;

use BusyPHP\App;
use BusyPHP\helper\LogHelper;
use Exception;
use LogicException;
use phpthumb;
use phpthumb_functions;
use RuntimeException;
use think\exception\FileException;
use think\facade\Filesystem;
use think\Response;

/**
 * 图片处理类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/12 下午1:40 下午 Image.php $
 * @link http://phpthumb.sourceforge.net/demo/demo/phpThumb.demo.demo.php
 */
class Image
{
    // +----------------------------------------------------
    // + 裁剪类型
    // +----------------------------------------------------
    /**
     * 裁剪到指定大小充满尺寸
     * @var string
     */
    const THUMB_CORP = 'crop';
    
    /**
     * 按比例缩放到指定大小
     * @var string
     */
    const THUMB_ZOOM = 'zoom';
    
    /**
     * 忽略图片比例并缩放到指定大小
     * @var string
     */
    const THUMB_LOSE = 'lose';
    
    // +----------------------------------------------------
    // + 位置
    // +----------------------------------------------------
    /**
     * 上中
     * @var string
     */
    const P_TOP = 'T';
    
    /**
     * 下中
     * @var string
     */
    const P_BOTTOM = 'B';
    
    /**
     * 左中
     * @var string
     */
    const P_LEFT = 'L';
    
    /**
     * 右中
     * @var string
     */
    const P_RIGHT = 'R';
    
    /**
     * 上左
     * @var string
     */
    const P_TOP_LEFT = 'TL';
    
    /**
     * 上右
     * @var string
     */
    const P_TOP_RIGHT = 'TR';
    
    /**
     * 下左
     * @var string
     */
    const P_BOTTOM_LEFT = 'BL';
    
    /**
     * 下右
     * @var string
     */
    const P_BOTTOM_RIGHT = 'BR';
    
    /**
     * 中间
     * @var string
     */
    const P_CENTER = 'C';
    
    /**
     * 水印铺满
     * @var string
     */
    const P_FILL = '*';
    
    // +----------------------------------------------------
    // + 格式
    // +----------------------------------------------------
    /**
     * png格式
     * @var string
     */
    const F_PNG = 'png';
    
    /**
     * gif格式
     * @var string
     */
    const F_GIF = 'gif';
    
    /**
     * ico格式
     * @var string
     */
    const F_ICO = 'ico';
    
    /**
     * bmp格式
     * @var string
     */
    const F_BMP = 'bmp';
    
    /**
     * jpg格式
     * @var string
     */
    const F_JPEG = 'jpeg';
    
    /**
     * jpg格式
     * @var string
     */
    const F_JPG = 'jpg';
    
    // +----------------------------------------------------
    // + 内置
    // +----------------------------------------------------
    /**
     * @var phpthumb
     */
    protected $phpThumb;
    
    /**
     * 是否保存到本地
     * @var bool
     */
    protected $save = false;
    
    /**
     * 保存到本地的路径
     * @var string
     */
    protected $local = '';
    
    /**
     * 是否执行
     * @var bool
     */
    protected $isExec = false;
    
    
    /**
     * 构造器
     * @param string $src 图片地址
     */
    public function __construct(string $src = null)
    {
        $fileSystem = Filesystem::disk('local');
        $path       = 'image' . DIRECTORY_SEPARATOR . 'phpthumb';
        if (!$fileSystem->createDir($path)) {
            throw new FileException("创建临时目录失败: {$path}");
        }
        
        $cacheDir = $fileSystem->path($path);
        
        $this->phpThumb                               = new phpthumb();
        $this->phpThumb->config_disable_debug         = false;
        $this->phpThumb->config_cache_directory_depth = 3;
        $this->phpThumb->config_temp_directory        = $cacheDir;
        $this->phpThumb->config_cache_directory       = $cacheDir;
        $this->phpThumb->config_document_root         = App::getInstance()->getRootPath();
        $this->src($src);
    }
    
    
    /**
     * 设置图片地址
     * @param string $src
     * @return $this
     */
    public function src($src) : self
    {
        $this->phpThumb->src = $src;
        
        return $this;
    }
    
    
    /**
     * 设置宽度
     * @param int $width
     * @return $this
     */
    public function width($width) : self
    {
        $this->phpThumb->w = intval($width);
        
        return $this;
    }
    
    
    /**
     * 设置高度
     * @param int $height
     * @return $this
     */
    public function height($height) : self
    {
        $this->phpThumb->h = intval($height);
        
        return $this;
    }
    
    
    /**
     * 设置背景色
     * @param $bgColor
     * @return $this
     */
    public function bgColor($bgColor) : self
    {
        $this->phpThumb->bg = ltrim($bgColor, '#');
        
        return $this;
    }
    
    
    /**
     * 设置缩放类型
     * @param string $type
     * @return $this
     */
    public function thumb(string $type) : self
    {
        switch ($type) {
            case self::THUMB_CORP:
                $this->phpThumb->far = 'C';
                $this->phpThumb->zc  = 'C';
                $this->phpThumb->iar = false;
            break;
            case self::THUMB_ZOOM:
                $this->phpThumb->far = 'C';
            break;
            case self::THUMB_LOSE:
                $this->phpThumb->iar = true;
            break;
        }
        
        return $this;
    }
    
    
    /**
     * 设置输出格式
     * @param string $format 格式
     * @return $this
     */
    public function format(string $format) : self
    {
        $this->phpThumb->f = $format;
        
        return $this;
    }
    
    
    /**
     * 设置图片质量
     * @param int $volume 范围在1 - 100
     * @return $this
     */
    public function quality(int $volume) : self
    {
        $this->phpThumb->q = $volume;
        
        return $this;
    }
    
    
    /**
     * 设置小图不够是否放大
     * @param bool $enlarge
     * @return $this
     */
    public function enlarge(bool $enlarge) : self
    {
        $this->phpThumb->aoe = $enlarge;
        
        return $this;
    }
    
    
    /**
     * 设置自动旋转图片
     * @param string $rotate 角度范围: x=自动, l=头朝右, L=头朝左, p=头朝上, P=头朝下
     * @return $this
     */
    public function autoRotate(string $rotate) : self
    {
        $this->phpThumb->ar = $rotate;
        
        return $this;
    }
    
    
    /**
     * 设置旋转角度
     * @param int $rotate 范围 1 - 180
     * @return $this
     */
    public function rotate(int $rotate) : self
    {
        $this->phpThumb->ra = $rotate;
        
        return $this;
    }
    
    
    /**
     * 设置滤镜
     * @param $filter
     * @return $this
     */
    public function filter(...$filter) : self
    {
        $this->phpThumb->fltr[] = implode('|', $filter);
        
        return $this;
    }
    
    
    /**
     * 失去焦点滤镜
     * @param int $volume 范围在 1 - 25
     * @return $this
     */
    public function blur(int $volume) : self
    {
        return $this->filter('blur', $volume);
    }
    
    
    /**
     * 扣图滤镜
     * @param string $color 背景颜色
     * @param int    $minLimit 最小限制，不知道什么意思，后期在注释
     * @param int    $maxLimit 最大限制，不知道什么意思，后期在注释
     * @return $this
     */
    public function stc(string $color, int $minLimit = 5, int $maxLimit = 10) : self
    {
        $this->format(self::F_PNG);
        
        return $this->filter('stc', $color, $minLimit, $maxLimit);
    }
    
    
    /**
     * 设置边框
     * @param int    $width 边框宽度
     * @param string $color 边框颜色
     * @param int    $radiusX 水平圆角
     * @param int    $radiusY 垂直圆角
     * @return $this
     */
    public function border(int $width, string $color = '000000', int $radiusX = 0, int $radiusY = 0) : self
    {
        return $this->filter('bord', $width, $radiusX, $radiusY, $color);
    }
    
    
    /**
     * 设置圆角
     * @param int $x 水平圆角
     * @param int $y 垂直圆角
     * @return $this
     */
    public function radius(int $x = 0, int $y = 0) : self
    {
        $this->format(self::F_PNG);
        
        return $this->filter('ric', $x, $y);
    }
    
    
    /**
     * 设置水印
     * @param string $path 水印路径
     * @param string $position 水印位置 可设置 常量 或 左偏移像素x上偏移像素
     * @param int    $opacity 透明度,范围: 1 - 100
     * @param int    $x 水平偏移 0 - 图片宽度
     * @param int    $y 垂直偏移 0 - 图片高度
     * @param int    $rotate 角度 0 - 89
     * @return $this
     */
    public function watermark(string $path, string $position = self::P_BOTTOM_RIGHT, int $opacity = 25, int $x = 0, int $y = 0, int $rotate = 0) : self
    {
        return $this->filter('wmi', $path, $position, $opacity, $x, $y, $rotate);
    }
    
    
    /**
     * 设置面具
     * @param string $path 面具图片路径
     * @return $this
     */
    public function mask(string $path) : self
    {
        return $this->filter('mask', $path);
    }
    
    
    /**
     * 设置文本
     * @param string $text 文字内容
     * @param int    $size 文字大小
     * @param string $align 文字位置
     * @param string $color 文字颜色
     * @param string $ttf 文字字体
     * @param int    $opacity 透明度
     * @param int    $margin 间距
     * @param int    $angle 角度
     * @param string $bgColor 背景色
     * @param int    $bgOpacity 背景透明度
     * @param string $fillExtend
     * @param float  $lineHeight 行高
     * @return $this
     */
    public function text(string $text, int $size = null, string $align = self::P_BOTTOM_RIGHT, string $color = '000000', string $ttf = '', int $opacity = null, int $margin = null, int $angle = null, string $bgColor = null, int $bgOpacity = 0, string $fillExtend = '', float $lineHeight = null)
    {
        return $this->filter('wmt', $text, $size, $align, $color, $ttf, $opacity, $margin, $angle, $bgColor, $bgOpacity, $fillExtend, $lineHeight);
    }
    
    
    /**
     * 设置保存到本地
     * @param bool   $save 是否保存
     * @param string $local 保存路径
     * @return $this
     */
    public function save(bool $save, string $local) : self
    {
        $this->save  = $save;
        $this->local = $local;
        
        if ($this->save) {
            $path = pathinfo($local, PATHINFO_DIRNAME);
            if (!is_dir($path)) {
                if (!mkdir($path, 0755, true)) {
                    throw new FileException("文件路径不可写: {$path}");
                }
            }
        }
        
        return $this;
    }
    
    
    /**
     * 获取phpThumb
     * @return phpthumb
     */
    public function getPhpThumb() : phpthumb
    {
        return $this->phpThumb;
    }
    
    
    /**
     * 执行缩放
     * @param bool $return 是否返回图像内容
     * @return Response
     * @throws RuntimeException
     */
    public function exec(bool $return = false) : ?Response
    {
        $this->isExec = true;
        $data         = null;
        try {
            if (!$this->phpThumb->GenerateThumbnail()) {
                throw new Exception($this->phpThumb->fatalerror);
            }
            
            // 保存到本地
            if ($this->save) {
                if (!$this->phpThumb->RenderToFile($this->local)) {
                    throw new Exception($this->phpThumb->fatalerror);
                }
                
                if ($return) {
                    $data = $this->phpThumb->outputImageData;
                }
                
                $this->phpThumb->purgeTempFiles();
            }
            
            //
            // 输出到浏览器
            else {
                if ($return) {
                    if (!$this->phpThumb->RenderOutput()) {
                        throw new Exception($this->phpThumb->fatalerror);
                    }
                    $this->phpThumb->purgeTempFiles();
                    
                    $data = $this->phpThumb->outputImageData;
                } else {
                    if (!$this->phpThumb->OutputThumbnail()) {
                        throw new Exception($this->phpThumb->fatalerror);
                    }
                }
            }
        } catch (Exception $e) {
            $record = '';
            foreach ($this->phpThumb->debugmessages as $i => $msg) {
                $record .= PHP_EOL . str_pad($i . ".", 3, ' ') . ' ' . $msg;
            }
            LogHelper::default()->error($record);
            
            $message = $e->getMessage();
            $message = explode('phpthumb.sourceforge.net', $message);
            $message = '缩图失败: ' . trim($message[1]);
            throw new RuntimeException($message, 0, $e);
        }
        
        if ($return) {
            return Response::create($data, 'html', 200)
                ->header(['Content-Length' => strlen($data)])
                ->contentType($this->getMimeType());
        }
        
        return null;
    }
    
    
    /**
     * 在 exec执行后获取获取文件头
     * @return string|false
     */
    public function getMimeType()
    {
        if (!$this->isExec) {
            throw new LogicException('请在exec执行后使用该方法: ' . __METHOD__);
        }
        
        return phpthumb_functions::ImageTypeToMIMEtype($this->getPhpThumb()->thumbnailFormat);
    }
    
    
    /**
     * 数字转水印位置
     * @param int $number
     * @return string
     */
    public static function numberToWatermarkPosition(int $number) : string
    {
        $number   = $number == 0 ? rand(1, 9) : $number;
        $position = Image::P_BOTTOM_RIGHT;
        switch ($number) {
            case 1:
                $position = Image::P_TOP_LEFT;
            break;
            case 2:
                $position = Image::P_TOP;
            break;
            case 3:
                $position = Image::P_TOP_RIGHT;
            break;
            case 4:
                $position = Image::P_LEFT;
            break;
            case 5:
                $position = Image::P_CENTER;
            break;
            case 6:
                $position = Image::P_RIGHT;
            break;
            case 7:
                $position = Image::P_BOTTOM_LEFT;
            break;
            case 8:
                $position = Image::P_BOTTOM;
            break;
            case 9:
                $position = Image::P_BOTTOM_RIGHT;
            break;
            case 10:
                $position = Image::P_FILL;
            break;
        }
        
        return $position;
    }
}