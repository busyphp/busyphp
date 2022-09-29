<?php

namespace BusyPHP\app\admin\filesystem\driver;

use BusyPHP\app\admin\filesystem\Driver;

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
}