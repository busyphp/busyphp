<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\common;

use BusyPHP\app\admin\model\system\logs\SystemLogs;
use BusyPHP\app\admin\model\system\task\StartedException;
use BusyPHP\app\admin\model\system\task\SystemTask;
use BusyPHP\app\admin\model\system\task\SystemTaskInterface;
use Closure;
use InvalidArgumentException;
use LogicException;
use think\Container;
use think\Response;
use think\route\Url;
use Throwable;

/**
 * 系统任务操作
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/16 20:14 Task.php $
 */
class Task
{
    /** @var string 日志注入 */
    public const MAKER_LOG = 'log';
    
    /** @var string 下载地址注入 */
    public const MAKER_DOWNLOAD_URL = 'download_url';
    
    /** @var string 响应注入 */
    public const MAKER_RESPONSE = 'response';
    
    /**
     * 服务注入
     * @var Closure[]
     */
    protected static array $maker = [];
    
    /**
     * 任务处理类
     * @var string
     */
    protected string $class;
    
    /**
     * 任务数据
     * @var mixed
     */
    protected mixed $data;
    
    /**
     * 任务标题
     * @var string
     */
    protected string $title;
    
    /**
     * 延迟执行秒数
     * @var int
     */
    protected int $later;
    
    /**
     * 循环间隔秒数
     * @var int
     */
    protected int $loop;
    
    /**
     * 输出消息
     * @var string
     */
    protected string $message = '';
    
    /**
     * 操作URL
     * @var Url|string|bool|Closure
     */
    protected Url|string|bool|Closure $operateUrl = '';
    
    /**
     * 操作名称
     * @var string
     */
    protected string $operateName = '';
    
    /**
     * 是否新建窗口打开操作URL
     * @var bool|string
     */
    protected bool|string $operateBlank = false;
    
    /**
     * 日志类型
     * @var int|Closure
     */
    protected int|Closure $logType = SystemLogs::TYPE_DEFAULT;
    
    /**
     * 日志操作名称
     * @var string
     */
    protected string $logName = '';
    
    /**
     * 日志操作结果
     * @var string
     */
    protected string $logResult = '';
    
    
    /**
     * 快速实例化
     * @param class-string<SystemTaskInterface> $class 任务处理类
     * @param mixed                             $data 任务数据
     * @param string                            $title 任务标题
     * @param int                               $later 延迟执行秒数
     * @param int                               $loop 循环间隔秒数
     * @return static
     */
    public static function init(string $class, mixed $data = null, string $title = '', int $later = 0, int $loop = 0) : static
    {
        return Container::getInstance()->make(self::class, [$class, $data, $title, $later, $loop], true);
    }
    
    
    /**
     * 设置服务注入
     * @param string  $type 注入类型
     * @param Closure $maker 注入回调
     * @return void
     */
    public static function maker(string $type, Closure $maker) : void
    {
        static::$maker[$type] = $maker;
    }
    
    
    /**
     * 构造函数
     * @param class-string<SystemTaskInterface> $class 任务处理类
     * @param mixed                             $data 任务数据
     * @param string                            $title 任务标题
     * @param int                               $later 延迟执行秒数
     * @param int                               $loop 循环间隔秒数
     */
    public function __construct(string $class, mixed $data = null, string $title = '', int $later = 0, int $loop = 0)
    {
        $this->class = $class;
        $this->data  = $data;
        $this->title = $title;
        $this->later = $later;
        $this->loop  = $loop;
    }
    
    
    /**
     * 设置提示消息
     * @param string $message
     * @return $this
     */
    public function message(string $message) : static
    {
        $this->message = $message;
        
        return $this;
    }
    
    
    /**
     * 设置任务处理完成操作
     * @param Url|string|bool|Closure $url 操作URL，设为true或闭包则使用内置下载
     * @param string                  $name 操作名称，$url为true或闭包则代表下载文件名
     * @param bool                    $blank 是否新建窗口打开URL，$url为true或闭包则代表下载文件的mimetype
     * @return $this
     */
    public function operate(Url|string|bool|Closure $url, string $name = '', bool|string $blank = false) : static
    {
        $this->operateUrl   = $url;
        $this->operateName  = $name;
        $this->operateBlank = $blank;
        
        return $this;
    }
    
    
    /**
     * 设置操作日志
     * @param int|Closure $type 日志操作类型
     * @param string      $name 日志操作名称
     * @param string      $result 日志操作结果
     * @return $this
     */
    public function log(int|Closure $type, string $name = '', string $result = '') : static
    {
        if (!$type instanceof Closure && $name === '') {
            throw new InvalidArgumentException('日志操作名称不能为空');
        }
        
        $this->logType   = $type;
        $this->logName   = $name;
        $this->logResult = $result;
        
        return $this;
    }
    
    
    /**
     * 创建任务
     * @return Response
     * @throws Throwable
     */
    public function create() : Response
    {
        if ($this->operateUrl === true) {
            $makerDownloadUrl = static::$maker[static::MAKER_DOWNLOAD_URL] ?? null;
            if (!$makerDownloadUrl) {
                throw new LogicException('未设置URL生成回调');
            }
            
            $this->operateUrl = $makerDownloadUrl;
        }
        
        try {
            $info = SystemTask::init()
                ->operate($this->operateUrl, $this->operateName, $this->operateBlank)
                ->create($this->class, $this->data, $this->title, $this->later, $this->loop);
            
            // 操作日志
            if ($this->logType instanceof Closure) {
                call_user_func_array($this->logType, []);
            } else {
                $makerLog = static::$maker[static::MAKER_LOG] ?? null;
                if ($makerLog) {
                    call_user_func_array($makerLog, [$this->logType, $this->logName, $this->logResult]);
                } else {
                    SystemLogs::init()->record($this->logType, $this->logName, $this->logResult);
                }
            }
        } catch (StartedException $e) {
            $this->message = $e->getMessage();
            
            $info = $e->getInfo();
        }
        
        $makerResponse = static::$maker[static::MAKER_RESPONSE] ?? null;
        if ($makerResponse) {
            return call_user_func_array($makerResponse, [$info, $this->message]);
        }
        
        return ConsoleLog::dialog($info, $this->message);
    }
}