<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\annotation;

use Attribute;

/**
 * 定义控制器类路由名称注解
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/9 17:03 MenuRoute.php $
 */
#[Attribute(Attribute::TARGET_CLASS)]
class MenuRoute
{
    private string $path;
    
    private bool   $class;
    
    
    /**
     * 构造函数
     * @param string $path 定义该控制器类的的路由名称，一般作为外部插件时定义，定义后该控制器将被路由转发，默认为当前控制器名称(不含后缀Controller)
     * @param bool   $class 设置路由转发方式为类名，而不是URL
     */
    public function __construct(string $path = '', bool $class = false)
    {
        $this->path  = $path;
        $this->class = $class;
    }
    
    
    /**
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }
    
    
    /**
     * @return bool
     */
    public function isClass() : bool
    {
        return $this->class;
    }
}