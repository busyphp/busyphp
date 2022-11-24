<?php

namespace BusyPHP\app\general\controller;

use BusyPHP\app\admin\setting\CaptchaSetting;
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
        $app    = $this->get('app/s', 'trim');
        
        $setting = CaptchaSetting::instance();
        $setting->setClient($app ?: $this->app->getDirName());
        
        $captcha = new Captcha();
        $captcha->curve($setting->isCurve());
        $captcha->noise($setting->isNoise());
        $captcha->bgImage($setting->isBgImage());
        $captcha->length($setting->getLength());
        $captcha->expire($setting->getExpireMinute() * 60);
        $captcha->fontSize($setting->getFontSize());
        $captcha->token($setting->getToken());
        
        // 背景颜色
        if ($bgColor = $setting->getBgColor()) {
            $captcha->bgColor($bgColor);
        }
        
        // 验证码类型
        $zh = false;
        switch ($setting->getType()) {
            // 纯英文
            case 1:
                $captcha->chars('abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY');
            break;
            // 纯数字
            case 2:
                $captcha->chars('0123456789');
            break;
            // 中文
            case 3:
                $zh = true;
                $captcha->zh(true);
            break;
        }
        
        // 验证码字符
        if ($code = $setting->getCode()) {
            if ($zh) {
                $captcha->zhChars($code);
            } else {
                $captcha->chars($code);
            }
        }
        
        // 验证码字体
        if (is_file($fontFile = $setting->getFontFile(true))) {
            $captcha->fontFile($fontFile);
        } elseif ($font = $setting->getFont()) {
            if (0 === strpos($font, 'zh_')) {
                $captcha->fontFile($this->app->getFrameworkPath(sprintf("file/captcha/zhttfs/%s.ttf", substr($font, 3))));
            } else {
                $captcha->fontFile($this->app->getFrameworkPath(sprintf("file/captcha/ttfs/%s.ttf", $font)));
            }
        }
        
        return $captcha->width($width)->height($height)->entry($key);
    }
}