<?php

namespace BusyPHP;

use BusyPHP\exception\VerifyException;
use BusyPHP\helper\LogHelper;
use Exception;
use think\App;
use think\Container;
use think\Log;
use think\Request;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class Handle extends \think\exception\Handle
{
    /** @var string 异常渲染事件 */
    public static $renderEvent = self::class . 'render';
    
    /** @var string 异常汇报事件 */
    public static $reportEvent = self::class . 'report';
    
    
    public function __construct(App $app)
    {
        parent::__construct($app);
        
        $this->ignoreReport[] = VerifyException::class;
    }
    
    
    /**
     * 记录异常信息（包括日志或者其它方式记录）
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception) : void
    {
        if (!$this->isIgnoreReport($exception)) {
            // 触发异常汇报事件
            if ($this->app->event->trigger(self::$reportEvent, $exception, true)) {
                return;
            }
            
            try {
                $args          = func_get_args();
                $prefixMessage = $args[1] ?? '';
                $this->app->log->record(LogHelper::parse($exception, $prefixMessage), Log::ERROR);
            } catch (Exception $e) {
            }
        }
    }
    
    
    /**
     * Render an exception into an HTTP response.
     * @param Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e) : Response
    {
        // 触发异常渲染事件
        $result = $this->app->event->trigger(self::$renderEvent, [$request, $e], true);
        if ($result instanceof Response) {
            return $result;
        }
        
        return parent::render($request, $e);
    }
    
    
    /**
     * 记录异常数据
     * @param Throwable $e 异常
     * @param string    $message 异常消息
     */
    public static function log(Throwable $e, $message = '') : void
    {
        /** @var Handle $handle */
        $handle = Container::getInstance()->make(Handle::class);
        $handle->report($e, $message);
    }
}
