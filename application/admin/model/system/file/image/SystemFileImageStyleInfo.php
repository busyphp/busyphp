<?php

namespace BusyPHP\app\admin\model\system\file\image;

use BusyPHP\image\result\ImageStyleResult;
use ReflectionException;

/**
 * SystemFileImageStyleInfo
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/15 11:34 AM SystemFileImageStyleInfo.php $
 */
class SystemFileImageStyleInfo extends SystemFileImageStyleField
{
    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    protected function onParseAfter() : void
    {
        $this->content = ImageStyleResult::fillContent($this->content);
    }
}