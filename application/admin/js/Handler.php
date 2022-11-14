<?php

namespace BusyPHP\app\admin\js;

use BusyPHP\App;
use BusyPHP\Model;
use BusyPHP\Request;

/**
 * 基本处理回调类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 00:27 Handler.php $
 */
abstract class Handler
{
    /**
     * 查询模型
     * @var Model
     */
    protected $model;
    
    /**
     * 组件驱动
     * @var Driver
     */
    protected $driver;
    
    /**
     * 请求对象
     * @var Request
     */
    protected $request;
    
    /**
     * App对象
     * @var App
     */
    protected $app;
    
    /**
     * 是否准备完成
     * @var bool
     */
    private $prepare;
    
    
    /**
     * 预备参数
     * @param Driver $driver
     * @return $this
     */
    public function prepare(Driver $driver) : self
    {
        if ($this->prepare) {
            return $this;
        }
        
        $this->prepare = true;
        $app           = App::getInstance();
        $this->driver  = $driver;
        $this->app     = $app;
        $this->request = $app->request;
        $this->model   = $driver->getModel();
        
        return $this;
    }
}