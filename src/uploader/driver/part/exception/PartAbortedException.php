<?php
declare(strict_types = 1);

namespace BusyPHP\uploader\driver\part\exception;

use RuntimeException;

/**
 * 分块上传已终止
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/5/26 09:14 PartAbortedException.php $
 */
class PartAbortedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('已终止完成', 0);
    }
}