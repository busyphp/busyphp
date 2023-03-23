<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\filesystem\driver;

use BusyPHP\image\Driver as ImageDriver;
use BusyPHP\image\driver\Local as LocalImage;
use BusyPHP\upload\front\Local as LocalFront;
use BusyPHP\upload\interfaces\FrontInterface;
use BusyPHP\upload\interfaces\PartInterface;
use BusyPHP\upload\part\Local as LocalPart;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PathNormalizer;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use League\Flysystem\WhitespacePathNormalizer;
use think\filesystem\Driver;

/**
 * 本地文件处理系统
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/17 9:05 PM Local.php $
 */
class Local extends Driver
{
    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        'root' => '',
    ];
    
    /**
     * @var PathPrefixer
     */
    protected $prefixer;
    
    /**
     * @var PathNormalizer
     */
    protected $normalizer;
    
    
    /**
     * @inheritDoc
     */
    protected function createAdapter() : FilesystemAdapter
    {
        return new LocalFilesystemAdapter(
            $this->config['root'],
            PortableVisibilityConverter::fromArray(
                $this->config['permissions'] ?? [],
                $this->config['visibility'] ?? Visibility::PRIVATE
            ),
            $this->config['lock'] ?? LOCK_EX,
            ($this->config['links'] ?? null) === 'skip' ? LocalFilesystemAdapter::SKIP_LINKS : LocalFilesystemAdapter::DISALLOW_LINKS
        );
    }
    
    
    protected function prefixer() : PathPrefixer
    {
        if (!$this->prefixer) {
            $this->prefixer = new PathPrefixer($this->config['root'], DIRECTORY_SEPARATOR);
        }
        
        return $this->prefixer;
    }
    
    
    protected function normalizer() : WhitespacePathNormalizer
    {
        if (!$this->normalizer) {
            $this->normalizer = new WhitespacePathNormalizer();
        }
        
        return $this->normalizer;
    }
    
    
    /**
     * @inheritDoc
     */
    protected function createImageDriver() : ImageDriver
    {
        return new LocalImage($this, $this->config);
    }
    
    
    /**
     * @inheritDoc
     */
    protected function createPart() : PartInterface
    {
        return new LocalPart($this);
    }
    
    
    /**
     * @inheritDoc
     */
    protected function createFront() : FrontInterface
    {
        return new LocalFront($this);
    }
    
    
    /**
     * @inheritDoc
     */
    public function url(string $path) : string
    {
        return $this->concatPathToUrl($this->config['url'] ?? '', $this->normalizer()->normalizePath($path));
    }
    
    
    /**
     * @inheritDoc
     */
    public function path(string $path) : string
    {
        return $this->prefixer()->prefixPath($path);
    }
    
    
    /**
     * @inheritDoc
     */
    public function matchRelativePathByURL(string $url) : ?string
    {
        $prefix = $this->config['url'] ?? '';
        $prefix = $prefix ? '/' . ltrim($prefix, '/') : '';
        $path   = '/' . ltrim(parse_url($url, PHP_URL_PATH) ?: '', '/');
        if (!$prefix || !str_starts_with($path, $prefix)) {
            return null;
        }
        
        $path = substr($path, strlen($prefix));
        if (!is_string($path)) {
            return null;
        }
        
        return $this->normalizer()->normalizePath($path);
    }
}
