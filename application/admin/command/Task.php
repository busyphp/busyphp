<?php
namespace BusyPHP\app\admin\command;

use BusyPHP\app\admin\model\system\task\SystemTask;
use BusyPHP\helper\ConsoleHelper;
use Psr\Log\NullLogger;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\console\Table;
use think\exception\Handle;
use Throwable;

/**
 * 系统任务命令
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/11 10:04 Task.php $
 */
class Task extends Command
{
    /**
     * @var Handle
     */
    protected Handle $handle;
    
    
    public function __construct(Handle $handle)
    {
        parent::__construct();
        
        $this->handle = $handle;
    }
    
    
    protected function configure()
    {
        $this->setName('bp:task')
            ->addArgument('action', Argument::OPTIONAL, 'start|stop|restart|listen|run|list|status|clean', 'start')
            ->addArgument('id', Argument::OPTIONAL, 'Task ID')
            ->addOption('daemon', 'd', Option::VALUE_NONE, 'The task process listen in daemon mode')
            ->setDescription('Asynchronous task command for BusyPHP');
    }
    
    
    protected function execute(Input $input, Output $output)
    {
        switch ($input->getArgument('action')) {
            case 'run':
                $this->runTask();
            break;
            case 'list':
                $this->list();
            break;
            case 'start':
                $this->input->hasOption('daemon') ? $this->start() : $this->listen();
            break;
            case 'status':
                $this->status();
            break;
            case 'stop':
                $this->stop();
            break;
            case 'listen':
                $this->listen();
            break;
            case 'clean':
                $this->clean();
            break;
            default:
                $this->output->error("allow stop|start|status|list|listen|clean|run");
        }
    }
    
    
    /**
     * 获取运行中的监听进程
     * @return int[]
     */
    protected function getRunningListen() : array
    {
        $scriptFile = ConsoleHelper::scriptFile();
        $range      = [
            "$scriptFile {$this->getName()} listen",
            "$scriptFile {$this->getName()} start",
            "$scriptFile {$this->getName()}"
        ];
        $list       = [];
        foreach (ConsoleHelper::queryScriptList($this->getName()) as $item) {
            if (false === $index = strrpos($item['cmd'], $scriptFile)) {
                continue;
            }
            
            $cmd = substr($item['cmd'], $index);
            if (in_array($cmd, $range)) {
                $list[] = $item['pid'];
            }
        }
        
        return $list;
    }
    
    
    /**
     * 执行指定任务
     */
    protected function runTask()
    {
        $id = $this->input->getArgument('id');
        if (!$id) {
            $this->output->error(sprintf('Task ID cannot be empty. please enter php think %s `TaskID number`', $this->getName()));
            
            return;
        }
        
        try {
            SystemTask::init()->run($id, getmypid());
        } catch (Throwable $e) {
            $this->output->error($e->getMessage());
        }
    }
    
    
    /**
     * 启动任务
     */
    protected function start()
    {
        if ($list = $this->getRunningListen()) {
            $this->output->error(sprintf('The task process is already running, pid with %s.', $list[0]));
            
            return;
        }
        
        $command = ConsoleHelper::makeScriptCommand([$this->getName(), 'listen']);
        $process = ConsoleHelper::makeShell(ConsoleHelper::makeShellCommand($command, true));
        $process->run();
    }
    
    
    /**
     * 停止任务
     */
    protected function stop()
    {
        if (!$list = $this->getRunningListen()) {
            $this->output->error('The task process is not running.');
            
            return;
        }
        
        $this->output->writeln('The task process is stopping, please wait...');
        foreach ($list as $item) {
            ConsoleHelper::close($item);
        }
        $this->output->info('The task process is stopped successfully.');
    }
    
    
    /**
     * 查询任务状态
     */
    protected function status()
    {
        if ($list = $this->getRunningListen()) {
            $this->output->info(sprintf('The task listen process is running, with pid %s', $list[0]));
        } else {
            $this->output->error('The task listen process is not running.');
        }
    }
    
    
    /**
     * 清理任务
     */
    protected function clean()
    {
        try {
            $result = SystemTask::init()->clean();
            $this->output->info(sprintf('cleaned, deleted total %s, reset total %s.', $result['deleted'], $result['reset']));
        } catch (Throwable $e) {
            $this->output->error($e->getMessage());
        }
    }
    
    
    /**
     * 执行任务监听
     * @throws Throwable
     */
    protected function listen()
    {
        if ($list = $this->getRunningListen()) {
            $this->output->error(sprintf('The task listen process is already running, with pid %s', $list[0]));
            
            return;
        }
        
        $this->output->info(sprintf('The task listen process is started, with pid %s.', getmypid()));
        $this->output->writeln("You can exit with <info>`CTRL-C`</info>");
        $this->app->db->setLog(new NullLogger());
        while (true) {
            try {
                $systemTask = SystemTask::init();
                $systemTask::setRunningServer(getmypid(), $this->getName());
                
                if (!$info = $systemTask->getWait()) {
                    ConsoleHelper::sleep(3);
                    continue;
                }
            } catch (Throwable $e) {
                $this->handle->report($e);
                $this->output->error($e->getMessage());
                
                throw $e;
            }
            
            $command = ConsoleHelper::makeScriptCommand([$this->getName(), 'run', $info->id]);
            $process = ConsoleHelper::makeShell(ConsoleHelper::makeShellCommand($command, true));
            $process->run(function($type, $line) {
                $this->output->write($line);
            });
        }
    }
    
    
    /**
     * 列出所有任务
     */
    protected function list()
    {
        $table = new Table();
        $table->setHeader(['PID', 'Command']);
        $rows = [];
        foreach (ConsoleHelper::queryScriptList($this->getName()) as $item) {
            $rows[] = [$item['pid'], $item['cmd']];
        }
        $table->setRows($rows);
        $this->table($table);
    }
}