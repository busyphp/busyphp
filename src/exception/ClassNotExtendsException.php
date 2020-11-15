<?php

namespace BusyPHP\exception;

/**
 * 类未继承异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/11/14 下午11:48 下午 ClassInterfaceException.php $
 */
class ClassNotExtendsException extends AppException
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
     * @param string|object $class
     * @param string|object $extends
     * @param string        $message
     */
    public function __construct($class, $extends, $message = "")
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
        
        $this->setData('CLASS MUST EXTENDS', [
            'class'   => $class,
            'extends' => $extends
        ]);
        
        parent::__construct($message);
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