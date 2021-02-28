<?php

namespace BusyPHP\app\general\controller;

use BusyPHP\App;
use BusyPHP\Controller;
use BusyPHP\exception\AppException;
use BusyPHP\image\Image;
use BusyPHP\image\url\ThumbUrl;
use BusyPHP\app\admin\setting\FileSetting;
use think\Exception;

/**
 * 动态缩图
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/11 下午6:09 下午 ThumbController.php $
 */
class ThumbController extends Controller
{
    /**
     * 动态缩图
     */
    public function index()
    {
        try {
            // 图片地址
            $src = trim($this->iParam('src'));
            $src = ltrim($src, '/');
            if (!$src) {
                throw new AppException('图片地址不存在');
            }
            
            // 解析地址中最后一个_的位置
            if (false === $sizeIndex = strrpos($src, '_')) {
                throw new AppException('无法解析图片地址: ' . $src);
            }
            
            // 获取地址中最后一个.的位置
            if (false === $dotIndex = strrpos($src, '.')) {
                throw new AppException('无法解析图片地址: ' . $src);
            }
            
            $typeIndex = strpos($src, '/');
            $type      = substr($src, 0, $typeIndex);
            $size      = substr($src, $sizeIndex + 1, $dotIndex - $sizeIndex - 1);
            $filename  = substr($src, $typeIndex + 1, $sizeIndex - $typeIndex - 1);
            $extension = substr($src, $dotIndex + 1);
            if (!$size || !$extension || !$filename) {
                throw new AppException('无法解析图片地址: ' . $src);
            }
            
            // 解析尺寸
            $config          = (new ThumbUrl())->getConfig()->get('thumb');
            $config['sizes'] = $config['sizes'] ?? null;
            if (is_array($config['sizes'])) {
                if ($sizeConfig = $config['sizes'][$size] ?? null) {
                    [$width, $height] = $sizeConfig;
                } elseif (in_array($size, array_values($config['sizes']))) {
                    [$width, $height] = $this->parseSize($size);
                } else {
                    throw new AppException('配置不存在: ' . $size);
                }
            } else {
                [$width, $height] = $this->parseSize($size);
            }
            
            
            $width  = intval($width);
            $height = intval($height);
            $height = $height <= 0 ? $width : $height;
            if ($width < 1 || $height < 1) {
                throw new AppException("尺寸无效, width: {$width}, height: {$height}");
            }
            
            // 拼接源图路径
            $noWatermark = false;
            if ($filename === ($config['empty_image_var'] ?: ThumbUrl::EMPTY_IMAGE_VAR)) {
                $noWatermark = true;
                $source      = $config['empty_image_path'] ?: App::getPublicPath('assets') . 'no_image.jpeg';
                if (!$source) {
                    throw new AppException('没有配置无图图片资源路径: empty_image_path');
                }
                if (!is_file($source)) {
                    throw new AppException('无图图片资源不存在: ' . $source);
                }
            } else {
                $source = App::getPublicPath() . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $filename), DIRECTORY_SEPARATOR) . '.' . $extension;
                if (!is_file($source)) {
                    throw new AppException('图片资源不存在: ' . $src);
                }
            }
            
            
            // 图片缩放
            $thumb = new Image();
            $thumb->src($source)
                ->format($extension)
                ->save($config['save_local'] ?? false, App::getPublicPath('thumbs') . ltrim($src, '/'), true)
                ->width($width)
                ->height($height)
                ->thumb($type)
                ->bgColor($config['bg_color'] ?: 'FFFFFF');
            
            // 水印
            if (($config['watermark_status'] ?? false) && !$noWatermark) {
                $fileSetting   = FileSetting::init();
                $waterPath     = $config['watermark_image_path'] ?: '';
                $waterPosition = $config['watermark_position'] ?: '';
                if (!$waterPosition) {
                    switch ($fileSetting->getWatermarkPosition()) {
                        case 4:
                        case 1:
                            $waterPosition = Image::P_TOP_LEFT;
                        break;
                        case 2:
                            $waterPosition = Image::P_TOP;
                        break;
                        case 6:
                        case 3:
                            $waterPosition = Image::P_TOP_RIGHT;
                        break;
                        case 5:
                            $waterPosition = Image::P_CENTER;
                        break;
                        case 7:
                            $waterPosition = Image::P_BOTTOM_LEFT;
                        break;
                        case 8:
                            $waterPosition = Image::P_BOTTOM;
                        break;
                        case 0:
                            $waterPosition = Image::P_FILL;
                        break;
                        case 9:
                        default:
                            $waterPosition = Image::P_BOTTOM_RIGHT;
                    }
                }
                if (!$waterPath) {
                    $waterPath = App::urlToPath($fileSetting->getWatermarkFile());
                }
                
                if (is_file($waterPath)) {
                    $thumb->watermark($waterPath, $waterPosition, $config['watermark_opacity'] ?? 25, $config['watermark_x'] ?? 0, $config['watermark_y'] ?? 0);
                }
            }
            
            $content = $thumb->exec(true);
            
            return response($content, 200, ['Content-Length' => strlen($content)])->contentType($thumb->getMimeType());
        } catch (Exception $e) {
            abort(404, $e->getMessage());
            
            return null;
        }
    }
    
    
    /**
     * 解析尺寸
     * @param $size
     * @return array
     */
    private function parseSize($size) : array
    {
        $size   = str_replace('x', '-', strtolower($size));
        $size   = false === strpos($size, '-') ? $size . '-' : $size;
        $expArr = explode('-', $size);
        
        return $expArr ? $expArr : [0, 0];
    }
}