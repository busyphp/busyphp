<?php
declare(strict_types = 1);

namespace BusyPHP\upload\parameter;

use BusyPHP\upload\concern\BasenameMimetypeConcern;
use BusyPHP\Upload;
use BusyPHP\upload\driver\ContentUpload;
use BusyPHP\upload\interfaces\BindDriverParameterInterface;

/**
 * 文件内容上传参数模版
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/20 2:18 PM ContentParameter.php $
 */
class ContentParameter implements BindDriverParameterInterface
{
    use BasenameMimetypeConcern;
    
    /** @var string */
    private $data;
    
    
    /**
     * 构造函数
     * @param string $content 文件数据
     */
    public function __construct(string $content)
    {
        $this->data = $content;
    }
    
    
    /**
     * 获取文件数据
     * @return string
     */
    public function getData() : string
    {
        return $this->data;
    }
    
    
    /**
     * 获取上传驱动类
     * @return class-string<Upload>
     */
    public function getDriver() : string
    {
        return ContentUpload::class;
    }
}