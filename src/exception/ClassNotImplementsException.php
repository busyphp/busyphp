<?php

namespace BusyPHP\exception;

use RuntimeException;
use Throwable;

/**
 * 类未实现接口异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午下午1:10 ClassNotImplementsException.php $
 */
class ClassNotImplementsException extends RuntimeException
{
    /**
     * @var string
     */
    protected $class;
    
    /**
     * @var string
     */
    protected $interface;
    
    
    /**
     * ClassNotImplementsException constructor.
     * @param string|object  $class
     * @param string|object  $interface
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($class, $interface, string $message = "", int $code = 0, Throwable $previous = null)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        
        if (is_object($interface)) {
            $interface = get_class($interface);
        }
        
        $this->class     = $class;
        $this->interface = $interface;
        $message         = (!empty($message) ? "{$message} " : '') . "{$class} must implements {$interface}";
        
        parent::__construct($message, $code, $previous);
    }
    
    
    /**
     * 获取类名称
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }
    
    
    /**
     * 获取接口名称
     * @return string
     */
    public function getInterface()
    {
        return $this->interface;
    }
}