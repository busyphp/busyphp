<?php
declare(strict_types = 1);

namespace BusyPHP\office\excel\import\parameter;

use BusyPHP\office\excel\import\ImportException;

/**
 * BaseParameter
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/9 13:52 BaseParameter.php $
 */
abstract class BaseParameter
{
    /**
     * 执行跳过
     * @param string $message 跳过原因
     * @throws ImportException
     */
    public function continue(string $message = '')
    {
        throw ImportException::continue($message);
    }
    
    
    /**
     * 执行跳出
     * @param string $message 跳出原因
     * @throws ImportException
     */
    public function break(string $message = '')
    {
        throw ImportException::break($message);
    }
}