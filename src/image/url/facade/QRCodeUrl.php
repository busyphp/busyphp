<?php
declare(strict_types = 1);

namespace BusyPHP\image\url\facade;

use think\Facade;

/**
 * 动态二维码URL生成
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/12 下午7:41 下午 QRCodeUrl.php $
 * @mixin \BusyPHP\image\url\QRCodeUrl
 * @see \BusyPHP\image\url\QRCodeUrl
 * @method \BusyPHP\image\url\QRCodeUrl text(string $text) 设置文本
 * @method \BusyPHP\image\url\QRCodeUrl size(int $size) 设置尺寸
 * @method \BusyPHP\image\url\QRCodeUrl margin(int $margin) 设置空白间距
 * @method \BusyPHP\image\url\QRCodeUrl level(int $margin) 设置识别率等级
 * @method \BusyPHP\image\url\QRCodeUrl logo(string $logo) 设置LOGO url 相对于根目录的URL
 * @method string build() 生成URL
 */
class QRCodeUrl extends Facade
{
    protected static $alwaysNewInstance = true;
    
    
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return \BusyPHP\image\url\QRCodeUrl::class;
    }
}