<?php
declare(strict_types = 1);

namespace BusyPHP\upload\parameter;

use BusyPHP\upload\concern\BasenameMimetypeConcern;
use BusyPHP\Upload;
use BusyPHP\upload\driver\LocalUpload;
use BusyPHP\upload\interfaces\BindDriverParameterInterface;
use think\File;

/**
 * 本地文件上传参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/20 6:35 PM LocalParameter.php $
 */
class LocalParameter implements BindDriverParameterInterface
{
    use BasenameMimetypeConcern;
    
    /** @var File|string|array */
    private $file;
    
    
    /**
     * 构造函数
     * @param File|string|array $file 文件对象或文件字段名或$_FILES['字段']数组
     */
    public function __construct($file)
    {
        $this->file = $file;
    }
    
    
    /**
     * 获取文件对象或文件字段名或$_FILES['字段']数组
     * @return array|File|string
     */
    public function getFile()
    {
        return $this->file;
    }
    
    
    /**
     * 获取上传驱动类
     * @return class-string<Upload>
     */
    public function getDriver() : string
    {
        return LocalUpload::class;
    }
}