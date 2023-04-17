<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\task;

use LogicException;

/**
 * 任务已启动异常
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/16 19:01 StartedException.php $
 */
class StartedException extends LogicException
{
    private SystemTaskField $info;
    
    
    public function __construct(SystemTaskField $info)
    {
        $this->info = $info;
        
        parent::__construct("该任务已启动，请稍后再试！", 0);
    }
    
    
    /**
     * @return SystemTaskField
     */
    public function getInfo() : SystemTaskField
    {
        return $this->info;
    }
}