<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\task;

use BusyPHP\app\admin\component\common\ConsoleLog;
use BusyPHP\app\admin\model\system\task\ConfigSystemTask;
use BusyPHP\app\admin\model\system\task\SystemTaskInterface;
use BusyPHP\app\admin\model\system\task\RunSystemTask;
use think\facade\Db;
use Throwable;

/**
 * 数据表任务
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/14 22:36 Database.php $
 */
class Database implements SystemTaskInterface
{
    /** @var string 修复数据表 */
    public const TYPE_REPAIR = 'repair';
    
    /** @var string 优化数据表 */
    public const TYPE_OPTIMIZE = 'optimize';
    
    
    public function configSystemTask(ConfigSystemTask $config) : void
    {
        if ($config->getData() === self::TYPE_REPAIR) {
            $config->setTitle('修复数据库中的所有数据表');
        } else {
            $config->setTitle('优化数据库中的所有数据表');
        }
    }
    
    
    public function runSystemTask(RunSystemTask $task) : void
    {
        $name = '优化';
        $type = 'OPTIMIZE';
        if ($task->data === self::TYPE_REPAIR) {
            $name = '修复';
            $type = 'REPAIR';
        }
        
        $task->log(sprintf('开始查询要%s的数据表...', $name));
        
        $tables = Db::getTables();
        $count  = count($tables);
        $index  = 0;
        
        $task->log(
            sprintf('共有 %s 张表需要%s', $count, $name),
            false,
            1
        );
        
        foreach ($tables as $table) {
            $index++;
            $percentage = [$count, $index];
            $task->log(
                sprintf('[%s] 正在%s数据表 <%s>%s</%s>', $task->step($count, $index), $name, ConsoleLog::LEVEL_COMMENT, $table, ConsoleLog::LEVEL_COMMENT),
                $percentage
            );
            
            try {
                Db::query(sprintf('%s TABLE `%s`', $type, $table));
                
                $task->log(
                    sprintf('[%s] %s数据表 <%s>%s</%s> 成功', $task->step($count, $index), $name, ConsoleLog::LEVEL_INFO, $table, ConsoleLog::LEVEL_INFO),
                    $percentage,
                    1
                );
            } catch (Throwable $e) {
                $task->logError(
                    sprintf('[%s] %s数据表 <%s>%s</%s> 失败, %s', $task->step($count, $index), $name, ConsoleLog::LEVEL_INFO, $table, ConsoleLog::LEVEL_INFO, $e->getMessage()),
                    $percentage,
                    1
                );
            }
        }
        
        $task->complete(sprintf('共有 %s 张表%s完成', $count, $name));
    }
}