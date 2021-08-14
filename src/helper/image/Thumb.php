<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// | Author: busy^life <busy.life@qq.com>
// +----------------------------------------------------------------------
namespace BusyPHP\helper\image;

use BusyPHP\exception\AppException;


/**
 * 图像缩放操作类
 * @author liu21st <liu21st@gmail.com>
 * @author busy^life <busy.life@qq.com>
 * @version $Id: 2018/1/17 上午10:53 Thumb.php busy^life $
 */
class Thumb extends Image
{
    
    private $source;
    private $export;
    private $width;
    private $height;
    private $save;
    
    
    /**
     * 初始化
     * @param string $source 要处理的图片
     * @param string $export 处理后的图片存放路径
     * @param int    $width 缩图宽度，0为不限
     * @param int    $height 缩图高度，0为不限
     * @param bool   $save 是否直接在浏览器中输出图片，默认为保存图片
     */
    public function __construct($source = '', $export = '', $width = 0, $height = 0, $save = true)
    {
        $this->source = $source;
        $this->export = $export;
        $this->width  = $width;
        $this->height = $height;
        $this->save   = $save;
    }
    
    
    /**
     * 生成指定大小的图片，不保持比例
     * @throws AppException
     */
    public function contort()
    {
        // 获取图片信息和资源句柄
        $info = Thumb::createImage($this->source);
        
        // 画图
        $resource = Thumb::draw($info['resource'], $info['mime'], 0, 0, $info['width'], $info['height'], 0, 0, $this->width, $this->height);
        
        // 输出图像
        Image::output($resource, $info['type'], ($this->save ? $this->export : ''));
        
        imagedestroy($info['resource']);
    }
    
    
    /**
     * 按比例缩放图片，可以设置小图不够是否自动放大
     * @param bool $zoom 小图不够是否自动放大: true为放大, false为保持原图，默认为false
     * @throws AppException
     */
    public function scale($zoom = false)
    {
        // 获取图片信息和资源句柄
        $info          = Thumb::createImage($this->source);
        $source_gene   = $info['width'] / $info['height'];
        $export_width  = $this->width;
        $export_height = $this->height;
        
        // 宽度为空
        if (!$export_width && $export_height) {
            $export_width = $export_height * $info['width'] / $info['height'];
        }
        
        // 高度为空
        if ($export_width && !$export_height) {
            $export_height = $export_width * $info['height'] / $info['width'];
        }
        
        $export_gene = $export_width / $export_height;
        if ($export_gene <= $source_gene) {
            $width  = $export_width;
            $height = $width * ($info['height'] / $info['width']);
        } else {
            $height = $export_height;
            $width  = $height * ($info['width'] / $info['height']);
        }
        
        //缩放图片
        if ($info['width'] > $export_width || $info['height'] > $export_height || $zoom) {
            // 画图
            $resource = Thumb::draw($info['resource'], $info['mime'], 0, 0, $info['width'], $info['height'], 0, 0, $width, $height);
            
            // 输出图像
            Image::output($resource, $info['type'], ($this->save ? $this->export : ''));
            imagedestroy($info['resource']);
        } else {
            // 输出图像
            Image::output($info['resource'], $info['type'], ($this->save ? $this->export : ''));
        }
    }
    
    
    /**
     * 按指定宽度高度从图像中心裁剪图片
     * @throws AppException
     */
    public function cut()
    {
        // 获取图片信息和资源句柄
        $info        = Thumb::createImage($this->source);
        $source_gene = $info['width'] / $info['height'];
        $export_gene = $this->width / $this->height;
        
        if ($export_gene <= $source_gene) {
            $height = $this->height;
            $width  = $height * $source_gene;
        } else {
            $width  = $this->width;
            $height = $width * ($info['height'] / $info['width']);
        }
        
        // 先将图片缩放到接近宽高最小的比例
        $scale_resource = Thumb::draw($info['resource'], $info['mime'], 0, 0, $info['width'], $info['height'], 0, 0, $width, $height);
        
        // 计算裁剪坐标
        $source_x = ($width - $this->width) / 2;
        $source_y = ($height - $this->height) / 2;
        
        // 依据坐标裁剪
        $resource = Thumb::draw($scale_resource, $info['mime'], $source_x, $source_y, $this->width, $this->height, 0, 0, $this->width, $this->height);
        
        // 输出图像
        Image::output($resource, $info['type'], ($this->save ? $this->export : ''));
        imagedestroy($scale_resource);
        imagedestroy($info['resource']);
    }
    
    
    /**
     * 等比例缩放图片，如果缩放出来的宽高不够，则在不够的一方填充指定背景色
     * @param string $color 16位进制颜色值
     * @throws AppException
     */
    public function backFill($color = '#FFFFFF')
    {
        // 获取图片信息和资源句柄
        $info        = Thumb::createImage($this->source);
        $source_gene = $info['width'] / $info['height'];
        $export_gene = $this->width / $this->height;
        $color       = Thumb::to_rgb($color ? $color : '#FFFFFF');
        
        if ($export_gene <= $source_gene) {
            $width  = $this->width;
            $height = $width * ($info['height'] / $info['width']);
        } else {
            $height = $this->height;
            $width  = $height * ($info['width'] / $info['height']);
        }
        
        if (function_exists("imagecreatetruecolor")) {
            $image = imagecreatetruecolor($this->width, $this->height);
        } else {
            $image = imagecreate($this->width, $this->height);
        }
        
        $bg = imagecolorallocate($image, $color['r'], $color['g'], $color['b']);
        $x  = ($this->width - $width) / 2;
        $y  = ($this->width - $height) / 2;
        
        //填充背景颜色
        imagefilledrectangle($image, 0, 0, $this->width, $this->height, $bg);
        
        if ($info['width'] > $this->width || $info['height'] > $this->height) {
            $resource = Thumb::draw($info['resource'], $info['mime'], 0, 0, $info['width'], $info['height'], 0, 0, $width, $height);
            if ($width < $this->width) {
                imagecopy($image, $resource, $x, 0, 0, 0, $width, $height);
            } elseif ($height < $this->height) {
                imagecopy($image, $resource, 0, $y, 0, 0, $width, $height);
            } else {
                imagecopy($image, $resource, 0, 0, 0, 0, $width, $height);
            }
        } else {
            imagecopymerge($image, $info['resource'], $x, $y, 0, 0, $width, $height, 100);
        }
        
        // 输出图像
        Image::output($image, $info['type'], ($this->save ? $this->export : ''));
        imagedestroy($info['resource']);
    }
    
    
    /**
     * 重新设置宽度
     * @param int $width 宽度值
     */
    public function setWidth($width)
    {
        if (!is_null($width)) {
            $this->width = $width;
        }
    }
    
    
    /**
     * 重新设置宽度
     * @param int $height 宽度值
     */
    public function setHeight($height)
    {
        if (!is_null($height)) {
            $this->height = $height;
        }
    }
    
    
    /**
     * 重新设置是否保存
     * @param bool $save 是否保存
     */
    public function setSave($save)
    {
        if (!is_null($save)) {
            $this->save = $save;
        }
    }
    
    
    /**
     * 重新设置源图像
     * @param string $source 源图像路径
     */
    public function setSource($source)
    {
        if (!is_null($source)) {
            $this->source = $source;
        }
    }
    
    
    /**
     * 重新设置保存路径
     * @param string $export 要保存的路径
     */
    public function setExport($export)
    {
        if (!is_null($export)) {
            $this->export = $export;
        }
    }
    
    
    /**
     * 通过图片创建新的图像，并返回图像信息
     * @param string $source 图片文件路径
     * @return array
     * 返回array说明：
     * resource: 新图像资源句柄
     * width    : 源图像宽度
     * height    : 源图像高度
     * type    : 源图像后缀
     * size    : 源图像字节
     * mime    : 源图像文件类型
     * @throws AppException
     */
    public static function createImage($source)
    {
        // 验证图片是否存在
        if (!file_exists($source)) {
            throw new AppException('图片文件无效');
        }
        
        // 验证图片是否有效
        $info = Image::getImageInfo($source);
        if (false === $info || !$info['width'] || !$info['height']) {
            throw new AppException('图片文件无效');
        }
        
        // 验证类型
        // TODO MUST 大图片内存不够用
        switch ($info['mime']) {
            case 'image/jpeg' :
            case 'image/jpg' :
                $resource = imagecreatefromjpeg($source);
            break;
            case 'image/png' :
                $resource = imagecreatefrompng($source);
            break;
            case 'image/gif' :
                $resource = imagecreatefromgif($source);
            break;
            //case 'image/x-ms-bmp' :
            //	$resource = Thumb::imagecreatefrombmp($source);
            //break;
            default :
                throw new AppException('不支持的文件类型');
        }
        
        if (!$resource) {
            throw new AppException('imagecreatefrom error');
        }
        
        $info['resource'] = $resource;
        
        return $info;
    }
    
    
    /**
     * 将图像资源句柄画到新的画布上面
     * @param resource $sourceResource 源图像资源句柄
     * @param string   $sourceMime 源图像的mimeType
     * @param number   $sourceX 选择从源图像的X坐标开始剪切资源
     * @param number   $sourceY 选择从源图像的Y坐标开始剪切资源
     * @param number   $sourceWidth 从源图像剪切的宽度
     * @param number   $sourceHeight 从源图像剪切的高度
     * @param number   $newX 从新画布的X坐标开始画
     * @param number   $newY 从新画布的Y坐标开始画
     * @param number   $newWidth 画好的新图像宽度
     * @param number   $newHeight 画好的新图像高度
     * @return resource 画好的资源句柄
     * @throws AppException
     */
    public static function draw($sourceResource, $sourceMime, $sourceX, $sourceY, $sourceWidth, $sourceHeight, $newX, $newY, $newWidth, $newHeight)
    {
        //验证真彩函数是否存在
        if (function_exists("imagecreatetruecolor")) {
            $resource = imagecreatetruecolor($newWidth, $newHeight);
            if (!$resource) {
                throw new AppException('imagecreatetruecolor error');
            }
            
            //PNG黑,GIF黑
            if ($sourceMime == 'image/png' || $sourceMime == 'image/gif') {
                if (!imagealphablending($resource, false)) {
                    throw new AppException('imagealphablending error');
                }
                if (!imagesavealpha($resource, true)) {
                    throw new AppException('imagesavealpha error');
                }
            }
            
            
            if (!imagecopyresampled($resource, $sourceResource, $newX, $newY, $sourceX, $sourceY, $newWidth, $newHeight, $sourceWidth, $sourceHeight)) {
                throw new AppException('imagecopyresampled error');
            }
        } else {
            $resource = imagecreate($newWidth, $newHeight);
            if (!$resource) {
                throw new AppException('imagecreate error');
            }
            
            if (!imagecopyresized($resource, $sourceResource, $newX, $newY, $sourceX, $sourceY, $newWidth, $newHeight, $sourceWidth, $sourceHeight)) {
                throw new AppException('imagecopyresized error');
            }
        }
        
        return $resource;
    }
    
    
    /**
     * 验证图片是否是动态图片
     * @param string $source 图片文件路径
     * @return bool
     */
    public static function isDynamic($source)
    {
        $fp   = fopen($source, 'rb');
        $head = fread($fp, 1024);
        fclose($fp);
        
        return preg_match("/" . chr(0x21) . chr(0xff) . chr(0x0b) . 'NETSCAPE2.0' . "/", $head) ? true : false;
    }
    
    
    /**
     * 颜色转换
     * @param string $color 十六位进制颜色标识，默认白色'#FFFFFF'
     * @return array
     */
    public static function to_rgb($color = '#FFFFFF')
    {
        $color = str_replace('#', '', $color);
        if (strlen($color) > 3) {
            return array(
                'r' => hexdec(substr($color, 0, 2)),
                'g' => hexdec(substr($color, 2, 2)),
                'b' => hexdec(substr($color, 4, 2))
            );
        } else {
            return array(
                'r' => hexdec(substr($color, 0, 1) . substr($color, 0, 1)),
                'g' => hexdec(substr($color, 1, 1) . substr($color, 1, 1)),
                'b' => hexdec(substr($color, 2, 1) . substr($color, 2, 1))
            );
        }
    }
    
    
    /**
     * BMP 创建函数
     * @author simon
     * @param string $filename path of bmp file
     * @example who use,who knows
     * @return resource|false of GD
     */
    public static function imagecreatefrombmp($filename)
    {
        if (!$f1 = fopen($filename, "rb")) {
            return false;
        }
        
        $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1, 14));
        if ($FILE['file_type'] != 19778) {
            return false;
        }
        
        $BMP           = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' . '/Vcompression/Vsize_bitmap/Vhoriz_resolution' . '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1, 40));
        $BMP['colors'] = pow(2, $BMP['bits_per_pixel']);
        if ($BMP['size_bitmap'] == 0) {
            $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
        }
        $BMP['bytes_per_pixel']  = $BMP['bits_per_pixel'] / 8;
        $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
        $BMP['decal']            = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
        $BMP['decal'] -= floor($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
        $BMP['decal'] = 4 - (4 * $BMP['decal']);
        if ($BMP['decal'] == 4) {
            $BMP['decal'] = 0;
        }
        
        $PALETTE = array();
        if ($BMP['colors'] < 16777216) {
            $PALETTE = unpack('V' . $BMP['colors'], fread($f1, $BMP['colors'] * 4));
        }
        
        $IMG  = fread($f1, $BMP['size_bitmap']);
        $VIDE = chr(0);
        
        $res = imagecreatetruecolor($BMP['width'], $BMP['height']);
        $P   = 0;
        $Y   = $BMP['height'] - 1;
        while ($Y >= 0) {
            $X = 0;
            while ($X < $BMP['width']) {
                if ($BMP['bits_per_pixel'] == 32) {
                    $COLOR = unpack("V", substr($IMG, $P, 3));
                    $B     = ord(substr($IMG, $P, 1));
                    $G     = ord(substr($IMG, $P + 1, 1));
                    $R     = ord(substr($IMG, $P + 2, 1));
                    $color = imagecolorexact($res, $R, $G, $B);
                    if ($color == -1) {
                        $color = imagecolorallocate($res, $R, $G, $B);
                    }
                    $COLOR[0] = $R * 256 * 256 + $G * 256 + $B;
                    $COLOR[1] = $color;
                } elseif ($BMP['bits_per_pixel'] == 24) {
                    $COLOR = unpack("V", substr($IMG, $P, 3) . $VIDE);
                } elseif ($BMP['bits_per_pixel'] == 16) {
                    $COLOR    = unpack("n", substr($IMG, $P, 2));
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                } elseif ($BMP['bits_per_pixel'] == 8) {
                    $COLOR    = unpack("n", $VIDE . substr($IMG, $P, 1));
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                } elseif ($BMP['bits_per_pixel'] == 4) {
                    $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                    if (($P * 2) % 2 == 0) {
                        $COLOR[1] = ($COLOR[1] >> 4);
                    } else {
                        $COLOR[1] = ($COLOR[1] & 0x0F);
                    }
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                } elseif ($BMP['bits_per_pixel'] == 1) {
                    $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                    if (($P * 8) % 8 == 0) {
                        $COLOR[1] = $COLOR[1] >> 7;
                    } elseif (($P * 8) % 8 == 1) {
                        $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
                    } elseif (($P * 8) % 8 == 2) {
                        $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
                    } elseif (($P * 8) % 8 == 3) {
                        $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
                    } elseif (($P * 8) % 8 == 4) {
                        $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
                    } elseif (($P * 8) % 8 == 5) {
                        $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
                    } elseif (($P * 8) % 8 == 6) {
                        $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
                    } elseif (($P * 8) % 8 == 7) {
                        $COLOR[1] = ($COLOR[1] & 0x1);
                    }
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                } else {
                    return false;
                }
                imagesetpixel($res, $X, $Y, $COLOR[1]);
                $X++;
                $P += $BMP['bytes_per_pixel'];
            }
            $Y--;
            $P += $BMP['decal'];
        }
        fclose($f1);
        
        return $res;
    }
} 