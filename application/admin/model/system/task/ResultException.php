<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\task;

use RuntimeException;

/**
 * 运行系统任务结果异常类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/15 20:31 ResultException.php $
 */
class ResultException extends RuntimeException
{
    private bool|string $result;
    
    
    /**
     * 构造函数
     * @param string $message 完成说明
     * @param bool   $result 是否处理成功或处理成功结果字符串(以配合执行成功操作)，空字符串为不成功
     */
    public function __construct(string $message = "", bool|string $result = true)
    {
        $this->result = $result;
        
        parent::__construct($message, 0);
    }
    
    
    /**
     * @return bool|string
     */
    public function getResult() : bool|string
    {
        return $this->result;
    }
}