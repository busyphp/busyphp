<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\driver\move;

use BusyPHP\uploader\concern\BasenameMimetypeConcern;
use BusyPHP\uploader\interfaces\DataInterface;
use think\File;

/**
 * 移动文件上传参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/20 2:45 PM MoveData.php $
 */
class MoveData implements DataInterface
{
    use BasenameMimetypeConcern;
    
    
    /**
     * @var bool
     */
    private bool $retain;
    
    /**
     * @var File|string
     */
    private string|File $file;
    
    
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
    public function getFile() : File|string
    {
        return $this->file;
    }
    
    
    /**
     * 设置是否保留源文件
     * @param bool $retain
     * @return static
     */
    public function setRetain(bool $retain) : static
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
}