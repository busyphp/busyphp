<?php
declare(strict_types = 1);

namespace BusyPHP\exception;

/**
 * 参数无效异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午11:25 上午 ParamInvalidException.php $
 */
class ParamInvalidException extends AppException
{
    /**
     * 出错的参数
     * @var string
     */
    protected $param;
    
    
    /**
     * 构造器
     * @param mixed $param 出错的参数
     * @param int   $code 错误代码
     */
    public function __construct($param, $code = 0)
    {
        if ($param instanceof self) {
            $this->code  = $param->getCode();
            $this->param = $param->getParam();
            $param       = $this->param;
            $code        = $this->code;
        } else {
            $this->param = $param;
            $this->code  = $code;
        }
        
        $this->setData('PARAM INVALID ERROR', [
            'param' => $this->param,
            'code'  => $this->code
        ]);
        
        parent::__construct("参数无效: {$param}", $code);
    }
    
    
    /**
     * 获取出错的参数
     * @return string
     */
    public function getParam()
    {
        return $this->param;
    }
}