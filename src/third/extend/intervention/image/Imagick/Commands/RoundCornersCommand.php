<?php

namespace Intervention\Image\Imagick\Commands;

use Intervention\Image\Commands\AbstractCommand;
use Intervention\Image\Image;

/**
 * Imagick库圆角处理命令
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
     * @throws \ImagickDrawException
     * @throws \ImagickException
     */
    public function execute($image) : bool
    {
        $rx = $this->argument(0)->type('numeric')->required()->value();
        $ry = $this->argument(1)->type('numeric')->required()->value();
        
        /** @var \Imagick $api */
        $api   = $image->getCore();
        $shape = new \ImagickDraw();
        $shape->setFillColor(new \ImagickPixel('black'));
        $shape->roundRectangle(0, 0, $image->width(), $image->height(), $rx, $ry);
        
        $mask = new \Imagick();
        $mask->newImage($image->width(), $image->height(), new \ImagickPixel('transparent'), 'png');
        $mask->drawImage($shape);
        $api->compositeImage($mask, \Imagick::COMPOSITE_DSTIN, 0, 0);
        
        return true;
    }
}