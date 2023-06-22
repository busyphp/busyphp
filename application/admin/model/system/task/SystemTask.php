<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\model\system\task;

use BusyPHP\App;
use BusyPHP\app\admin\component\common\ConsoleLog;
use BusyPHP\exception\ClassNotImplementsException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\CacheHelper;
use BusyPHP\helper\ClassHelper;
use BusyPHP\helper\LogHelper;
use BusyPHP\interfaces\ContainerInterface;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use Closure;
use LogicException;
use think\Container;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;
use think\response\File;
use think\route\Url;
use Throwable;

/**
 * 系统任务模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/10 10:28 SystemTask.php $
 * @method SystemTaskField getInfo(string $id, string $notFoundMessage = null)
 * @method SystemTaskField|null findInfo(string $id = null)
 * @method SystemTaskField[] selectList()
 * @method SystemTaskField[] indexList(string|Entity $key = '')
 * @method SystemTaskField[] indexListIn(array $range, string|Entity $key = '', string|Entity $field = '')
 */
class SystemTask extends Model implements ContainerInterface
{
    // +----------------------------------------------------
    // + 执行状态
    // +----------------------------------------------------
    /** @var int 待执行 */
    public const STATUS_WAIT = 0;
    
    /** @var int 执行中 */
    public const STATUS_STARTED = 1;
    
    /** @var int 已完成 */
    public const STATUS_COMPLETE = 2;
    
    /** @var int 待重执 */
    public const STATUS_AGAIN = 3;
    
    protected string     $fieldClass          = SystemTaskField::class;
    
    protected string     $dataNotFoundMessage = '任务不存在';
    
    protected static int $runningServerTime;
    
    
    /**
     * @inheritDoc
     */
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    /**
     * 生成日志ID
     * @param string $id
     * @return string
     */
    public static function createLogId(string $id) : string
    {
        return sprintf('system_task_%s', $id);
    }
    
    
    /**
     * 生成ID
     * @param string $command
     * @param mixed  $data
     * @return string
     */
    public static function createId(string $command, mixed $data) : string
    {
        return md5($command . '@' . serialize($data));
    }
    
    
    /**
     * 获取状态
     * @param int|null $status
     * @return string|array|null
     */
    public static function getStatusMap(int $status = null) : string|array|null
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstAttrs(self::class, 'STATUS_', ClassHelper::ATTR_NAME), $status);
    }
    
    
    /**
     * 获取接口类
     * @param class-string<SystemTaskInterface> $class 任务处理类
     * @return SystemTaskInterface
     */
    protected function getInterface(string $class) : SystemTaskInterface
    {
        if (!$class) {
            throw new LogicException('Task class not specified for execution');
        }
        
        if (!is_subclass_of($class, SystemTaskInterface::class)) {
            throw new ClassNotImplementsException($class, SystemTaskInterface::class);
        }
        
        return Container::getInstance()->make($class, [], true);
    }
    
    
    /**
     * 设置任务成功操作
     * @param Url|string|Closure $url 操作URL，设为{@see Closure}则使用内置下载，请在{@see Closure}内返回下载的URL，并在控制器中使用{@see SystemTask::downloadResult()}处理下载
     * @param string             $name 操作名称，$url为{@see Closure}则代表下载的文件名
     * @param bool               $blank 是否新建窗口打开URL，$url为{@see Closure}则代表下载文件的mimetype
     * @return static
     */
    public function operate(string|Url|Closure $url, string $name = '', bool|string $blank = false) : static
    {
        $this->options['__operate'] = [
            'url'   => $url,
            'name'  => $name,
            'blank' => $blank
        ];
        
        return $this;
    }
    
    
    /**
     * 创建任务
     * @param class-string<SystemTaskInterface> $class 任务处理类
     * @param mixed                             $data 任务处理数据
     * @param string                            $title 任务标题
     * @param int                               $later 延迟执行秒数
     * @param int                               $loop 循环间隔秒数
     * @return SystemTaskField
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function create(string $class, mixed $data = null, string $title = '', int $later = 0, int $loop = 0) : SystemTaskField
    {
        $operate = $this->options['__operate'] ?? [];
        
        // 已存在且已启动的任务不允许覆盖
        $info = $this->findInfo($id = static::createId($class, $data));
        if ($info && $info->started) {
            throw new StartedException($info);
        }
        
        $this->getInterface($class)->configSystemTask($config = new ConfigSystemTask($data, $title, $later, $loop));
        $title = trim($config->getTitle());
        if ($title === '') {
            throw new VerifyException('任务名称不能为空', 'title');
        }
        
        // 解析操作
        if ($operate) {
            if ($operate['url'] instanceof Closure) {
                $operate['url']      = call_user_func_array($operate['url'], [
                    $id,
                    $operate['name'],
                    is_string($operate['blank']) ? $operate['blank'] : ''
                ]);
                $operate['name']     = '';
                $operate['download'] = true;
                $operate['blank']    = true;
            }
            $operate['url'] = (string) $operate['url'];
        }
        
        $insert = SystemTaskField::init();
        $insert->setId($id);
        $insert->setTitle($title);
        $insert->setCommand($class);
        $insert->setData($data);
        $insert->setLoops($config->getLoop());
        $insert->setCreateTime(time());
        $insert->setPlanTime(time() + $config->getLater());
        $insert->setStatus(self::STATUS_WAIT);
        $insert->setOperate($operate);
        $this->replace(true)->insert($insert);
        
        ConsoleLog::create(self::createLogId($id), '创建任务成功');
        
        return $this->getInfo($id);
    }
    
    
    /**
     * 删除任务
     * @param string $id 任务ID
     * @return int
     * @throws Throwable
     */
    public function remove(string $id) : int
    {
        return $this->delete($id);
    }
    
    
    /**
     * 重置任务
     * @param string $id 任务ID
     * @throws Throwable
     */
    public function reset(string $id)
    {
        $this->transaction(function() use ($id) {
            $info = $this->lock(true)->getInfo($id);
            if ($info->started) {
                throw new LogicException('无法重置执行中的任务');
            }
            
            $data = SystemTaskField::init();
            $data->setPlanTime(time());
            $data->setStatus(self::STATUS_AGAIN);
            $data->setPid(0);
            $this->where(SystemTaskField::id($info->id))->update($data);
            
            ConsoleLog::create($info->logId, '重置任务成功');
        });
    }
    
    
    /**
     * 获取一个等待执行的任务
     * @throws Throwable
     */
    public function getWait() : ?SystemTaskField
    {
        return $this->transaction(function() {
            $info = $this->lock(true)
                ->where(SystemTaskField::planTime('<=', time()))
                ->where(SystemTaskField::status('in', [self::STATUS_WAIT, self::STATUS_AGAIN]))
                ->order(SystemTaskField::planTime(), 'asc')
                ->findInfo();
            if (!$info || $info->started) {
                return null;
            }
            
            // 标记状态，防止再次被执行
            $data = SystemTaskField::init();
            $data->setStatus(self::STATUS_STARTED);
            $data->setAttempts(SystemTaskField::attempts('+', 1));
            $data->setStartTime(microtime(true));
            $this->where(SystemTaskField::id($info->id))->update($data);
            
            ConsoleLog::info($info->logId, '任务开始处理');
            
            return $info;
        });
    }
    
    
    /**
     * 设置进程ID
     * @param string $id 任务ID
     * @param int    $pid 进程ID
     * @throws DbException
     */
    public function setPid(string $id, int $pid)
    {
        $this->where(SystemTaskField::id($id))->setField(SystemTaskField::pid(), $pid);
    }
    
    
    /**
     * 设置执行完成
     * @param SystemTaskField $info 任务数据
     * @param bool|string     $res 是否处理成功或处理成功结果字符串(以配合执行成功操作)，空字符串为不成功
     * @param string          $remark 完成说明
     * @throws DbException
     */
    public function setComplete(SystemTaskField $info, bool|string $res, string $remark)
    {
        $success = $res;
        $result  = '';
        if (is_string($res)) {
            $success = $res !== '';
            $result  = $res;
        }
        
        // 设置结果
        $data = SystemTaskField::init();
        $data->setEndTime(microtime(true));
        $data->setRemark($remark);
        $data->setSuccess($success);
        $data->setResult($result);
        $data->setPid(0);
        
        // 循环任务
        if ($info->loops > 0) {
            $data->setPlanTime(time() + $info->loops);
            $data->setStatus(self::STATUS_AGAIN);
        } else {
            $data->setStatus(self::STATUS_COMPLETE);
        }
        
        $this->where(SystemTaskField::id($info->id))->update($data);
        ConsoleLog::finish($info->logId, $remark, $success);
    }
    
    
    /**
     * 清理任务
     * @return array{reset: int, deleted: int}
     * @throws DbException
     */
    public function clean(int $resetDuration = 60, int $deleteDuration = 86400 * 30) : array
    {
        // 重置超时任务
        $data = SystemTaskField::init();
        $data->setPid(0);
        $data->setStatus(self::STATUS_STARTED);
        $data->setResult('任务执行超时，已重置任务！');
        $data->setPlanTime(time());
        $reset = $this
            ->where(SystemTaskField::status(self::STATUS_STARTED))
            ->where(SystemTaskField::planTime('<', time() - $resetDuration))
            ->update($data);
        
        // 删除已完成任务
        $deleted = $this->where(SystemTaskField::status(self::STATUS_COMPLETE))
            ->where(SystemTaskField::planTime('<', time() - $deleteDuration))
            ->delete();
        
        return [
            'reset'   => $reset,
            'deleted' => $deleted
        ];
    }
    
    
    /**
     * 运行一个任务
     * @param string $id 任务ID
     * @param int    $pid 进程ID
     * @throws Throwable
     */
    public function run(string $id, int $pid) : void
    {
        try {
            if ($pid > 0) {
                $this->setPid($id, $pid);
            }
            
            $info = $this->getInfo($id);
            
            // 任务已执行完毕，直接退出
            if ($info->complete) {
                return;
            }
            
            $this->getInterface($info->command)->runSystemTask(new RunSystemTask($info));
            
            throw new ResultException('任务处理完成');
        } catch (Throwable $e) {
            if (isset($info)) {
                try {
                    // 任务执行完成
                    if ($e instanceof ResultException && ($e->getResult() === true || (is_string($e->getResult()) && $e->getResult() !== ''))) {
                        $this->setComplete($info, $e->getResult(), $e->getMessage());
                        
                        return;
                    }
                    
                    $this->setComplete($info, false, $e->getMessage());
                } catch (Throwable $setError) {
                    LogHelper::default()->tag('设置任务状态失败', __METHOD__)->error($setError);
                }
            }
            
            throw $e;
        }
    }
    
    
    /**
     * 下载结果
     * @param string $id 任务ID
     * @param string $name 下载名称
     * @param string $mimetype 文件mimetype
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function downloadResult(string $id, string $name = '', string $mimetype = '') : Response
    {
        $info = $this->getInfo($id);
        if (!$info->complete) {
            throw new LogicException('文件正在生成中');
        }
        if (!$info->success) {
            throw new LogicException(sprintf('文件生成失败: %s', $info->remark));
        }
        
        if (is_subclass_of($info->command, DownloadSystemTaskResultInterface::class)) {
            /** @var DownloadSystemTaskResultInterface $command */
            $command = Container::getInstance()->make($info->command, [], true);
            
            return $command->downloadSystemTaskResult($info, $name, $mimetype);
        }
        
        $path = $info->result;
        if (!$path) {
            throw new LogicException('无法获取文件');
        }
        if (!is_file($path)) {
            $path = App::urlToPath($path);
        }
        
        $file = new File($path);
        $file->isContent(false);
        $file->name($name);
        $file->mimeType($mimetype);
        $file->expire(0);
        
        return $file;
    }
    
    
    /**
     * 设置运行的服务程序
     * @param int    $pid 进程ID
     * @param string $name 服务名称
     */
    public static function setRunningServer(int $pid, string $name)
    {
        if (isset(self::$runningServerTime) && time() - self::$runningServerTime < 3) {
            self::$runningServerTime = time();
            
            return;
        }
        
        self::$runningServerTime = time();
        CacheHelper::set(self::class, 'server', [
            'runtime' => time(),
            'name'    => $name,
            'pid'     => $pid
        ]);
    }
    
    
    /**
     * 获取运行的服务程序
     * @param int $timeout 判断未运行的超时秒数
     * @return array{runtime:string,name:string,pid:int}|false 返回false则服务未运行，返回int则为进程ID
     */
    public static function getRunningServer(int $timeout = 5) : array|false
    {
        $config = CacheHelper::get(self::class, 'server');
        if (!$config || !isset($config['runtime']) || !isset($config['pid'])) {
            return false;
        }
        
        if (time() - $config['runtime'] <= max($timeout, 3)) {
            return $config;
        }
        
        return false;
    }
}