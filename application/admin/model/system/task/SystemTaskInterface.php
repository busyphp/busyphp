<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\task;

/**
 * 系统任务接口类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/15 18:46 SystemTaskInterface.php $
 */
interface SystemTaskInterface
{
    /**
     * 配置任务
     * @param ConfigSystemTask $config
     */
    public function configSystemTask(ConfigSystemTask $config) : void;
    
    
    /**
     * 运行任务
     * @param RunSystemTask $task
     */
    public function runSystemTask(RunSystemTask $task) : void;
}