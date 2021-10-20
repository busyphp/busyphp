<?php
declare(strict_types = 1);

namespace BusyPHP\facade;

use think\Facade;

/**
 * 验证码URL生成类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/20 下午下午2:18 CaptchaUrl.php $
 * @mixin \BusyPHP\file\captcha\CaptchaUrl
 * @see \BusyPHP\file\captcha\CaptchaUrl
 * @method static \BusyPHP\file\captcha\CaptchaUrl key(string $width) 验证码标识
 * @method static \BusyPHP\file\captcha\CaptchaUrl width(int $width) 验证码宽度
 * @method static \BusyPHP\file\captcha\CaptchaUrl height(int $width) 验证码高度
 * @method static \BusyPHP\file\captcha\CaptchaUrl domain(bool|string $domain) 绑定域名
 * @method static string build(int $width) 生成验证码连接
 */
class CaptchaUrl extends Facade
{
    protected static $alwaysNewInstance = true;
    
    
    protected static function getFacadeClass()
    {
        return \BusyPHP\file\captcha\CaptchaUrl::class;
    }
}