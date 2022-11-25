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
    
    /**
     * @var string
     */
    protected $name;
    
    
    /**
     * 构造函数
     * @param string         $path
     * @param string         $name
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(string $path = '', $name = '', int $code = 0, Throwable $previous = null)
    {
        $this->path = $path;
        $this->name = $name;
        
        parent::__construct(sprintf('%s "%s" does not exist', $name ?: 'The file', $path), $code, $previous);
    }
    
    
    /**
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }
}