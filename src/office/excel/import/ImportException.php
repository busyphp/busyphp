<?php

namespace BusyPHP\office\excel\import;

use RuntimeException;

/**
 * ImportException
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/9 00:03 ImportException.php $
 */
class ImportException extends RuntimeException
{
    private bool $break;
    
    
    public function __construct(string $message = '', bool $break = false)
    {
        $this->break = $break;
        
        parent::__construct($message, 0);
    }
    
    
    /**
     * @return bool
     */
    public function isBreak() : bool
    {
        return $this->break;
    }
    
    
    /**
     * @param string $message
     * @return static
     */
    public static function break(string $message = '') : static
    {
        return new static($message, true);
    }
    
    
    /**
     * @param string $message
     * @return static
     */
    public static function continue(string $message = '') : static
    {
        return new static($message, false);
    }
}