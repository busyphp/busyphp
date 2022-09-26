<?php

namespace BusyPHP\upload\concern;

use BusyPHP\helper\FileHelper;
use InvalidArgumentException;
use think\Container;
use think\File;

/**
 * 文件名/mimetype 参数模版相关类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/22 1:09 PM BasenameMimetypeConcern.php $
 */
trait BasenameMimetypeConcern
{
    /** @var string|callable|Closure */
    private $basename;
    
    /** @var string|callable|Closure */
    private $mimetype;
    
    
    /**
     * 设置文件名(含扩展名)
     * @param callable|Closure|string $basename
     */
    public function setBasename($basename) : self
    {
        $this->basename = $basename;
        
        return $this;
    }
    
    
    /**
     * 设置文件mimetype
     * @param callable|Closure|string $mimetype
     */
    public function setMimetype($mimetype) : self
    {
        $this->mimetype = $mimetype;
        
        return $this;
    }
    
    
    /**
     * 获取文件名(含扩展名)
     * @param string|File $data 文件数据或文件对象
     * @param string      $sourceBasename 原文件名
     * @return string
     */
    public function getBasename($data = null, string $sourceBasename = '') : string
    {
        if (is_callable($this->basename)) {
            $basename = (string) Container::getInstance()->invokeFunction($this->basename, [
                $data,
                $sourceBasename
            ]);
        } else {
            $basename = (string) $this->basename;
        }
        $basename = $basename === '' ? $sourceBasename : $basename;
        if ($basename === '' || pathinfo($basename, PATHINFO_FILENAME) === '') {
            throw new InvalidArgumentException('文件名无效');
        }
        if (pathinfo($basename, PATHINFO_EXTENSION) === '') {
            throw new InvalidArgumentException('文件名未包含扩展名');
        }
        
        return $basename;
    }
    
    
    /**
     * 获取文件mimetype
     * @param null   $data 文件数据或文件对象
     * @param string $sourceMimetype 原mimetype
     * @param string $basename 文件名(含扩展名)
     * @param bool   $check 是否验证mimetype格式
     * @return string
     */
    public function getMimetype($data = null, string $sourceMimetype = '', string $basename = '', bool $check = true) : string
    {
        if (is_callable($this->mimetype)) {
            $mimetype = (string) Container::getInstance()->invokeFunction($this->mimetype, [
                $data,
                $sourceMimetype,
                $basename
            ]);
        } else {
            $mimetype = (string) $this->mimetype;
        }
        
        $mimetype = $mimetype ?: $sourceMimetype;
        if (!$mimetype) {
            $mimetype = FileHelper::getMimetypeByPath($basename);
        }
        
        $mimetype = trim($mimetype, '/');
        $pos      = strpos($mimetype, '/');
        if ($check && (!$mimetype || $pos === 0 || $pos === strlen($mimetype) - 1 || $pos === false)) {
            throw new InvalidArgumentException('mimetype无效');
        }
        
        return $mimetype;
    }
}