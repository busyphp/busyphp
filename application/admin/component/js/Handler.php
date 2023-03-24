<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js;

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
     * @var mixed
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
     * @param mixed      $driver
     * @param Model|null $model
     * @return static
     */
    public function prepare($driver, Model $model = null) : static
    {
        if ($this->prepare) {
            return $this;
        }
        $this->prepare = true;
        
        $app           = App::getInstance();
        $this->app     = $app;
        $this->request = $app->request;
        
        if ($driver instanceof Driver) {
            $this->model = $driver->getModel();
        } else {
            $this->model = $model;
        }
        
        return $this;
    }
}