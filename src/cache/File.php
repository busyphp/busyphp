<?php
declare(strict_types = 1);

namespace BusyPHP\cache;

/**
 * 文件缓存类扩展
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/31 下午10:21 上午 BaseFileCache.php $
 */
class File extends \think\cache\driver\File
{
    /**
     * 获取缓存文件路径
     * @param string $name
     * @return string
     */
    public function getCacheKey(string $name) : string
    {
        if (substr($name, 0, 5) === 'core/' || substr($name, 0, 4) === 'app/' || substr($name, 0, 8) === 'BusyPHP/') {
            if ($this->options['prefix']) {
                $name = $this->options['prefix'] . DIRECTORY_SEPARATOR . $name;
            }
            
            if (substr($this->options['path'], -7) === 'common/') {
                $this->options['path'] = substr($this->options['path'], 0, -7);
            }
            
            $name = $this->options['path'] . $name . '.php';
        } else {
            $name = hash($this->options['hash_type'], $name);
            
            // 使用子目录
            if ($this->options['cache_subdir']) {
                $name = substr($name, 0, 2) . DIRECTORY_SEPARATOR . substr($name, 2);
            }
            
            if ($this->options['prefix']) {
                $name = $this->options['prefix'] . DIRECTORY_SEPARATOR . $name;
            }
            
            $name = $this->options['path'] . 'common' . DIRECTORY_SEPARATOR . $name . '.php';
        }
        
        return $name;
    }
}