<?php
declare(strict_types = 1);

namespace BusyPHP\exception;

/**
 * 验证异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午11:03 上午 VerifyException.php $
 */
class VerifyException extends AppException
{
    /**
     * 错误字段
     * @var string
     */
    protected $field = '';
    
    
    /**
     * 构造器
     * @param mixed  $message 错误消息
     * @param string $field 错误字段
     * @param int    $code 错误代码
     */
    public function __construct($message, string $field = '', int $code = 0)
    {
        if ($message instanceof self) {
            $this->field   = $message->getField();
            $this->message = $message->getMessage();
            $this->code    = $message->getCode();
            $message       = $this->message;
            $code          = $this->code;
        } else {
            $this->field = $field;
            $this->code  = $code;
        }
        
        $this->setData('VERIFY ERROR', [
            'field' => $this->field,
            'code'  => $this->code
        ]);
        
        parent::__construct($message, $code);
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