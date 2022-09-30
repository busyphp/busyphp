<?php

namespace Intervention\Image\Gd\Commands;

use Intervention\Image\Commands\AbstractCommand;
use Intervention\Image\Image;

/**
 * 删除图片元信息命令
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/12 6:25 PM StripMetaCommand.php $
 */
class StripMetaCommand extends AbstractCommand
{
    /**
     * Executes current command on given image
     *
     * @param Image $image
     * @return mixed
     */
    public function execute($image) : bool
    {
        // TODO GD库会直接删除EXIF信息，以后再研究如何保留
        
        return true;
    }
}