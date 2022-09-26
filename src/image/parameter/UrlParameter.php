<?php
declare(strict_types = 1);

namespace BusyPHP\image\parameter;

/**
 * 图片在线处理参数模板
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/14 12:26 PM UrlParameter.php $
 */
class UrlParameter extends ProcessParameter
{
    /** @var bool */
    protected $download = false;
    
    /** @var int */
    protected $lifetime = 0;
    
    /** @var string */
    protected $filename = '';
    
    
    /**
     * 构造器
     * @param string $url 图片URL
     */
    public function __construct(string $url = '')
    {
        // TODO 支持网址，/开头的网址，支持相对路径
        parent::__construct($url, '');
    }
    
    
    /**
     * 设置是否下载 与 {@see UrlParameter::cache()} 互斥
     * @param string $filename 文件名
     * @return UrlParameter
     */
    public function download(string $filename = '') : self
    {
        $this->download = true;
        $this->lifetime = 0;
        $this->filename = $filename;
        
        return $this;
    }
    
    
    /**
     * 设置缓存多少秒 与 {@see UrlParameter::download()} 互斥
     * @param int $lifetime 过期秒数
     * @return UrlParameter
     */
    public function cache(int $lifetime) : self
    {
        $this->download = false;
        $this->lifetime = $lifetime;
        
        return $this;
    }
    
    
    /**
     * 是否下载图片
     * @return bool
     */
    public function isDownload() : bool
    {
        return $this->download;
    }
    
    
    /**
     * 下载的图片名称
     * @return string
     */
    public function getFilename() : string
    {
        return rawurldecode(pathinfo($this->filename ?: $this->getOldPath(), PATHINFO_FILENAME)) ?: date('YmdHis');
    }
    
    
    /**
     * 获取缓存秒数
     * @return int
     */
    public function getLifetime() : int
    {
        return max($this->lifetime, 0);
    }
}