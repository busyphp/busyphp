<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\concern;

use BusyPHP\helper\FileHelper;
use Closure;
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
    /**
     * @var string|Closure
     */
    private string|Closure $basename;
    
    /**
     * @var string|Closure
     */
    private string|Closure $mimetype;
    
    
    /**
     * 设置文件名(含扩展名)
     * @param string|Closure $basename
     * @return static
     */
    public function setBasename(string|Closure $basename) : static
    {
        $this->basename = $basename;
        
        return $this;
    }
    
    
    /**
     * 设置文件mimetype
     * @param string|Closure $mimetype
     * @return static
     */
    public function setMimetype(string|Closure $mimetype) : static
    {
        $this->mimetype = $mimetype;
        
        return $this;
    }
    
    
    /**
     * 获取文件名(含扩展名)
     * @param string|File|null $data 文件数据或文件对象
     * @param string           $sourceBasename 原文件名
     * @return string
     */
    public function getBasename(string|File $data = null, string $sourceBasename = '') : string
    {
        if (!isset($this->basename)) {
            $this->basename = '';
        }
        
        if ($this->basename instanceof Closure) {
            $basename = (string) Container::getInstance()->invokeFunction($this->basename, [
                $data,
                $sourceBasename
            ]);
        } else {
            $basename = $this->basename;
        }
        
        if ($basename === '') {
            $basename = $sourceBasename;
        }
        if ($basename === '') {
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
        if (!isset($this->mimetype)) {
            $this->mimetype = '';
        }
        
        if ($this->mimetype instanceof Closure) {
            $mimetype = (string) Container::getInstance()->invokeFunction($this->mimetype, [
                $data,
                $sourceMimetype,
                $basename
            ]);
        } else {
            $mimetype = $this->mimetype;
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