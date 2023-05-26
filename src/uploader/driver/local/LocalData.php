<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\driver\local;

use BusyPHP\uploader\concern\BasenameMimetypeConcern;
use BusyPHP\uploader\interfaces\DataInterface;
use think\File;

/**
 * 本地文件上传参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/20 6:35 PM LocalData.php $
 */
class LocalData implements DataInterface
{
    use BasenameMimetypeConcern;
    
    /**
     * @var File
     */
    private File $file;
    
    
    /**
     * 构造函数
     * @param File $file 文件对象
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }
    
    
    /**
     * 获取文件对象
     * @return File
     */
    public function getFile() : File
    {
        return $this->file;
    }
}