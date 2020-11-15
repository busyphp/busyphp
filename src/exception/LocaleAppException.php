<?php

namespace BusyPHP\exception;

use think\facade\Lang;
use Throwable;

/**
 * 国际化通用异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/11/15 下午12:02 上午 LocaleAppException.php $
 */
class LocaleAppException extends AppException
{
    /**
     * LocaleAppException constructor.
     * @param string         $message 语言标识
     * @param array          $vars 语言变量值或错误代码
     * @param int            $code 错误代码
     * @param Throwable|null $previous
     */
    public function __construct($message, $vars = [], int $code = 0, Throwable $previous = null)
    {
        if (is_numeric($vars)) {
            $code = $vars;
        }
        
        parent::__construct(Lang::get($message, $vars), $code, $previous);
    }
}