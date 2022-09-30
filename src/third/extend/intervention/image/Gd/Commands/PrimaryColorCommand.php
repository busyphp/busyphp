<?php

namespace Intervention\Image\Gd\Commands;

use Intervention\Image\Commands\AbstractCommand;
use Intervention\Image\Gd\Color;
use Intervention\Image\Image;

/**
 * 获取图片主色命令
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/12 5:47 PM PrimaryColorCommand.php $
 */
class PrimaryColorCommand extends AbstractCommand
{
    /**
     * Executes current command on given image
     *
     * @param Image $image
     * @return mixed
     */
    public function execute($image) : bool
    {
        $width  = $image->width();
        $height = $image->height();
        $r      = 0;
        $g      = 0;
        $b      = 0;
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($image->getCore(), $x, $y);
                $r   += $rgb >> 16;
                $g   += $rgb >> 8 & 255;
                $b   += $rgb & 255;
            }
        }
        
        $pxl   = $width * $height;
        $color = new Color([round($r / $pxl), round($g / $pxl), round($b / $pxl)]);
        
        $this->setOutput($color->format('hex'));
        
        return true;
    }
}