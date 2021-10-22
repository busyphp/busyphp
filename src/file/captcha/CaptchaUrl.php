<?php

namespace BusyPHP\file\captcha;

use BusyPHP\App;
use think\facade\Route;

/**
 * 验证码URL生成类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/20 下午下午12:30 CaptchaUrl.php $
 */
class CaptchaUrl
{
    /**
     * 验证码宽度
     * @var int
     */
    protected $width;
    
    /**
     * 验证码高度
     * @var int
     */
    protected $height;
    
    /**
     * 验证码标识
     * @var string
     */
    protected $key;
    
    /**
     * 绑定域名
     * @var bool|string
     */
    protected $domain = false;
    
    
    /**
     * CaptchaUrl constructor.
     * @param string $key
     */
    public function __construct(string $key = '')
    {
        $this->key($key);
    }
    
    
    /**
     * 设置验证码宽度
     * @param int $width
     * @return CaptchaUrl
     */
    public function width(int $width) : self
    {
        $this->width = $width;
        
        return $this;
    }
    
    
    /**
     * 设置验证码高度
     * @param int $height
     * @return CaptchaUrl
     */
    public function height(int $height) : self
    {
        $this->height = $height;
        
        return $this;
    }
    
    
    /**
     * 设置验证码标识
     * @param string $key
     * @return CaptchaUrl
     */
    public function key(string $key) : self
    {
        $this->key = $key;
        
        return $this;
    }
    
    
    /**
     * 设置绑定域名
     * @param bool|string $domain
     * @return CaptchaUrl
     */
    public function domain($domain) : self
    {
        $this->domain = $domain;
        
        return $this;
    }
    
    
    /**
     * 生成URL
     * @return string
     */
    public function build() : string
    {
        $params = ['app' => App::init()->getDirName()];
        if ($this->width) {
            $params['width'] = $this->width;
        }
        if ($this->height) {
            $params['height'] = $this->height;
        }
        
        $params['key'] = $this->key;
        
        return (string) Route::buildUrl('/general/captcha', $params)->domain($this->domain)->suffix(false);
    }
    
    
    public function __toString()
    {
        return $this->build();
    }
}