<?php

namespace BusyPHP\exception;

use RuntimeException;
use Throwable;

/**
 * 类未继承异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午下午1:08 ClassNotExtendsException.php $
 */
class ClassNotExtendsException extends RuntimeException
{
    /**
     * @var string
     */
    protected $class;
    
    /**
     * @var string
     */
    protected $extends;
    
    
    /**
     * ClassNotExtendsException constructor.
     * @param string|object  $class
     * @param string|object  $extends
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($class, $extends, string $message = "", int $code = 0, Throwable $previous = null)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        
        if (is_object($extends)) {
            $extends = get_class($extends);
        }
        
        $this->class   = $class;
        $this->extends = $extends;
        $message       = (!empty($message) ? "{$message} " : '') . "{$class} must extends {$extends}";
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
     * 获取继承类名称
     * @return string
     */
    public function getExtends()
    {
        return $this->extends;
    }
}