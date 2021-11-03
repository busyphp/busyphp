<?php
declare(strict_types = 1);

namespace BusyPHP\file\image;

use BusyPHP\App;
use BusyPHP\app\admin\setting\ThumbSetting;

/**
 * 动态缩图URL生成
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/12 下午4:39 下午 ThumbUrl.php $
 */
class ThumbUrl
{
    /**
     * 无图变量标识
     */
    const EMPTY_IMAGE_VAR = 'no_picture';
    
    /**
     * @var string
     */
    protected $url;
    
    /**
     * 缩图配置
     * @var ThumbSetting
     */
    protected $setting;
    
    /**
     * 缩图类型
     * @var string
     */
    protected $type;
    
    /**
     * 缩图配置
     * @var string
     */
    protected $size;
    
    /**
     * 域名地址
     * @var bool|string
     */
    protected $domain = false;
    
    
    /**
     * Url constructor.
     */
    public function __construct()
    {
        $this->setting = ThumbSetting::init();
        
        $domain = $this->setting->getDomain();
        if ($domain) {
            $this->domain($domain);
        }
    }
    
    
    /**
     * 设置图片URL
     * @param string $url
     * @return $this
     */
    public function url($url) : self
    {
        $this->url = trim((string) $url);
        
        return $this;
    }
    
    
    /**
     * 设置缩图配置
     * @param string $size
     * @return $this
     */
    public function size(string $size) : self
    {
        $this->size = $size;
        
        return $this;
    }
    
    
    /**
     * 设置缩图类型
     * @param string $thumbType
     * @return $this
     */
    public function type(string $thumbType) : self
    {
        $this->type = $thumbType;
        
        return $this;
    }
    
    
    /**
     * 设置是否输出域名
     * @param bool|string $domain 域名地址
     * @return $this
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
        if (false !== strpos($this->url, '://')) {
            return $this->url;
        }
        
        $info              = pathinfo($this->url ?: $this->setting->getEmptyImageVar());
        $info['extension'] = $info['extension'] ?? 'jpeg';
        $info['dirname']   = $info['dirname'] ?? '';
        if ($info['dirname'] === '/' || $info['dirname'] === '.') {
            $info['dirname'] = '';
        }
        
        // 域名
        $request = App::getInstance()->request;
        $domain  = $request->getWebUrl(false);
        if ($this->domain) {
            $domain = $this->domain === true ? $request->getWebUrl(true) : $this->domain;
            $domain = rtrim($domain, '/') . '/';
        }
        
        return "{$domain}thumbs/{$this->type}{$info['dirname']}/{$info['filename']}_{$this->size}.{$info['extension']}";
    }
    
    
    public function __toString() : string
    {
        return $this->build();
    }
}