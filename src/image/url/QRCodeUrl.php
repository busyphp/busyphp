<?php
declare(strict_types = 1);

namespace BusyPHP\image\url;

use BusyPHP\App;
use BusyPHP\helper\util\Transform;
use BusyPHP\image\QRCode;
use think\Config;

/**
 * 动态二维码URL生成
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/12 下午7:41 下午 QRCodeUrl.php $
 */
class QRCodeUrl
{
    /**
     * 二维码内容
     * @var string
     */
    protected $text;
    
    /**
     * 识别率等级
     * @var string
     */
    protected $level;
    
    /**
     * 尺寸
     * @var int
     */
    protected $size;
    
    /**
     * 间距
     * @var int
     */
    protected $margin;
    
    /**
     * LOGO URL
     * @var string
     */
    protected $logo;
    
    /**
     * @var Config
     */
    protected static $config;
    
    /**
     * @var array
     */
    protected $options;
    
    
    /**
     * QRCodeUrl constructor.
     */
    public function __construct()
    {
        if (!isset(self::$config)) {
            self::$config = (new QRCode())->getConfig();
        }
        
        $this->options = self::$config->get('qrcode');
    }
    
    
    /**
     * 设置文本
     * @param string $text
     * @return $this
     */
    public function text(string $text) : self
    {
        $this->text = trim($text);
        
        return $this;
    }
    
    
    /**
     * 设置尺寸
     * @param int $size 范围 1 - 10
     * @return $this
     */
    public function size(int $size) : self
    {
        $this->size = $size;
        
        return $this;
    }
    
    
    /**
     * 设置空白间距
     * @param int $margin
     * @return $this
     */
    public function margin(int $margin) : self
    {
        $this->margin = $margin;
        
        return $this;
    }
    
    
    /**
     * 设置识别率等级
     * @param string $level
     * @return $this
     */
    public function level(string $level) : self
    {
        $this->level = trim($level);
        
        return $this;
    }
    
    
    /**
     * 设置LOGO url 相对于根目录的URL
     * @param string $logo
     * @return $this
     */
    public function logo(string $logo) : self
    {
        $logo = trim($logo);
        if ($logo) {
            $this->logo = App::urlToPath($logo);
        }
        
        return $this;
    }
    
    
    /**
     * 生成URL
     * @return string
     */
    public function build() : string
    {
        $text     = $this->text ?? '';
        $text     .= $this->logo ? '###' . $this->logo : '';
        $text     = Transform::base64encodeUrl($text);
        $filename = "{$text}.{$this->level}X{$this->size}X{$this->margin}.png";
        $hash     = md5($filename);
        $path     = substr($hash, 4, 1);
        $path     .= '/' . substr($hash, 8, 1);
        $path     .= '/' . substr($hash, 12, 1);
        
        // 绑定域名
        $domain = $this->options['domain'] ?: URL_ROOT;
        $domain = rtrim($domain, '/') . '/';
        
        return "{$domain}qrcodes/{$path}/{$filename}";
    }
    
    
    public function __toString() : string
    {
        return $this->build();
    }
}