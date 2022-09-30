<?php

namespace Intervention\Image\Imagick\Commands;

use Intervention\Image\Commands\AbstractCommand;
use Intervention\Image\Image;

/**
 * 删除图片元信息命令
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/12 6:26 PM StripMetaCommand.php $
 */
class StripMetaCommand extends AbstractCommand
{
    /**
     * Executes current command on given image
     *
     * @param Image $image
     * @return bool
     */
    public function execute($image) : bool
    {
        $core     = $image->getCore();
        $profiles = $core->getImageProfiles("icc", true);
        $core->stripImage();
        if (!empty($profiles)) {
            $core->profileImage("icc", $profiles['icc']);
        }
        
        return true;
    }
}