<?php

namespace BusyPHP\exception;

use think\facade\Lang;

/**
 * 国际化验证异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/11/14 下午11:59 下午 LocaleVerifyException.php $
 */
class LocaleVerifyException extends VerifyException
{
    /**
     * LocaleVerifyException constructor.
     * @param string       $message 语言标识
     * @param string|array $field 错误字段或语言变量值
     * @param array        $vars 语言变量值
     * @param int          $code 错误代码
     */
    public function __construct($message, $field = '', array $vars = [], int $code = 0)
    {
        if (is_array($field)) {
            $vars  = $field;
            $field = $vars;
        }
        
        parent::__construct(Lang::get($message, $vars), $field, $code);
    }
}