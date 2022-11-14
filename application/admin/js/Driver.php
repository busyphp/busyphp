<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\js;

use BusyPHP\App;
use BusyPHP\app\admin\controller\AdminHandle;
use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\helper\StringHelper;
use BusyPHP\Model;
use BusyPHP\Request;
use RuntimeException;
use think\Container;
use think\exception\HttpResponseException;
use think\Response;

/**
 * JS插件驱动基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/12 11:48 Driver.php $
 */
abstract class Driver
{
    /**
     * @var App
     */
    protected $app;
    
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var Model|null
     */
    protected $model;
    
    /**
     * 处理回调
     * @var Handler
     */
    protected $handler;
    
    
    public function __construct()
    {
        $this->app     = App::getInstance();
        $this->request = $this->app->request;
        $this->model   = $this->obtainModel();
    }
    
    
    /**
     * 指定查询模型
     * @param Model|class-string<Model> $model 模型
     * @return $this
     */
    public function model($model)
    {
        if (is_string($model)) {
            if (!is_subclass_of($model, Model::class)) {
                throw new ClassNotExtendsException($model, Model::class);
            }
            
            $this->model = Container::getInstance()->make($model, [], true);
        } elseif ($model instanceof Model) {
            $this->model = $model;
        } else {
            throw new ClassNotExtendsException($model, Model::class);
        }
        
        return $this;
    }
    
    
    /**
     * 获取查询模型
     * @return Model|null
     */
    public function getModel() : ?Model
    {
        return $this->model;
    }
    
    
    /**
     * 指定处理回调
     * @param Handler $handler
     * @return $this
     */
    public function handler(Handler $handler)
    {
        $this->handler = $handler;
        
        return $this;
    }
    
    
    /**
     * 准备handler
     */
    protected function prepareHandler()
    {
        if ($this->handler) {
            $this->handler->prepare($this);
        }
    }
    
    
    /**
     * 构建JS组件数据
     * @return null|array
     */
    abstract public function build() : ?array;
    
    
    /**
     * 响应构建的JS组件数据
     * @return Response
     */
    public function response() : Response
    {
        $result = $this->build();
        if (is_null($result)) {
            throw new RuntimeException('组件数据获取失败，请检测前置条件');
        }
        
        return AdminHandle::restResponseSuccess($result);
    }
    
    
    /**
     * 获取模型
     * @param string      $key 模型参数名称，默认为 model
     * @param string|null $method 获取方式，默认用 {@see Request::param()} 获取
     * @return Model|null
     */
    protected function obtainModel(string $key = '', string $method = null) : ?Model
    {
        $method = $method ?: 'param';
        if (!method_exists($this->request, $method)) {
            return null;
        }
        
        $model = call_user_func_array([$this->request, $method], [sprintf('%s/s', $key ?: 'model'), '', 'trim']);
        if (!$model || !class_exists($model) || !is_subclass_of($model, Model::class)) {
            return null;
        }
        
        return Container::getInstance()->make($model, [], true);
    }
    
    
    /**
     * 获取该JS组件的名称
     * @return string
     */
    public static function getRequestName() : string
    {
        return App::getInstance()->request->header('Busy-Admin-Plugin', '');
    }
    
    
    /**
     * 是否该JS组件发起的请求
     * @return bool
     */
    public static function isRequest() : bool
    {
        return static::getRequestName() === basename(str_replace('\\', '/', static::class));
    }
    
    
    /**
     * 获取单例
     * @return static
     */
    public static function getInstance() : self
    {
        return Container::getInstance()->make(static::class);
    }
    
    
    /**
     * 如果是该JS组件发起的请求，则获取该JS组件的单例
     * @return static|null
     */
    public static function getInstanceIfRequest() : ?self
    {
        if (static::isRequest()) {
            return static::getInstance();
        }
        
        return null;
    }
    
    
    /**
     * 自动响应
     */
    public static function autoResponse()
    {
        $name = static::getRequestName();
        if (!$name) {
            return;
        }
        
        $name = str_replace('/', '\\', $name);
        if (false === strpos($name, '\\')) {
            $name = '\BusyPHP\app\admin\js\driver\\' . StringHelper::studly($name);
        }
        
        if (!class_exists($name) || !is_subclass_of($name, Driver::class)) {
            return;
        }
        
        /** @var Driver $driver */
        $driver = Container::getInstance()->make($name);
        if (null === $result = $driver->build()) {
            return;
        }
        
        throw new HttpResponseException(AdminHandle::restResponseSuccess($result));
    }
}