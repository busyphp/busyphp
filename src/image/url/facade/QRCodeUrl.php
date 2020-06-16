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