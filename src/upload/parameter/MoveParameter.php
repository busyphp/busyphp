<?php
declare(strict_types = 1);

namespace BusyPHP\upload\parameter;

use BusyPHP\upload\concern\BasenameMimetypeConcern;
use BusyPHP\upload\Driver;
use BusyPHP\upload\driver\MoveUpload;
use BusyPHP\upload\interfaces\BindDriverParameterInterface;
use think\File;

/**
 * 移动文件上传参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/20 2:45 PM MoveParameter.php $
 */
class MoveParameter implements BindDriverParameterInterface
{
    use BasenameMimetypeConcern;
    
    
    /** @var bool */
    private $retain;
    
    /** @var File|string */
    private $file;
    
    
    /**
     * 构造函数
     * @param File|string $file 要移动的文件对象或文件绝对路径
     * @param bool        $retain 是否保留源文件
     */
    public function __construct($file, bool $retain = true)
    {
        $this->file   = $file;
        $this->retain = $retain;
    }
    
    
    /**
     * 获取要移动的文件对象或文件绝对路径
     * @return string|File
     */
    public function getFile()
    {
        return $this->file;
    }
    
    
    /**
     * 设置是否保留源文件
     * @param bool $retain
     * @return MoveParameter
     */
    public function setRetain(bool $retain) : self
    {
        $this->retain = $retain;
        
        return $this;
    }
    
    
    /**
     * 是否保留源文件
     * @return bool
     */
    public function isRetain() : bool
    {
        return $this->retain;
    }
    
    
    /**
     * 获取上传驱动类
     * @return class-string<Driver>
     */
    public function getDriver() : string
    {
        return MoveUpload::class;
    }
}