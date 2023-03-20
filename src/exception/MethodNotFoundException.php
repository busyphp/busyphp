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
    protected string $method;
    
    /**
     * @var string
     */
    protected string $class;
    
    
    /**
     * MethodNotFoundException constructor.
     * @param mixed          $class 类名
     * @param string         $method 方法名
     * @param bool           $static 是否静态方法
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($class, string $method, bool $static = false, int $code = 0, Throwable $previous = null)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $this->class  = $class;
        $this->method = $method;
        $prefix       = $static ? 'static ' : '';
        $symbol       = $static ? '::' : '->';
        parent::__construct($prefix . "method $class$symbol$method() does not exist", $code, $previous);
    }
    
    
    /**
     * @return string
     */
    public function getClass() : mixed
    {
        return $this->class;
    }
    
    
    /**
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }
}