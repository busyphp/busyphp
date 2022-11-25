<?php

namespace BusyPHP\exception;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Throwable;

/**
 * 文件不存在异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/24 20:37 FileNotFoundException.php $
 */
class FileNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
    /**
     * @var string
     */
    protected $path;
    
    
    public function __construct(string $path = '', $code = 0, Throwable $previous = null)
    {
        $this->path = $path;
        
        parent::__construct(sprintf('The file "%s" does not exist', $path), $code, $previous);
    }
    
    
    /**
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }
}