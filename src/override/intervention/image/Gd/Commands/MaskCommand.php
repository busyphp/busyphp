<?php

namespace Intervention\Image\Gd\Commands;

use Intervention\Image\Commands\AbstractCommand;
use Intervention\Image\Image;

/**
 * Mask指令
 * 主要为了解决官方库速度慢的问题(主要是 {@see Image::pickColor()} 中的 new Color() 效率太低)
 * 覆盖 vendor/intervention/image/src/Intervention/Image/Gd/Commands/MaskCommand.php
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/12 12:37 PM MaskCommand.php $
 */
class MaskCommand extends AbstractCommand
{
    /**
     * Applies an alpha mask to an image
     *
     * @param Image $image
     * @return boolean
     */
    public function execute($image) : bool
    {
        $mask_source  = $this->argument(0)->value();
        $mask_w_alpha = $this->argument(1)->type('bool')->value(false);
        
        $image_size = $image->getSize();
        
        // create empty canvas
        $canvas = $image->getDriver()->newImage($image_size->width, $image_size->height, [0, 0, 0, 0]);
        
        // build mask image from source
        $mask      = $image->getDriver()->init($mask_source);
        $mask_size = $mask->getSize();
        
        // resize mask to size of current image (if necessary)
        if ($mask_size != $image_size) {
            $mask->resize($image_size->width, $image_size->height);
        }
        
        imagealphablending($canvas->getCore(), false);
        
        if (!$mask_w_alpha) {
            // mask from greyscale image
            imagefilter($mask->getCore(), IMG_FILTER_GRAYSCALE);
        }
        
        // redraw old image pixel by pixel considering alpha map
        for ($x = 0; $x < $image_size->width; $x++) {
            for ($y = 0; $y < $image_size->height; $y++) {
                $color          = imagecolorsforindex($image->getCore(), imagecolorat($image->getCore(), $x, $y));
                $color['alpha'] = round(1 - $color['alpha'] / 127, 2);
                $color          = [$color['red'], $color['green'], $color['blue'], $color['alpha']];
                
                $alpha          = imagecolorsforindex($mask->getCore(), imagecolorat($mask->getCore(), $x, $y));
                $alpha['alpha'] = round(1 - $alpha['alpha'] / 127, 2);
                $alpha          = [$alpha['red'], $alpha['green'], $alpha['blue'], $alpha['alpha']];
                
                
                if ($mask_w_alpha) {
                    $alpha = $alpha[3]; // use alpha channel as mask
                } else {
                    if ($alpha[3] == 0) { // transparent as black
                        $alpha = 0;
                    } else {
                        // $alpha = floatval(round((($alpha[0] + $alpha[1] + $alpha[3]) / 3) / 255, 2));
                        
                        // image is greyscale, so channel doesn't matter (use red channel)
                        $alpha = floatval(round($alpha[0] / 255, 2));
                    }
                }
                
                // preserve alpha of original image...
                if ($color[3] < $alpha) {
                    $alpha = $color[3];
                }
                
                // replace alpha value
                $color[3] = $alpha;
                $oldMin   = 0;
                $oldMax   = 1;
                $newMin   = 127;
                $newMax   = 0;
                $color[3] = ceil(((($color[3] - $oldMin) * ($newMax - $newMin)) / ($oldMax - $oldMin)) + $newMin);
                
                // redraw pixel
                imagesetpixel($canvas->getCore(), $x, $y, ($color[3] << 24) + ($color[0] << 16) + ($color[1] << 8) + $color[2]);
            }
        }
        
        
        // replace current image with masked instance
        $image->setCore($canvas->getCore());
        
        return true;
    }
}
