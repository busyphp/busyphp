<?php
declare(strict_types = 1);

namespace BusyPHP\helper;

use BusyPHP\App;
use RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * 控制台辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/11 08:52 ConsoleHelper.php $
 */
class ConsoleHelper
{
    /**
     * 获取当前执行脚本名称
     * @return string
     */
    public static function scriptFile() : string
    {
        return 'think';
    }
    
    
    /**
     * 执行休眠
     * @param float|int $seconds 休眠的秒数
     */
    public static function sleep(float|int $seconds) : void
    {
        if ($seconds < 1 || is_float($seconds)) {
            usleep((int) ceil($seconds * 1000000));
        } else {
            sleep($seconds);
        }
    }
    
    
    /**
     * 获取PHP执行文件
     * @return string
     */
    public static function phpBinary() : string
    {
        return (new PhpExecutableFinder)->find(false);
    }
    
    
    /**
     * 是否Window环境
     * @return bool
     */
    public static function isWindow() : bool
    {
        static $win;
        if (!isset($win)) {
            $win = '\\' === DIRECTORY_SEPARATOR;
        }
        
        return $win;
    }
    
    
    /**
     * 创建一个Process实例来执行命令
     * @param array       $command 命令行
     * @param string|null $cwd 工作目录或null默认使用当前PHP进程的工作目录
     * @param array|null  $env 环境变量
     * @param mixed|null  $input stream resource 或 {@see \Traversable}
     * @param float|null  $timeout 执行超时秒数
     * @return Process
     */
    public static function makeProcess(array $command, string $cwd = null, array $env = null, mixed $input = null, ?float $timeout = 60) : Process
    {
        return new Process(
            $command,
            is_null($cwd) ? App::getInstance()->getRootPath() : $cwd,
            $env,
            $input,
            $timeout
        );
    }
    
    
    /**
     * 创建一个Process实例作为要在shell中运行的命令行。
     * @param array|string $command 命令行，数组代表第一个是unix命令，第二个代表window命令
     * @param string|null  $cwd 工作目录或null默认使用当前PHP进程的工作目录
     * @param array|null   $env 环境变量
     * @param mixed|null   $input stream resource 或 {@see \Traversable}
     * @param float|null   $timeout 执行超时秒数
     */
    public static function makeShell(array|string $command, string $cwd = null, array $env = null, mixed $input = null, ?float $timeout = 60) : Process
    {
        if (is_array($command)) {
            if (static::isWindow()) {
                $command = $command[1] ?? '';
            } else {
                $command = $command[0] ?? '';
            }
        }
        
        return Process::fromShellCommandline(
            $command,
            is_null($cwd) ? App::getInstance()->getRootPath() : $cwd,
            $env,
            $input,
            $timeout
        );
    }
    
    
    /**
     * 执行一条shell命令
     * @param array|string $command 命令行，数组代表第一个是unix命令，第二个代表window命令
     * @param string|null  $cwd 工作目录或null默认使用当前PHP进程的工作目录
     * @param array|null   $env 环境变量
     * @param mixed|null   $input stream resource 或 {@see \Traversable}
     * @param float|null   $timeout 执行超时秒数
     * @return string[]
     */
    public static function execShell(array|string $command, string $cwd = null, array $env = null, mixed $input = null, ?float $timeout = 60) : array
    {
        $lines = [];
        $error = [];
        $code  = static::makeShell($command, $cwd, $env, $input, $timeout)
            ->run(function($type, $data) use (&$error, &$lines) {
                if ($type === Process::ERR) {
                    $error[] = $data;
                } else {
                    $lines[] = $data;
                }
            });
        if ($error) {
            throw new RuntimeException(implode('', $error), $code);
        }
        
        return explode(PHP_EOL, implode('', $lines));
    }
    
    
    /**
     * 查询进程列表
     * @param string $command 要查询的进程命令
     * @param string $windowName window程序名称
     * @return array<array{pid: int, cmd: string}>
     */
    public static function queryProcessList(string $command, string $windowName = 'php.exe') : array
    {
        $lines = static::execShell([
            sprintf('ps ax|grep -v grep|grep "%s"', $command),
            sprintf('wmic process where name="%s" get processid,CommandLine', $windowName)
        ]);
        
        $list = [];
        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/', ' ', str_replace('\\', '/', $line)));
            if (false === stripos($line, $command)) {
                continue;
            }
            
            $line = explode(' ', $line);
            if (static::isWindow()) {
                $pid = (int) array_pop($line);
                $cmd = implode(' ', $line);
            } else {
                $pid = (int) array_shift($line);
                array_shift($line);
                array_shift($line);
                array_shift($line);
                $cmd = implode(' ', $line);
            }
            if ($pid === getmypid()) {
                continue;
            }
            $list[] = ['pid' => $pid, 'cmd' => $cmd];
        }
        
        return $list;
    }
    
    
    /**
     * 查询通过系统脚本执行命令的进程列表
     * @param string $command 要查询的进程命令
     * @param string $windowName window程序名称
     * @return array<array{pid: int, cmd: string}>
     */
    public static function queryScriptList(string $command, string $windowName = 'php.exe') : array
    {
        return static::queryProcessList(sprintf('%s %s', static::scriptFile(), $command), $windowName);
    }
    
    
    /**
     * 生成脚本命令行
     * @param array $command
     * @return array
     */
    public static function makeScriptCommand(array $command) : array
    {
        return [static::phpBinary(), static::scriptFile(), ...$command];
    }
    
    
    /**
     * 生成shell脚本命令
     * @param array|string $command 命令
     * @param bool         $daemon 是否蜕变为后台允许
     * @return string
     */
    public static function makeShellCommand(array|string $command, bool $daemon = false) : string
    {
        if (is_array($command)) {
            $command = implode(' ', $command);
        }
        if ($daemon) {
            $command .= ' > /dev/null 2>&1 &';
        }
        
        return $command;
    }
    
    
    /**
     * 关闭任务进程
     * @param int $pid 进程ID
     */
    public static function close(int $pid) : void
    {
        static::execShell([
            "kill -9 $pid",
            "wmic process $pid call terminate"
        ]);
    }
}