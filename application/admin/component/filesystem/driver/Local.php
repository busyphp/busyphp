<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\filesystem\driver;

use BusyPHP\app\admin\component\filesystem\Driver;

/**
 * 本次磁盘管理类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/9/29 2:28 PM Local.php $
 */
class Local extends Driver
{
    /**
     * 向 {@see FileController::config()} 中注入上传脚本
     * @return string
     */
    public function frontUploadInjectScript() : string
    {
        return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'local' . DIRECTORY_SEPARATOR . 'front.js');
    }
    
    
    /**
     * 获取磁盘名称
     * @return string
     */
    public function getName() : string
    {
        return '本地服务器';
    }
    
    
    /**
     * 获取磁盘说明
     * @return string
     */
    public function getDescription() : string
    {
        return '文件直接上传到本地服务器的 <code>public/uploads</code> 目录，占用服务器磁盘空间，可以使用CDN加速';
    }
    
    
    /**
     * @inheritDoc
     */
    public function getForm() : array
    {
        return [];
    }
}