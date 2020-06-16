<?php
declare(strict_types = 1);

namespace BusyPHP\exception;

use think\Exception;
use Throwable;

/**
 * 应用通用异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午11:30 上午 AppException.php $
 */
class AppException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if ($message instanceof self) {
            $this->message = $message->getMessage();
            $this->code    = $code ? $code : $message->getCode();
            $code          = $this->code;
            $message       = $this->message;
        }
        
        parent::__construct($message, $code, $previous);
    }
}