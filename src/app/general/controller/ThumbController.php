<?php

namespace BusyPHP\app\general\controller;

use BusyPHP\App;
use BusyPHP\app\admin\setting\ThumbSetting;
use BusyPHP\app\admin\setting\WatermarkSetting;
use BusyPHP\Controller;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\file\Image;
use DomainException;
use Exception;
use RangeException;
use think\exception\FileException;

/**
 * 动态缩图
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/26 下午下午3:44 ThumbController.php $
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
            $src = $this->param('src/s', 'trim');
            $src = ltrim($src, '/');
            if (!$src) {
                throw new ParamInvalidException('src');
            }
            
            // 解析图片地址
            $pathInfo  = pathinfo($src);
            $filename  = $pathInfo['filename'] ?? '';
            $extension = $pathInfo['extension'] ?? 'jpeg';
            $dirname   = $pathInfo['dirname'] ?? '';
            
            
            // 获取缩图方式
            if (!$dirname) {
                throw new ParamInvalidException('src');
            }
            if (false === $thumbTypeIndex = strpos($dirname, '/')) {
                $thumbType = $dirname;
            } else {
                $thumbType = substr($dirname, 0, $thumbTypeIndex);
                $dirname   = substr($dirname, $thumbTypeIndex + 1);
            }
            
            
            // 获取尺寸配置
            if (false === $thumbSizeIndex = strrpos($filename, '_')) {
                throw new ParamInvalidException('src');
            }
            $thumbSize  = substr($filename, $thumbSizeIndex + 1);
            $filename   = substr($filename, 0, $thumbSizeIndex);
            $sourceFile = App::urlToPath($dirname . DIRECTORY_SEPARATOR . $filename . '.' . $extension);
            if (!$thumbType || !$thumbSize || !$filename) {
                throw new ParamInvalidException('src');
            }
            
            // 解析尺寸
            $thumbSetting = ThumbSetting::init();
            if ($thumbSetting->isUnlimitedSize()) {
                $size = str_replace('x', '-', strtolower($thumbSize));
                $size = false === strpos($size, '-') ? $size . '-' : $size;
                [$width, $height] = explode('-', $size);
            } else {
                if (!$sizes = $thumbSetting->getSize($thumbSize)) {
                    throw new RangeException('配置不存在: ' . $thumbSize);
                }
                $width  = $sizes['width'];
                $height = $sizes['height'];
            }
            
            
            $width  = (int) $width;
            $height = (int) $height;
            $height = $height <= 0 ? $width : $height;
            if ($width < 1 || $height < 1) {
                throw new DomainException("尺寸无效, width: {$width}, height: {$height}");
            }
            
            
            // 无图
            $isError = false;
            if ($filename == $thumbSetting->getEmptyImageVar() || !is_file($sourceFile)) {
                $isError    = true;
                $sourceFile = $thumbSetting->getErrorPlaceholder(true);
                if (!$sourceFile || !is_file($sourceFile)) {
                    throw new FileException("错误占位图不存在: {$sourceFile}");
                }
            }
            
            // 实例化图片缩放
            $thumb = new Image();
            $thumb->src($sourceFile)
                ->format($extension)
                ->save($isError ? false : $thumbSetting->isSaveLocal(), $this->app->getPublicPath("thumbs/{$src}"))
                ->width($width)
                ->height($height)
                ->thumb($thumbType)
                ->bgColor($thumbSetting->getBgColor());
            
            // 加水印
            $watermarkSetting = WatermarkSetting::init();
            if ($thumbSetting->isWatermark() && !$isError) {
                $thumb->watermark($watermarkSetting->getFile(), Image::numberToWatermarkPosition($watermarkSetting->getPosition()), $watermarkSetting->getOpacity(), $watermarkSetting->getOffsetX(), $watermarkSetting->getOffsetY(), $watermarkSetting->getOffsetRotate());
            }
            
            return $thumb->exec(true);
        } catch (Exception $e) {
            abort(404, $e->getMessage());
            
            return null;
        }
    }
}