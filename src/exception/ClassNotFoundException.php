<?php

namespace BusyPHP\exception;

/**
 * 类不存在异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/11/14 下午11:48 下午 ClassNotFoundException.php $
 */
class ClassNotFoundException extends AppException
{
    /**
     * @var string
     */
    protected $class;
    
    
    /**
     * ClassNotFoundException constructor.
     * @param string|object $class
     * @param string        $message
     */
    public function __construct($class, $message = "")
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        
        $this->class = $class;
        $message     = (!empty($message) ? "{$message} " : '') . "class not found {$class}";
        
        $this->setData('CLASS NOT FOUND', [
            'class' => $class,
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
}