<?php

namespace BusyPHP\app\general\controller;

use BusyPHP\Controller;
use BusyPHP\file\Captcha;

/**
 * 验证码生成
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午9:16 上午 VerifyController.php $
 */
class CaptchaController extends Controller
{
    /**
     * 验证码显示
     */
    public function index()
    {
        $key    = $this->get('key/s', 'trim');
        $width  = $this->get('width/d');
        $height = $this->get('height/d');
        
        return (new Captcha($this->get('app/s', 'trim')))->width($width)->height($height)->entry($key);
    }
}