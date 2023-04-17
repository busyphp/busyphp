<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\task;

use BusyPHP\app\admin\component\common\ConsoleLog;
use BusyPHP\exception\MethodNotFoundException;
use BusyPHP\helper\ConsoleHelper;

/**
 * 系统任务运行类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/10 21:24 RunSystemTask.php $
 * @see SystemTaskInterface::runSystemTask()
 * @method void logQuestion(string $message, float|false|array $percentage = false, int $backLine = 0) 疑问级别日志
 * @method void logHighlight(string $message, float|false|array $percentage = false, int $backLine = 0) 强调级别日志
 * @method void logComment(string $message, float|false|array $percentage = false, int $backLine = 0) 解释级别日志
 * @method void logError(string $message, float|false|array $percentage = false, int $backLine = 0) 错误级别日志
 * @method void logWarning(string $message, float|false|array $percentage = false, int $backLine = 0) 警告级别日志
 * @method void logInfo(string $message, float|false|array $percentage = false, int $backLine = 0) 消息级别日志
 */
class RunSystemTask
{
    /**
     * 任务ID
     * @var string
     */
    public string $id;
    
    /**
     * 任务数据
     * @var mixed
     */
    public mixed $data;
    
    /**
     * 任务信息
     * @var SystemTaskField
     */
    public SystemTaskField $info;
    
    /**
     * 日志ID
     * @var string
     */
    protected string $logId;
    
    
    /**
     * 构造函数
     * @param SystemTaskField $info
     */
    public function __construct(SystemTaskField $info)
    {
        $this->info  = $info;
        $this->id    = $info->id;
        $this->data  = $info->data;
        $this->logId = $info->logId;
    }
    
    
    public function __call(string $name, array $arguments)
    {
        if (str_starts_with($name, 'log')) {
            $level      = strtolower(substr($name, 3));
            $message    = $arguments[0] ?? '';
            $percentage = $arguments[1] ?? false;
            $backLine   = $arguments[2] ?? 0;
            
            $this->log($message, $percentage, $backLine, $level);
            
            return;
        }
        
        throw new MethodNotFoundException($this, $name);
    }
    
    
    /**
     * 记录日志
     * @param string                   $message 进度描述
     * @param float<0,100>|false|int[] $percentage 处理进度数值，范围 0-100，false则为普通日志，数组代表总数和当前值，如：[100,20]
     * @param int                      $backLine 回退行数
     * @param string                   $level 日志级别
     * @return void
     */
    public function log(string $message, float|false|array $percentage = false, int $backLine = 0, string $level = ConsoleLog::LEVEL_RECORD) : void
    {
        ConsoleLog::write($this->logId, $message, $percentage, $backLine, $level);
    }
    
    
    /**
     * 任务执行完成
     * @param string      $message 完成说明
     * @param bool|string $result 是否处理成功或处理成功结果字符串(以配合执行成功操作)，空字符串为不成功
     * @return void
     */
    public function complete(string $message, bool|string $result = true) : void
    {
        throw new ResultException($message, $result);
    }
    
    
    /**
     * 返回步进值
     * @param int $count 处理总数
     * @param int $current 当前处理第几条
     * @return string
     */
    public function step(int $count, int $current) : string
    {
        return str_pad("$current", strlen("$count"), '0', STR_PAD_LEFT) . '/' . $count;
    }
    
    
    /**
     * 休眠
     * @param float|int $seconds
     * @return void
     */
    public function sleep(float|int $seconds) : void
    {
        ConsoleHelper::sleep($seconds);
    }
}