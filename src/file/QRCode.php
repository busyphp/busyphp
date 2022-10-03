<?php
declare(strict_types = 1);

namespace BusyPHP\file;

use BusyPHP\app\admin\setting\QrcodeSetting;
use BusyPHP\helper\ClassHelper;
use Exception;
use LogicException;
use PHPQRCode\Constants;
use PHPQRCode\QRencode;
use PHPQRCode\QRtools;
use think\Config;
use think\exception\FileException;
use think\Response;

/**
 * 二维码生成类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/12 下午7:41 下午 QRCode.php $
 */
class QRCode
{
    // +----------------------------------------------------
    // + 识别率级别
    // +----------------------------------------------------
    /** @var string 还原率7％ */
    const LEVEL_L = 'L';
    
    /** @var string 还原率15% */
    const LEVEL_M = 'M';
    
    /** @var string 还原率25% */
    const LEVEL_Q = 'Q';
    
    /** @var string 还原率30% */
    const LEVEL_H = 'H';
    
    /**
     * 二维码文本
     * @var string
     */
    protected $text;
    
    /**
     * 二维码LOGO路径
     * @var string
     */
    protected $logo;
    
    /**
     * 二维码LOGO大小
     * @var int
     */
    protected $logoSize = 5;
    
    /**
     * 还原率级别
     * @var string
     */
    protected $level = self::LEVEL_M;
    
    /**
     * 设置大小
     * @var int
     */
    protected $size = 10;
    
    /**
     * 设置空白间距
     * @var int
     */
    protected $margin = 1;
    
    /**
     * 是否保存到本地
     * @var bool
     */
    protected $save = false;
    
    /**
     * 保存到本地的路径
     * @var string
     */
    protected $local;
    
    /**
     * 图片质量
     * @var int
     */
    protected $quality = 80;
    
    /**
     * 缩图配置
     * @var QrcodeSetting
     */
    protected $setting;
    
    /**
     * 公共配置
     * @var Config
     */
    protected static $config;
    
    
    /**
     * QRCode constructor.
     * @param string $text 二维码文本
     */
    public function __construct($text = '')
    {
        $this->setting = QrcodeSetting::init();
        $this->level($this->setting->getLevel());
        $this->margin($this->setting->getMargin());
        $this->size($this->setting->getSize());
        $this->quality($this->setting->getQuality());
        
        // Logo
        $logoPath = $this->setting->getLogoPath(true);
        if ($this->setting->isLogoStatus() && is_file($logoPath)) {
            $this->logo($logoPath, $this->setting->getLogoSize());
        }
        
        $this->text($text);
    }
    
    
    /**
     * 获取配置
     * @return Config
     */
    public function getConfig() : Config
    {
        return self::$config;
    }
    
    
    /**
     * 设置文本
     * @param string $text
     * @return $this
     */
    public function text($text) : self
    {
        $this->text = trim($text);
        
        return $this;
    }
    
    
    /**
     * 设置还原率级别
     * @param string $level
     * @return $this
     */
    public function level(string $level) : self
    {
        $this->level = $level;
        
        return $this;
    }
    
    
    /**
     * 设置空白间距
     * @param int $margin
     * @return $this
     */
    public function margin(int $margin) : self
    {
        $this->margin = $margin;
        
        return $this;
    }
    
    
    /**
     * 设置大小
     * @param int $size 大小，范围 1 - 10
     * @return $this
     */
    public function size(int $size) : self
    {
        $size       = $size <= 0 ? 1 : $size;
        $size       = $size > 10 ? 10 : $size;
        $this->size = $size;
        
        return $this;
    }
    
    
    /**
     * 设置LOGO路径
     * @param string $logo
     * @param int    $size 大小 1 - 100 数值越大，LOGO越小
     * @return $this
     */
    public function logo(string $logo, int $size = null) : self
    {
        if (!is_file($logo)) {
            throw new FileException("Logo不存在: {$logo}");
        }
        
        $this->logo = $logo;
        if ($size) {
            $this->logoSize = $size;
        }
        
        return $this;
    }
    
    
    /**
     * 设置是否保存到本地
     * @param bool   $status 是否保存
     * @param string $local 保存路径
     * @return $this
     */
    public function save(bool $status, string $local = null) : self
    {
        $this->save  = $status;
        $this->local = $local;
        
        if ($this->local) {
            $dir = dirname($local);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0775, true)) {
                    throw new FileException("文件夹不可写: {$this->local}");
                }
            }
        }
        
        return $this;
    }
    
    
    /**
     * 设置图片质量
     * @param int $quality
     * @return $this
     */
    public function quality(int $quality) : self
    {
        $this->quality = $quality;
        
        return $this;
    }
    
    
    /**
     * 生成二维码
     * @param bool $return 是否返回响应内容
     * @return Response
     * @throws Exception
     */
    public function exec($return = false) : ?Response
    {
        try {
            $encode = QRencode::factory($this->level, $this->size, $this->margin);
            ob_start();
            $tab = $encode->encode($this->text);
            $err = ob_get_contents();
            ob_end_clean();
            if ($err != '') {
                throw new LogicException("QrCodeError: {$err}");
            }
            
            $response = null;
            $maxSize  = (int) (Constants::QR_PNG_MAXIMUM_SIZE / (count($tab) + 2 * $this->margin));
            $image    = $this->image($tab, min(max(1, $this->size), $maxSize), $this->margin);
            
            // 加LOGO
            !$return or ob_start();
            if ($this->logo) {
                $this->output($this->markLogo($image), $return);
            } else {
                $this->output($image, $return);
            }
            
            if ($return) {
                header_remove('Content-type');
                $code     = ob_get_clean();
                $response = Response::create($code, 'html', 200)
                    ->header(['Content-Length' => strlen($code)])
                    ->contentType('image/jpeg');
            }
            
            return $response;
        } catch (Exception $e) {
            QRtools::log(false, $e->getMessage());
            
            throw $e;
        }
    }
    
    
    /**
     * 输出图片
     * @param resource $resource 图片资源
     * @param bool     $savePrint 保存图片后是否输出图像到浏览器
     */
    protected function output($resource, bool $savePrint = false) : void
    {
        if ($this->save) {
            imagejpeg($resource, $this->local, $this->quality);
            if ($savePrint) {
                header("Content-type: image/jpeg");
                imagejpeg($resource, null, $this->quality);
            }
        } else {
            header("Content-type: image/jpeg");
            imagejpeg($resource, null, $this->quality);
        }
        imagedestroy($resource);
    }
    
    
    /**
     * 加LOGO
     * @param resource $codeImage 二维码图片资源
     * @return resource
     */
    protected function markLogo($codeImage)
    {
        // 修复苹果图标的结尾没有png结尾标识导致的图标无法显示的问题
        $logo = file_get_contents($this->logo);
        $end  = b"\x49\x45\x4E\x44\xAE\x42\x60\x82";
        if (substr($logo, -8) != $end) {
            $logo .= $end;
        }
        
        $logoImage   = imagecreatefromstring($logo);
        $codeWidth   = (int) imagesx($codeImage);
        $codeHeight  = (int) imagesy($codeImage);
        $logoWidth   = (int) imagesx($logoImage);
        $logoHeight  = (int) imagesy($logoImage);
        $thumbWidth  = (int) ($codeWidth / $this->logoSize);
        $thumbHeight = (int) ($logoHeight / ($logoWidth / $thumbWidth));
        $x           = (int) (($codeWidth - $thumbWidth) / 2);
        $y           = (int) (($codeHeight - $thumbHeight) / 2);
        $draw        = imagecreatetruecolor($codeWidth, $codeHeight);
        $color       = imagecolorallocate($draw, 255, 255, 255);
        imagefill($draw, 0, 0, $color);
        imagecolortransparent($draw, $color);
        imagecopyresized($draw, $codeImage, 0, 0, 0, 0, $codeWidth, $codeHeight, $codeWidth, $codeHeight);
        imagecopyresampled($draw, $logoImage, $x, $y, 0, 0, $thumbWidth, $thumbHeight, $logoWidth, $logoHeight);
        imagedestroy($logoImage);
        imagedestroy($codeImage);
        
        return $draw;
    }
    
    
    /**
     * 生成图片
     * @param     $frame
     * @param int $pixelPerPoint
     * @param int $outerFrame
     * @return false|resource
     * @see QRimage::image()
     */
    protected function image($frame, $pixelPerPoint = 4, $outerFrame = 4)
    {
        $h = count($frame);
        $w = strlen($frame[0]);
        
        $imgW = $w + 2 * $outerFrame;
        $imgH = $h + 2 * $outerFrame;
        
        $base_image = imagecreate($imgW, $imgH);
        
        $col[0] = imagecolorallocate($base_image, 255, 255, 255);
        $col[1] = imagecolorallocate($base_image, 0, 0, 0);
        
        imagefill($base_image, 0, 0, $col[0]);
        
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                if ($frame[$y][$x] == '1') {
                    imagesetpixel($base_image, $x + $outerFrame, $y + $outerFrame, $col[1]);
                }
            }
        }
        
        $target_image = imagecreate($imgW * $pixelPerPoint, $imgH * $pixelPerPoint);
        imagecopyresized($target_image, $base_image, 0, 0, 0, 0, $imgW * $pixelPerPoint, $imgH * $pixelPerPoint, $imgW, $imgH);
        imagedestroy($base_image);
        
        return $target_image;
    }
    
    
    /**
     * 获取还原率
     * @return array
     */
    public static function getLevels() : array
    {
        return ClassHelper::getConstAttrs(self::class, 'LEVEL_', ClassHelper::ATTR_NAME);
    }
}