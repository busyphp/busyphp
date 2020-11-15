<?php

namespace BusyPHP\exception;

use Throwable;

/**
 * 找不到方法异常类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/11/15 下午3:15 下午 MethodNotFonundException.php $
 */
class MethodNotFoundException extends AppException
{
    /**
     * @var string
     */
    protected $method;
    
    /**
     * @var string
     */
    protected $class;
    
    
    public function __construct($class, $method, $message = "")
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $this->class   = $class;
        $this->message = $method;
        $message       = (!empty($message) ? "{$message} " : '') . "method {$method} does not exist in

 {$class}";
        
        $this->setData('METHOD NOT FOUND', [
            'class'  => $class,
            'method' => $method
        ]);
        
        parent::__construct($message, 0);
    }
}