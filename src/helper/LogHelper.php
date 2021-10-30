<?php

namespace BusyPHP\helper;

use BusyPHP\App;
use Exception;
use JsonSerializable;
use think\Container;
use think\contract\Jsonable;
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
 * @method $this emergency(mixed $msg, array $context = []) 系统无法使用的日志
 * @method $this alert(mixed $msg, array $context = []) 必须立即修正的日志，如：网站关闭，数据库不可用等
 * @method $this critical(mixed $msg, array $context = []) 临界条件的日志，如：应用程序组件不可用，意外异常
 * @method $this error(mixed $msg, array $context = []) 不需要立即修正的运行时错误日志
 * @method $this warning(mixed $msg, array $context = []) 非错误的日志，如：使用不推荐的API、API使用不当等
 * @method $this notice(mixed $msg, array $context = []) 正常但比较重要的日志
 * @method $this info(mixed $msg, array $context = []) 信息日志，一般用来记录运行过程等
 * @method $this debug(mixed $msg, array $context = []) 调试日志
 * @method $this log(string $level, mixed $msg, array $context = [])
 */
class LogHelper
{
    /**
     * @var Channel
     */
    protected $channel;
    
    /**
     * @var array
     */
    protected $options = [];
    
    /**
     * @var LogHelper[]
     */
    protected static $instances = [];
    
    
    /**
     * LogHelper constructor.
     * @param Channel $channel
     */
    public function __construct($channel = null)
    {
        if (!$channel instanceof Channel) {
            $channel = Log::channel();
        }
        
        $this->channel = $channel;
    }
    
    
    public function __call($name, $arguments)
    {
        switch (strtolower($name)) {
            case "emergency":
            case "alert":
            case "critical":
            case "error":
            case "warning":
            case "notice":
            case "info":
            case "debug":
                return $this->record($arguments[0], $name, $arguments[1] ?? []);
            case "log":
                return $this->record($arguments[1], $arguments[0], $arguments[2] ?? []);
            default:
                return $this->channel->$name(...$arguments);
        }
    }
    
    
    /**
     * 日志标题
     * @param string $title
     * @return $this
     */
    public function tag(string $title) : self
    {
        $this->options['tag'] = $title;
        
        return $this;
    }
    
    
    /**
     * 记录错误所在方法
     * @param string $method
     * @return $this
     */
    public function method(string $method) : self
    {
        $this->options['method'] = $method;
        
        return $this;
    }
    
    
    /**
     * 记录日志信息
     * @access public
     * @param mixed  $msg 日志信息
     * @param string $type 日志级别
     * @param array  $context 替换内容
     * @param bool   $lazy 是否延迟写入
     * @return $this
     */
    public function record($msg, string $type = 'info', array $context = [], bool $lazy = true) : self
    {
        $this->channel->record(self::parse($msg, $this->options['tag'] ?? '', $this->options['method'] ?? ''), $type, $context, $lazy);
        $this->options = [];
        
        return $this;
    }
    
    
    /**
     * 实时写入日志信息
     * @access public
     * @param mixed  $msg 调试信息
     * @param string $type 日志级别
     * @param array  $context 替换内容
     * @return $this
     */
    public function write($msg, string $type = 'info', array $context = []) : self
    {
        return $this->record($msg, $type, $context, false);
    }
    
    
    /**
     * 解析异常消息
     * @param mixed  $content 消息内容
     * @param string $tag 标签/标题
     * @param string $method 所在方法
     * @return string
     */
    public static function parse($content, ?string $tag = null, ?string $method = null) : string
    {
        $tag    = $tag ? "{$tag} : " : '';
        $method = $method ? " #method [{$method}]" : '';
        
        if ($content instanceof Throwable || $content instanceof Exception) {
            $message = static::getMessage($content);
            $code    = static::getCode($content);
            $file    = $content->getFile();
            $line    = $content->getLine();
            $class   = get_class($content);
            
            
            $msg = "{$tag}{$message} #throw [{$code}:{$class}] #file [{$file}:{$line}]{$method}";
            $app = App::init();
            
            // 扩展数据
            if ($app->config->get('log.record_data', true)) {
                if ($content instanceof \think\Exception) {
                    $class = get_class($content);
                    foreach ($content->getData() as $label => $item) {
                        $msg .= PHP_EOL . "[LABEL] {$class} {$label}: ";
                        foreach ($item as $key => $value) {
                            $value = is_array($value) || is_object($value) ? json_encode($value) : $value;
                            $msg   .= PHP_EOL . "{$key}: {$value}";
                        }
                    }
                }
            }
            
            // 记录trace
            if ($app->config->get('log.record_trace', false)) {
                $msg .= PHP_EOL . "Trace String: " . PHP_EOL . $content->getTraceAsString();
            }
        } else {
            if (!is_string($content)) {
                if (is_object($content)) {
                    if (method_exists($content, '__toString')) {
                        $content = (string) $content;
                    } else if ($content instanceof JsonSerializable) {
                        $content = json_encode($content, JSON_UNESCAPED_UNICODE);
                    } elseif ($content instanceof Jsonable) {
                        $content = $content->toJson(JSON_UNESCAPED_UNICODE);
                    } else {
                        $content = get_class($content) . ' ' . json_encode($content, JSON_UNESCAPED_UNICODE);
                    }
                } elseif (is_array($content)) {
                    $content = json_encode($content, JSON_UNESCAPED_UNICODE);
                } elseif (is_scalar($content)) {
                    $content = var_export($content, true);
                } else {
                    $content = gettype($content);
                }
            }
            
            
            $msg = "{$tag}{$content}{$method}";
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
    
    
    /**
     * 默认日志通道
     * @return LogHelper
     */
    public static function default() : LogHelper
    {
        return Container::getInstance()->make(LogHelper::class);
    }
}