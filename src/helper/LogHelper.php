<?php

namespace BusyPHP\helper;

use BusyPHP\App;
use Exception;
use think\Exception as ThinkException;
use think\exception\ErrorException;
use think\facade\Log;
use think\log\Channel;
use Throwable;

/**
 * 日志辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/30 下午下午5:42 LogHelper.php $
 * @mixin Channel
 * @see Channel
 */
class LogHelper
{
    /**
     * @var Channel
     */
    protected $channel;
    
    /**
     * @var App
     */
    protected $app;
    
    /**
     * @var LogHelper[]
     */
    protected static $instances = [];
    
    
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
        $this->app     = App::init();
    }
    
    
    public function __call($name, $arguments)
    {
        try {
            foreach ($arguments as $index => $argument) {
                if ($argument instanceof Throwable || $argument instanceof Exception) {
                    $arguments[$index] = self::parseMessage($argument);
                }
            }
            
            return $this->channel->$name(...$arguments);
        } catch (Exception $e) {
            return $this;
        }
    }
    
    
    /**
     * 解析异常消息
     * @param Throwable $exception 异常类
     * @param string    $title 消息标题
     * @return string
     */
    public static function parseMessage(Throwable $exception, string $title = '') : string
    {
        $message = static::getMessage($exception);
        $code    = static::getCode($exception);
        $file    = $exception->getFile();
        $line    = $exception->getLine();
        $class   = get_class($exception);
        $title   = $title ? "{$title}: " : '';
        
        $msg = "[{$code}:{$class}] {$title}{$message} [{$file}:{$line}]";
        $app = App::init();
        
        // 扩展数据
        if ($app->config->get('log.record_data', true)) {
            if ($exception instanceof ThinkException) {
                $class = get_class($exception);
                foreach ($exception->getData() as $label => $item) {
                    $msg .= PHP_EOL . "[LABEL] {$class} {$label}: ";
                    foreach ($item as $key => $value) {
                        $value = is_array($value) || is_object($value) ? json_encode($value) : $value;
                        $msg   .= PHP_EOL . "{$key}: {$value}";
                    }
                }
            }
        }
        
        // 记录trace
        if ($app->config->get('log.record_trace', true)) {
            $msg .= PHP_EOL . "Trace String: " . PHP_EOL . $exception->getTraceAsString();
        }
        
        return $msg;
    }
    
    
    /**
     * 获取错误信息
     * ErrorException则使用错误级别作为错误编码
     * @access protected
     * @param Throwable $exception
     * @return string                错误信息
     */
    public static function getMessage(Throwable $exception) : string
    {
        $message = $exception->getMessage();
        $app     = App::init();
        
        if ($app->runningInConsole()) {
            return $message;
        }
        
        $lang = $app->lang;
        if (strpos($message, ':')) {
            $name    = strstr($message, ':', true);
            $message = $lang->has($name) ? $lang->get($name) . strstr($message, ':') : $message;
        } elseif (strpos($message, ',')) {
            $name    = strstr($message, ',', true);
            $message = $lang->has($name) ? $lang->get($name) . ':' . substr(strstr($message, ','), 1) : $message;
        } elseif ($lang->has($message)) {
            $message = $lang->get($message);
        }
        
        return $message;
    }
    
    
    /**
     * 获取错误编码
     * ErrorException则使用错误级别作为错误编码
     * @access protected
     * @param Throwable $exception
     * @return integer                错误编码
     */
    public static function getCode(Throwable $exception)
    {
        $code = $exception->getCode();
        
        if (!$code && $exception instanceof ErrorException) {
            $code = $exception->getSeverity();
        }
        
        return $code;
    }
    
    
    /**
     * 自定义日志通道
     * @param string $name 通道名称
     * @return LogHelper
     */
    public static function use(string $name) : LogHelper
    {
        if (isset(static::$instances[$name])) {
            return static::$instances[$name];
        }
        
        $app    = App::init();
        $config = $app->config->get('log', []);
        $type   = "bp:use_{$name}";
        if (empty($config['channels'][$type])) {
            $config['channels'][$type] = [
                'type'           => 'File',
                'path'           => $app->getRuntimeRootPath('log' . DIRECTORY_SEPARATOR . $name),
                'single'         => false,
                'apart_level'    => [],
                'max_files'      => 0,
                'json'           => false,
                'processor'      => null,
                'close'          => false,
                'format'         => '[%s][%s] %s',
                'realtime_write' => false,
            ];
            $app->config->set($config, 'log');
        }
        
        $instance                 = new static(Log::channel($type));
        static::$instances[$name] = $instance;
        
        return $instance;
    }
    
    
    /**
     * 自定义插件日志通道
     * @param string $name
     * @return LogHelper
     */
    public static function plugin(string $name) : LogHelper
    {
        return static::use("plugin_{$name}");
    }
}