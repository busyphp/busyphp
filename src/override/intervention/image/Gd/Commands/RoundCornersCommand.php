<?php

namespace Intervention\Image\Gd\Commands;

use Intervention\Image\Commands\AbstractCommand;
use Intervention\Image\Image;

/**
 * GD库圆角处理命令
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/12 11:17 AM RoundCornersCommand.php $
 */
class RoundCornersCommand extends AbstractCommand
{
    /**
     * Executes current command on given image
     *
     * @param Image $image
     * @return bool
     */
    public function execute($image) : bool
    {
        $rx = $this->argument(0)->type('numeric')->required()->value();
        $ry = $this->argument(1)->type('numeric')->required()->value();
        
        $core        = $image->getCore();
        $imageWidth  = $image->width();
        $imageHeight = $image->height();
        $triple      = imagecreatetruecolor($rx * 6, $ry * 6);
        $mask        = imagecreatetruecolor($imageWidth, $imageHeight);
        $transparent = imagecolorallocate($triple, 255, 255, 255);
        imagefilledellipse($triple, $rx * 3, $ry * 3, $rx * 4, $ry * 4, $transparent);
        imagefilledrectangle($mask, 0, 0, $imageWidth, $imageHeight, $transparent);
        
        imagecopyresampled(
            $mask,
            $triple,
            0,
            0,
            $rx,
            $ry,
            $rx,
            $ry,
            $rx * 2,
            $ry * 2
        );
        
        imagecopyresampled(
            $mask,
            $triple,
            0,
            $imageHeight - $ry,
            $rx,
            $ry * 3,
            $rx,
            $ry,
            $rx * 2,
            $ry * 2
        );
        
        imagecopyresampled(
            $mask,
            $triple,
            $imageWidth - $rx,
            $imageHeight - $ry,
            $rx * 3,
            $ry * 3,
            $rx,
            $ry,
            $rx * 2,
            $ry * 2
        );
        
        imagecopyresampled(
            $mask,
            $triple,
            $imageWidth - $rx,
            0,
            $rx * 3,
            $ry,
            $rx,
            $ry,
            $rx * 2,
            $ry * 2
        );
        
        
        $resized = imagecreatetruecolor($imageWidth, $imageHeight);
        $blend   = imagecreatetruecolor($imageWidth, $imageHeight);
        
        imagecopyresampled(
            $resized,
            $mask,
            0,
            0,
            0,
            0,
            $imageWidth,
            $imageHeight,
            imagesx($mask),
            imagesy($mask)
        );
        
        imagefilledrectangle(
            $blend,
            0,
            0,
            $imageWidth,
            $imageHeight,
            imagecolorallocate($blend, 0, 0, 0)
        );
        imagealphablending($blend, false);
        imagesavealpha($blend, true);
        
        for ($x = 0; $x < $imageWidth; $x++) {
            for ($y = 0; $y < $imageHeight; $y++) {
                $corePixel    = imagecolorsforindex($core, imagecolorat($core, $x, $y));
                $resizedPixel = imagecolorsforindex($resized, imagecolorat($resized, $x, $y));
                $gray         = round(($resizedPixel['red'] * 0.30) + ($resizedPixel['green'] * 0.59) + ($resizedPixel['blue'] * 0.11));
                
                imagesetpixel(
                    $blend,
                    $x,
                    $y,
                    imagecolorallocatealpha(
                        $blend,
                        $corePixel['red'],
                        $corePixel['green'],
                        $corePixel['blue'],
                        127 - (floor($gray / 2) * (1 - ($corePixel['alpha'] / 127)))
                    )
                );
            }
        }
        
        imagealphablending($core, false);
        imagesavealpha($core, true);
        imagecopy(
            $core,
            $blend,
            0,
            0,
            0,
            0,
            $imageWidth,
            $imageHeight
        );
        
        // destroy
        imagedestroy($blend);
        imagedestroy($resized);
        imagedestroy($triple);
        imagedestroy($mask);
        
        return true;
    }
}