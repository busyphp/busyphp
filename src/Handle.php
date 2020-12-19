<?php

namespace BusyPHP;

use BusyPHP\exception\VerifyException;
use Exception;
use think\App;
use think\Container;
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
            $args          = func_get_args();
            $prefixMessage = $args[1] ?? '';
            $prefixMessage = $prefixMessage ? $prefixMessage . ': ' : '';
            
            // 触发异常汇报事件
            if ($this->app->event->trigger(self::$reportEvent, $exception, true)) {
                return;
            }
            
            // 收集异常数据
            $data = [
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'message' => $this->getMessage($exception),
                'code'    => $this->getCode($exception),
            ];
            $log  = "[{$data['code']}] {$prefixMessage}{$data['message']} [{$data['file']}:{$data['line']}]";
            
            // 扩展数据
            if ($this->app->config->get('log.record_data', true)) {
                if ($exception instanceof \think\Exception) {
                    $class = get_class($exception);
                    foreach ($exception->getData() as $label => $item) {
                        $log .= PHP_EOL . "[LABEL] {$class} {$label}: ";
                        foreach ($item as $key => $value) {
                            $log .= PHP_EOL . "{$key}: {$value}";
                        }
                    }
                }
            }
            
            // 记录trace
            if ($this->app->config->get('log.record_trace')) {
                $log .= PHP_EOL . "Trace String: " . PHP_EOL . $exception->getTraceAsString();
            }
            
            try {
                $this->app->log->record($log, 'error');
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
