<?php
declare(strict_types = 1);

namespace BusyPHP\exception;

use RuntimeException;
use Throwable;

/**
 * 验证异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午11:03 上午 VerifyException.php $
 */
class VerifyException extends RuntimeException
{
    /**
     * 错误字段
     * @var string
     */
    protected $field = '';
    
    
    /**
     * 构造器
     * @param mixed          $message 错误消息
     * @param string         $field 错误字段
     * @param int            $code 错误代码
     * @param Throwable|null $previous
     */
    public function __construct(string $message, string $field = '', int $code = 0, Throwable $previous = null)
    {
        $this->field = $field;
        
        parent::__construct($message, $code, $previous);
    }
    
    
    /**
     * 获取错误字段
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
}