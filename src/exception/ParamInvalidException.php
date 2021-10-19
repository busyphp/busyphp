<?php
declare(strict_types = 1);

namespace BusyPHP\exception;

use InvalidArgumentException;
use Throwable;

/**
 * 参数无效异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午11:25 上午 ParamInvalidException.php $
 */
class ParamInvalidException extends InvalidArgumentException
{
    /**
     * 出错的参数
     * @var string
     */
    protected $param;
    
    
    /**
     * 构造器
     * @param mixed          $param 出错的参数
     * @param int            $code 错误代码
     * @param Throwable|null $previous
     */
    public function __construct(string $param, int $code = 0, Throwable $previous = null)
    {
        $this->param = $param;
        
        parent::__construct("参数无效: {$param}", $code, $previous);
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