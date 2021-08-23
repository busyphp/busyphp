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
 * @method static \BusyPHP\image\url\ThumbUrl url(string $url) 设置图片URL
 * @method static \BusyPHP\image\url\ThumbUrl type(string $type) 设置缩图类型
 * @method static \BusyPHP\image\url\ThumbUrl size(string $size) 设置缩图配置
 * @method static string build() 生成URL
 */
class ThumbUrl extends Facade
{
    protected static $alwaysNewInstance = true;
    
    
    protected static function getFacadeClass()
    {
        return \BusyPHP\image\url\ThumbUrl::class;
    }
}