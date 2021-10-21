<?php
declare(strict_types = 1);

namespace BusyPHP\exception;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Throwable;

/**
 * 找不到方法异常类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/21 下午下午1:11 MethodNotFoundException.php $
 */
class MethodNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
    /**
     * @var string
     */
    protected $method;
    
    /**
     * @var string
     */
    protected $class;
    
    
    /**
     * MethodNotFoundException constructor.
     * @param mixed          $class 类名
     * @param string         $method 方法名
     * @param string         $message 消息
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($class, string $method, string $message = '', int $code = 0, Throwable $previous = null)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $this->class   = $class;
        $this->message = $method;
        $message       = (!empty($message) ? "{$message} " : '') . "method {$method} does not exist in {$class}";
        
        parent::__construct($message, $code, $previous);
    }
}