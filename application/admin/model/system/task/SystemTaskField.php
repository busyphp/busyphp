<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\task;

use BusyPHP\helper\TransHelper;
use BusyPHP\model\annotation\field\Filter;
use BusyPHP\model\annotation\field\Ignore;
use BusyPHP\model\annotation\field\Json;
use BusyPHP\model\annotation\field\Serialize;
use BusyPHP\model\annotation\field\ToArrayFormat;
use BusyPHP\model\annotation\field\ValueBindField;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use think\validate\ValidateRule;

/**
 * 系统任务字段
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/10 10:29 SystemTaskField.php $
 * @method static Entity id(mixed $op = null, mixed $condition = null) ID
 * @method static Entity title(mixed $op = null, mixed $condition = null) 任务名称
 * @method static Entity repeated(mixed $op = null, mixed $condition = null) 是否可重复执行
 * @method static Entity loops(mixed $op = null, mixed $condition = null) 重复间隔秒数
 * @method static Entity command(mixed $op = null, mixed $condition = null) 执行指令
 * @method static Entity pid(mixed $op = null, mixed $condition = null) 进程ID
 * @method static Entity attempts(mixed $op = null, mixed $condition = null) 累计执行次数
 * @method static Entity status(mixed $op = null, mixed $condition = null) 任务状态:0待执行, 1执行中, 2已完成, 3待重执
 * @method static Entity planTime(mixed $op = null, mixed $condition = null) 计划执行时间
 * @method static Entity startTime(mixed $op = null, mixed $condition = null) 开始执行时间
 * @method static Entity endTime(mixed $op = null, mixed $condition = null) 结束执行时间
 * @method static Entity createTime(mixed $op = null, mixed $condition = null) 创建时间
 * @method static Entity data(mixed $op = null, mixed $condition = null) 执行数据
 * @method static Entity success(mixed $op = null, mixed $condition = null) 是否执行成功
 * @method static Entity result(mixed $op = null, mixed $condition = null) 执行结果
 * @method static Entity operate(mixed $op = null, mixed $condition = null) 任务操作
 * @method static Entity remark(mixed $op = null, mixed $condition = null) 完成说明
 * @method $this setId(mixed $id, bool|ValidateRule[] $validate = false) 设置ID
 * @method $this setTitle(mixed $title, bool|ValidateRule[] $validate = false) 设置任务名称
 * @method $this setRepeated(mixed $repeated, bool|ValidateRule[] $validate = false) 设置是否可重复执行
 * @method $this setLoops(mixed $loops, bool|ValidateRule[] $validate = false) 设置重复间隔秒数
 * @method $this setCommand(mixed $command, bool|ValidateRule[] $validate = false) 设置执行指令
 * @method $this setPid(mixed $pid, bool|ValidateRule[] $validate = false) 设置进程ID
 * @method $this setAttempts(mixed $attempts, bool|ValidateRule[] $validate = false) 设置累计执行次数
 * @method $this setStatus(mixed $status, bool|ValidateRule[] $validate = false) 设置任务状态:0待执行, 1执行中, 2已完成, 3待重执
 * @method $this setPlanTime(mixed $planTime, bool|ValidateRule[] $validate = false) 设置计划执行时间
 * @method $this setStartTime(mixed $startTime, bool|ValidateRule[] $validate = false) 设置开始执行时间
 * @method $this setEndTime(mixed $endTime, bool|ValidateRule[] $validate = false) 设置结束执行时间
 * @method $this setCreateTime(mixed $createTime, bool|ValidateRule[] $validate = false) 设置创建时间
 * @method $this setData(mixed $data, bool|ValidateRule[] $validate = false) 设置执行数据
 * @method $this setSuccess(mixed $result, bool|ValidateRule[] $validate = false) 设置是否执行成功
 * @method $this setResult(mixed $result, bool|ValidateRule[] $validate = false) 设置执行结果
 * @method $this setOperate(mixed $result, bool|ValidateRule[] $validate = false) 设置任务操作
 * @method $this setRemark(mixed $result, bool|ValidateRule[] $validate = false) 设置完成说明
 */
#[ToArrayFormat(ToArrayFormat::TYPE_SNAKE)]
class SystemTaskField extends Field
{
    /**
     * ID
     * @var string
     */
    public $id;
    
    /**
     * 任务名称
     * @var string
     */
    #[Filter('trim')]
    public $title;
    
    /**
     * 是否可重复执行
     * @var bool
     */
    public $repeated;
    
    /**
     * 重复间隔秒数
     * @var int
     */
    public $loops;
    
    /**
     * 执行指令
     * @var string
     */
    #[Filter('trim')]
    public $command;
    
    /**
     * 进程ID
     * @var int
     */
    public $pid;
    
    /**
     * 累计执行次数
     * @var int
     */
    public $attempts;
    
    /**
     * 任务状态
     * @var int
     */
    public $status;
    
    /**
     * 计划执行时间
     * @var int
     */
    public $planTime;
    
    /**
     * 开始执行时间
     * @var float
     */
    public $startTime;
    
    /**
     * 结束执行时间
     * @var float
     */
    public $endTime;
    
    /**
     * 创建时间
     * @var int
     */
    public $createTime;
    
    /**
     * 执行数据
     * @var string
     */
    #[Serialize]
    public $data;
    
    /**
     * 是否执行成功
     * @var bool
     */
    public $success;
    
    /**
     * 执行结果
     * @var string
     */
    public $result;
    
    /**
     * 任务操作
     * @var array
     */
    #[Json(default: '{}')]
    public $operate;
    
    /**
     * 完成说明
     * @var string
     */
    public $remark;
    
    /**
     * 创建时间
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'createTime'])]
    #[Filter([TransHelper::class, 'date'])]
    public $formatCreateTime;
    
    /**
     * 计划执行时间
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'planTime'])]
    #[Filter([TransHelper::class, 'date'])]
    public $formatPlanTime;
    
    /**
     * 耗时
     * @var string
     */
    #[Ignore]
    public $duration;
    
    #[Ignore]
    #[ValueBindField([self::class, 'status'])]
    #[Filter([SystemTask::class, 'getStatus'])]
    public $statusName;
    
    /**
     * 是否待执行
     * @var bool
     */
    #[Ignore]
    public $wait;
    
    /**
     * 是否等待再次执行
     * @var bool
     */
    public $again;
    
    /**
     * 是否等待执行
     * @var bool
     */
    public $waiting;
    
    /**
     * 是否等待执行结果
     * @var bool
     */
    #[Ignore]
    public $pending;
    
    /**
     * 是否执行完成
     * @var bool
     */
    #[Ignore]
    public $complete;
    
    /**
     * 是否执行中
     * @var bool
     */
    public $started;
    
    /**
     * 日志ID
     * @var string
     */
    #[Ignore]
    #[ValueBindField([self::class, 'id'])]
    #[Filter([SystemTask::class, 'createLogId'])]
    public $logId;
    
    /**
     * 操作URL
     * @var string
     */
    public $operateUrl;
    
    /**
     * 操作名称
     * @var string
     */
    public $operateName;
    
    /**
     * 是否新建窗口打开操作URL
     * @var bool
     */
    public $operateBlank;
    
    
    protected function onParseAfter()
    {
        $model          = SystemTask::class();
        $this->wait     = $this->status == $model::STATUS_WAIT;
        $this->again    = $this->status == $model::STATUS_AGAIN;
        $this->started  = $this->status == $model::STATUS_STARTED;
        $this->complete = $this->status == $model::STATUS_COMPLETE;
        $this->waiting  = $this->wait || $this->again;
        $this->pending  = $this->waiting || $this->started;
        
        $this->duration = '0.000';
        if ($this->status !== $model::STATUS_STARTED) {
            $this->duration = number_format(max($this->endTime - $this->startTime, 0), 3);
        }
        
        $this->operate      = $this->operate ?: [];
        $this->operateUrl   = $this->operate['url'] ?? '';
        $this->operateBlank = $this->operate['blank'] ?? false;
        $this->operateName  = $this->operate['name'] ?? '';
        if (($this->operate['download'] ?? false)) {
            $this->operateName = '下载';
        }
    }
}