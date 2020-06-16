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

/**
 * 图像操作类库
 * @author liu21st <liu21st@gmail.com>
 * @author busy^life <busy.life@qq.com>
 * @version $Id: 2018/1/17 上午10:53 Image.php busy^life $
 */
class Image
{
    /**
     * 取得图像信息
     * @param string $image 图像文件名
     * @return array|false
     */
    public static function getImageInfo($image)
    {
        $imageInfo = getimagesize($image);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($image);
            $info      = array(
                "width"  => $imageInfo[0],
                "height" => $imageInfo[1],
                "type"   => $imageType ? $imageType : 'jpeg',
                "size"   => $imageSize,
                "mime"   => $imageInfo['mime']
            );
            
            return $info;
        } else {
            return false;
        }
    }
    
    
    /**
     * 为图片添加水印
     * @static public
     * @param string $source 原文件名
     * @param string $water 水印图片
     * @param int    $pos 水印位置<pre>
     * 0 : 随机
     * 1 : 顶端居左
     * 2 : 顶端居中
     * 3 : 顶端居右
     * 4 : 中部居左
     * 5 : 中部居中
     * 6 : 中部居右
     * 7 : 底端居左
     * 8 : 底端居中
     * 9 : 底端居右（默认）
     * </pre>
     * @param int    $alpha 水印的透明度
     * @param float  $posX 水印位置X坐标
     * @param float  $posY 水印位置Y坐标
     * @param string $outPath 输出路径
     * @return bool
     */
    public static function water($source, $water, $pos = 9, $alpha = 80, $posX = null, $posY = null, $outPath = null)
    {
        //检查文件是否存在
        if (!file_exists($source) || !file_exists($water)) {
            return false;
        }
        
        
        $unlink = true;
        if (!is_null($outPath)) {
            $unlink = false;
        } else {
            $outPath = $source;
        }
        
        //图片信息
        $sInfo = self::getImageInfo($source);
        $wInfo = self::getImageInfo($water);
        
        //如果图片小于水印图片，不生成图片
        if ($sInfo["width"] < $wInfo["width"] || $sInfo['height'] < $wInfo['height']) {
            return false;
        }
        
        //建立图像
        $sCreateFun = "imagecreatefrom" . $sInfo['type'];
        $sImage     = $sCreateFun($source);
        $wCreateFun = "imagecreatefrom" . $wInfo['type'];
        $wImage     = $wCreateFun($water);
        
        //设定图像的混色模式
        if (false === imagealphablending($wImage, true)) {
            return false;
        }
        
        
        if (is_null($posX) && is_null($posY)) {
            //图像位置,默认为右下角右对齐
            $posY = $sInfo["height"] - $wInfo["height"];
            $posX = $sInfo["width"] - $wInfo["width"];
            
            //1为顶端居左
            switch ($pos) {
                case 1:
                    $posX = 0;
                    $posY = 0;
                break;
                //2为顶端居中
                case 2:
                    $posX = ($sInfo["width"] - $wInfo["width"]) / 2;
                    $posY = 0;
                break;
                //3为顶端居右
                case 3:
                    $posX = $sInfo["width"] - $wInfo["width"];
                    $posY = 0;
                break;
                //4为中部居左
                case 4:
                    $posX = 0;
                    $posY = ($sInfo["height"] - $wInfo["height"]) / 2;
                break;
                //5为中部居中
                case 5:
                    $posX = ($sInfo["width"] - $wInfo["width"]) / 2;
                    $posY = ($sInfo["height"] - $wInfo["height"]) / 2;
                break;
                //6为中部居右
                case 6:
                    $posX = $sInfo["width"] - $wInfo["width"];
                    $posY = ($sInfo["height"] - $wInfo["height"]) / 2;
                break;
                //7为底端居左
                case 7:
                    $posX = 0;
                    $posY = $sInfo["height"] - $wInfo["height"];
                break;
                //8为底端居中
                case 8:
                    $posX = ($sInfo["width"] - $wInfo["width"]) / 2;
                    $posY = $sInfo["height"] - $wInfo["height"];
                break;
                //9为底端居右
                case 9:
                    $posX = $sInfo["width"] - $wInfo["width"];
                    $posY = $sInfo["height"] - $wInfo["height"];
                break;
                //随机
                case 0:
                    $posX = rand(0, ($sInfo["width"] - $wInfo["width"]));
                    $posY = rand(0, ($sInfo["height"] - $wInfo["height"]));
                break;
            }
        }
        
        // 生成混合图像
        // PNG透明
        if ($wInfo['mime'] == 'image/png') {
            imagesavealpha($wImage, true);
            imagecopy($sImage, $wImage, $posX, $posY, 0, 0, $wInfo['width'], $wInfo['height']);
        } else {
            imagecopymerge($sImage, $wImage, $posX, $posY, 0, 0, $wInfo['width'], $wInfo['height'], $alpha);
        }
        //输出图像
        $ImageFun = 'Image' . $sInfo['type'];
        
        //保存图像
        if ($unlink) {
            @unlink($source);
        }
        
        $ImageFun($sImage, $outPath);
        imagedestroy($sImage);
        
        return true;
    }
    
    
    /**
     * 生成缩略图
     * @param string  $image 原图
     * @param string  $thumbName 缩略图文件名
     * @param string  $type 图像格式
     * @param int     $maxWidth 宽度
     * @param int     $maxHeight 高度
     * @param boolean $interlace 启用隔行扫描
     * @return boolean
     */
    public static function thumb($image, $thumbName, $type = '', $maxWidth = 200, $maxHeight = 50, $interlace = true)
    {
        // 获取原图信息
        $info = Image::getImageInfo($image);
        if ($info !== false) {
            $srcWidth  = $info['width'];
            $srcHeight = $info['height'];
            $type      = empty($type) ? $info['type'] : $type;
            $type      = strtolower($type);
            $interlace = $interlace ? 1 : 0;
            unset($info);
            $scale = min($maxWidth / $srcWidth, $maxHeight / $srcHeight); // 计算缩放比例
            if ($scale >= 1) {
                // 超过原图大小不再缩略
                $width  = $srcWidth;
                $height = $srcHeight;
            } else {
                // 缩略图尺寸
                $width  = (int)($srcWidth * $scale);
                $height = (int)($srcHeight * $scale);
            }
            
            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            if (!function_exists($createFun)) {
                return false;
            }
            $srcImg = $createFun($image);
            
            //创建缩略图
            if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
                $thumbImg = imagecreatetruecolor($width, $height);
            } else {
                $thumbImg = imagecreate($width, $height);
            }
            //png和gif的透明处理 by luofei614
            if ('png' == $type) {
                imagealphablending($thumbImg, false);//取消默认的混色模式（为解决阴影为绿色的问题）
                imagesavealpha($thumbImg, true);//设定保存完整的 alpha 通道信息（为解决阴影为绿色的问题）
            } elseif ('gif' == $type) {
                $trnprt_indx = imagecolortransparent($srcImg);
                if ($trnprt_indx >= 0) {
                    //its transparent
                    $trnprt_color = imagecolorsforindex($srcImg, $trnprt_indx);
                    $trnprt_indx  = imagecolorallocate($thumbImg, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                    imagefill($thumbImg, 0, 0, $trnprt_indx);
                    imagecolortransparent($thumbImg, $trnprt_indx);
                }
            }
            // 复制图片
            if (function_exists("ImageCopyResampled")) {
                imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            } else {
                imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            }
            
            // 对jpeg图形设置隔行扫描
            if ('jpg' == $type || 'jpeg' == $type) {
                imageinterlace($thumbImg, $interlace);
            }
            
            // 生成图片
            $imageFun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
            $imageFun($thumbImg, $thumbName);
            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            
            return $thumbName;
        }
        
        return false;
    }
    
    
    /**
     * 把图像转换成字符显示
     * @static
     * @access public
     * @param string $image 要显示的图像
     * @param string $string 显示字符
     * @param string $type 图像类型，默认自动获取
     * @return string
     */
    public static function showASCIIImg($image, $string = '', $type = '')
    {
        $info = Image::getImageInfo($image);
        if ($info !== false) {
            $type = empty($type) ? $info['type'] : $type;
            unset($info);
            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            $im        = $createFun($image);
            $dx        = imagesx($im);
            $dy        = imagesy($im);
            $i         = 0;
            $out       = '<span style="padding:0px;margin:0;line-height:100%;font-size:1px;">';
            set_time_limit(0);
            for ($y = 0; $y < $dy; $y++) {
                for ($x = 0; $x < $dx; $x++) {
                    $col = imagecolorat($im, $x, $y);
                    $rgb = imagecolorsforindex($im, $col);
                    $str = empty($string) ? '*' : $string[$i++];
                    $out .= sprintf('<span style="margin:0px;color:#%02x%02x%02x">' . $str . '</span>', $rgb['red'], $rgb['green'], $rgb['blue']);
                }
                $out .= "<br>\n";
            }
            $out .= '</span>';
            imagedestroy($im);
            
            return $out;
        }
        
        return false;
    }
    
    
    /**
     * 生成UPC-A条形码
     * @static
     * @param string $code 代码
     * @param string $type 图像格式
     * @param int    $lw 单元宽度
     * @param int    $hi 条码高度
     */
    static function UPCA($code, $type = 'png', $lw = 2, $hi = 100)
    {
        static $Lencode = array(
            '0001101',
            '0011001',
            '0010011',
            '0111101',
            '0100011',
            '0110001',
            '0101111',
            '0111011',
            '0110111',
            '0001011'
        );
        static $Rencode = array(
            '1110010',
            '1100110',
            '1101100',
            '1000010',
            '1011100',
            '1001110',
            '1010000',
            '1000100',
            '1001000',
            '1110100'
        );
        $ends   = '101';
        $center = '01010';
        /* UPC-A Must be 11 digits, we compute the checksum. */
        if (strlen($code) != 11) {
            die("UPC-A Must be 11 digits.");
        }
        /* Compute the EAN-13 Checksum digit */
        $ncode = '0' . $code;
        $even  = 0;
        $odd   = 0;
        for ($x = 0; $x < 12; $x++) {
            if ($x % 2) {
                $odd += $ncode[$x];
            } else {
                $even += $ncode[$x];
            }
        }
        $code .= (10 - (($odd * 3 + $even) % 10)) % 10;
        /* Create the bar encoding using a binary string */
        $bars = $ends;
        $bars .= $Lencode[$code[0]];
        for ($x = 1; $x < 6; $x++) {
            $bars .= $Lencode[$code[$x]];
        }
        $bars .= $center;
        for ($x = 6; $x < 12; $x++) {
            $bars .= $Rencode[$code[$x]];
        }
        $bars .= $ends;
        /* Generate the Barcode Image */
        if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor($lw * 95 + 30, $hi + 30);
        } else {
            $im = imagecreate($lw * 95 + 30, $hi + 30);
        }
        $fg = ImageColorAllocate($im, 0, 0, 0);
        $bg = ImageColorAllocate($im, 255, 255, 255);
        ImageFilledRectangle($im, 0, 0, $lw * 95 + 30, $hi + 30, $bg);
        $shift = 10;
        for ($x = 0; $x < strlen($bars); $x++) {
            if (($x < 10) || ($x >= 45 && $x < 50) || ($x >= 85)) {
                $sh = 10;
            } else {
                $sh = 0;
            }
            if ($bars[$x] == '1') {
                $color = $fg;
            } else {
                $color = $bg;
            }
            ImageFilledRectangle($im, ($x * $lw) + 15, 5, ($x + 1) * $lw + 14, $hi + 5 + $sh, $color);
        }
        /* Add the Human Readable Label */
        ImageString($im, 4, 5, $hi - 5, $code[0], $fg);
        for ($x = 0; $x < 5; $x++) {
            ImageString($im, 5, $lw * (13 + $x * 6) + 15, $hi + 5, $code[$x + 1], $fg);
            ImageString($im, 5, $lw * (53 + $x * 6) + 15, $hi + 5, $code[$x + 6], $fg);
        }
        ImageString($im, 4, $lw * 95 + 17, $hi - 5, $code[11], $fg);
        /* Output the Header and Content. */
        Image::output($im, $type);
    }
    
    
    /**
     * 输出图像
     * @param resource $im 图片资源句柄
     * @param string   $type 输出类型
     * @param string   $filename 保存的文件路径
     */
    public static function output($im, $type = 'png', $filename = '')
    {
        $ImageFun = 'image' . $type;
        if (empty($filename)) {
            header("Content-type: image/" . $type);
            $ImageFun($im);
        } else {
            $ImageFun($im, $filename);
        }
        imagedestroy($im);
    }
}