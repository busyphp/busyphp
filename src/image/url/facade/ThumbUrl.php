<?php
declare(strict_types = 1);

namespace BusyPHP\image\url\facade;

use think\Facade;

/**
 * 动态缩图URL生成
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/12 下午4:39 下午 Url.php $
 * @mixin \BusyPHP\image\url\ThumbUrl
 * @see \BusyPHP\image\url\ThumbUrl
 */
class ThumbUrl extends Facade
{
    protected static $alwaysNewInstance = true;
    
    
    protected static function getFacadeClass()
    {
        return \BusyPHP\image\url\ThumbUrl::class;
    }
}