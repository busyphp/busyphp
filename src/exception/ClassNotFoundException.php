<?php

namespace BusyPHP\exception;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Throwable;

/**
 * 类不存在异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午下午1:09 ClassNotFoundException.php $
 */
class ClassNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
    /**
     * @var string
     */
    protected $class;
    
    
    /**
     * ClassNotFoundException constructor.
     * @param string|object  $class
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($class, $message = "", int $code = 0, Throwable $previous = null)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        
        $this->class = $class;
        $message     = (!empty($message) ? "{$message} " : '') . "class not found {$class}";
        
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
}