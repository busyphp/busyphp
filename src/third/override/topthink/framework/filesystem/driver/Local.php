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
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\AdapterInterface;
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
     * @inheritDoc
     */
    protected function createAdapter() : AdapterInterface
    {
        $permissions = $this->config['permissions'] ?? [];
        
        $links = ($this->config['links'] ?? null) === 'skip'
            ? LocalAdapter::SKIP_LINKS
            : LocalAdapter::DISALLOW_LINKS;
        
        return new LocalAdapter(
            $this->config['root'],
            LOCK_EX,
            $links,
            $permissions
        );
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
        $path = str_replace('\\', '/', $path);
        
        if (isset($this->config['url'])) {
            return $this->concatPathToUrl($this->config['url'], $path);
        }
        
        return parent::url($path);
    }
}
