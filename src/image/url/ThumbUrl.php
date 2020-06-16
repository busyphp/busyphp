<?php
declare(strict_types = 1);

namespace BusyPHP\image\url;

use think\App;
use think\Config;

/**
 * 动态缩图URL生成
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/12 下午4:39 下午 Url.php $
 */
class ThumbUrl
{
    /**
     * 无图变量标识
     */
    const EMPTY_IMAGE_VAR = 'no_picture';
    
    /**
     * 公共配置
     * @var Config
     */
    protected static $config;
    
    /**
     * @var string
     */
    protected $url;
    
    /**
     * 缩图配置
     * @var array
     */
    protected $options;
    
    /**
     * 缩图类型
     * @var string
     */
    private $type;
    
    /**
     * 缩图配置
     * @var string
     */
    private $size;
    
    
    /**
     * Url constructor.
     */
    public function __construct()
    {
        if (!isset(self::$config)) {
            $app = App::getInstance();
            $app->config->load($app->getConfigPath() . 'extend' . DIRECTORY_SEPARATOR . 'thumb.php', 'thumb');
            
            self::$config = $app->config;
        }
        
        $this->options = self::$config->get('thumb');
    }
    
    
    /**
     * 获取配置
     * @return Config
     */
    public function getConfig() : Config
    {
        return self::$config;
    }
    
    
    /**
     * 设置图片URL
     * @param string $url
     * @return $this
     */
    public function url($url) : self
    {
        $this->url = $url;
        
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
     * 生成URL
     * @return string
     */
    public function build() : string
    {
        if (false !== strpos($this->url, '://')) {
            return $this->url;
        }
        
        $this->url         = $this->url ?: ($this->options['empty_image_var'] ?: self::EMPTY_IMAGE_VAR);
        $info              = pathinfo($this->url);
        $info['extension'] = $info['extension'] ?: 'jpg';
        if ('/' === $info['dirname'] || '.' === $info['dirname']) {
            $info['dirname'] = '';
        }
        
        // 绑定域名
        $domain = $this->options['domain'] ?: URL_ROOT;
        $domain = rtrim($domain, '/') . '/';
        
        return "{$domain}thumbs/{$this->type}{$info['dirname']}/{$info['filename']}_{$this->size}.{$info['extension']}";
    }
    
    
    public function __toString() : string
    {
        return $this->build();
    }
}