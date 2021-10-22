<?php
declare(strict_types = 1);

namespace BusyPHP\facade;

use think\Facade;

/**
 * 动态二维码URL生成
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/12 下午7:41 下午 QRCodeUrl.php $
 * @mixin \BusyPHP\file\qrcode\QRCodeUrl
 * @see \BusyPHP\file\qrcode\QRCodeUrl
 * @method static \BusyPHP\file\qrcode\QRCodeUrl text(string $text) 设置文本
 * @method static \BusyPHP\file\qrcode\QRCodeUrl size(int $size) 设置尺寸
 * @method static \BusyPHP\file\qrcode\QRCodeUrl margin(int $margin) 设置空白间距
 * @method static \BusyPHP\file\qrcode\QRCodeUrl level(int $margin) 设置识别率等级
 * @method static \BusyPHP\file\qrcode\QRCodeUrl logo(string $logo, int $size) 设置LOGO url 相对于根目录的URL
 * @method static \BusyPHP\file\qrcode\QRCodeUrl domain(bool|string $domain) 设置绑定域名
 * @method static string build() 生成URL
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
        return \BusyPHP\file\qrcode\QRCodeUrl::class;
    }
}