<?php
declare (strict_types = 1);

namespace BusyPHP;

use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\exception\MethodNotFoundException;
use BusyPHP\helper\ClassHelper;
use BusyPHP\helper\LogHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\interfaces\ModelSceneValidateInterface;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\FilterHelper;
use BusyPHP\model\traits\Event;
use BusyPHP\traits\Cache;
use Closure;
use PDOStatement;
use Psr\Log\LoggerInterface;
use think\Collection;
use think\Container;
use think\Db;
use think\db\BaseQuery;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\InvalidArgumentException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\db\Raw;
use think\DbManager;
use think\helper\Str;
use think\Log;
use think\model\concern\TimeStamp;
use think\Validate;
use Throwable;

/**
 * 数据模型基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/30 下午9:22 上午 Model.php $
 * @method bool onBeforeInsert() 新增前回调, 返回false阻止新增
 * @method bool onBeforeUpdate() 更新前回调, 返回false阻止更新
 * @method bool onBeforeDelete() 删除前回调, 返回false阻止更新
 * @method void onChanged(string $method, mixed $id, array $options) 新增/更新/删除后回调
 * @method void onUpdateAll() 批量更新回调
 * @method void onInsertAll() 批量更新回调
 * @method void onAfterWrite($id, array $options) 新增/更新完成后回调
 * @method void onAfterInsert($id, array $options) 新增完成后回调
 * @method void onAfterUpdate($id, array $options) 更新完成后回调
 * @method void onAfterDelete($id, array $options) 删除完成后回调
 * @method $this lockShare(boolean $isLock) 是否加共享锁，允许其它对象读不允许写
 * @template T
 */
abstract class Model extends Query
{
    use Cache;
    use Event;
    use TimeStamp;
    
    // +----------------------------------------------------
    // + 常用场景名称
    // +----------------------------------------------------
    /** @var string 操作场景-创建信息 */
    public const SCENE_CREATE = 'create';
    
    /** @var string 操作场景-更新信息 */
    public const SCENE_UPDATE = 'update';
    
    //+--------------------------------------
    //| 数据库回调常量
    //+--------------------------------------
    /** @var string 新增完成事件 */
    public const CHANGED_INSERT = 'insert';
    
    /** @var string 更新完成事件 */
    public const CHANGED_UPDATE = 'update';
    
    /** @var string 删除完成事件 */
    public const CHANGED_DELETE = 'delete';
    
    /**
     * {@see Model::find()} 方法参数过滤器
     * @var string|callable|Closure
     */
    protected $findFilter = 'trim';
    
    /**
     * {@see Model::remove()} 方法参数过滤器
     * @var string|callable|Closure
     */
    protected $removeFilter = 'trim';
    
    /**
     * 绑定通用信息解析类
     * @var class-string<Field>
     */
    protected $bindParseClass;
    
    /**
     * 绑定包含扩展信息的解析类
     * @var class-string<Field>
     */
    protected $bindParseExtendClass;
    
    /**
     * 自定义绑定信息解析类
     * @var class-string<Field>
     */
    private $useBindParseClass;
    
    /**
     * 单条信息不存在的错误消息
     * @var string
     */
    protected $dataNotFoundMessage = '';
    
    /**
     * 列表信息不存在错误消息
     * @var string
     */
    protected $listNotFoundMessage = '';
    
    /**
     * 设置join别名
     * @var string
     */
    protected $joinAlias = '';
    
    /**
     * 当前数据表主键
     * @var string
     */
    protected $pk = 'id';
    
    /**
     * 数据表后缀
     * @var string
     */
    protected $suffix = '';
    
    /**
     * 当前模型的数据库连接标识
     * @var string
     */
    protected $connect = '';
    
    /**
     * 当前Db对象
     * @var Db
     */
    protected $manager;
    
    /**
     * 日志接口
     * @var LoggerInterface
     */
    protected $logger = null;
    
    /**
     * Db对象
     * @var Db
     */
    protected static $db;
    
    /**
     * 容器对象的依赖注入方法
     * @var callable
     */
    protected static $invoker;
    
    /**
     * 服务注入
     * @var Closure[]
     */
    protected static $maker = [];
    
    /**
     * 方法注入
     * @var Closure[][]
     */
    protected static $macro = [];
    
    /**
     * 数据对象解析注入
     * @var Closure[]
     */
    protected static $bindParseClassHandle = [];
    
    
    /**
     * 设置服务注入
     * @param Closure $maker
     * @return void
     */
    public static function maker(Closure $maker)
    {
        static::$maker[] = $maker;
    }
    
    
    /**
     * 设置方法注入
     * @param string  $method
     * @param Closure $closure
     * @return void
     */
    public static function macro(string $method, Closure $closure)
    {
        if (!isset(static::$macro[static::class])) {
            static::$macro[static::class] = [];
        }
        static::$macro[static::class][$method] = $closure;
    }
    
    
    /**
     * 设置数据对象解析注入
     * @param Closure $closure
     * @return void
     */
    public static function bindParseClassHandle(Closure $closure)
    {
        static::$bindParseClassHandle[] = $closure;
    }
    
    
    /**
     * 设置Db对象
     * @param DbManager $db Db对象
     * @return void
     */
    public static function setDb(DbManager $db)
    {
        static::$db = $db;
    }
    
    
    /**
     * 设置容器对象的依赖注入方法
     * @access public
     * @param callable $callable 依赖注入方法
     * @return void
     */
    public static function setInvoker(callable $callable) : void
    {
        static::$invoker = $callable;
    }
    
    
    /**
     * 切换后缀进行查询
     * @param string               $suffix 切换的表后缀
     * @param LoggerInterface|null $log 日志接口
     * @param string               $connect 数据库连接标识
     * @param bool                 $force 是否强制重连
     * @return static
     */
    public static function suffix(string $suffix, LoggerInterface $log = null, string $connect = '', bool $force = false)
    {
        $model = new static($log, $connect, $force);
        $model->setSuffix($suffix);
        
        return $model;
    }
    
    
    /**
     * 切换数据库连接进行查询
     * @param string               $connect 数据库连接标识
     * @param LoggerInterface|null $log 日志接口
     * @param bool                 $force 是否强制重连
     * @return static
     */
    public static function connect(string $connect, LoggerInterface $log = null, bool $force = false)
    {
        return new static($log, $connect, $force);
    }
    
    
    /**
     * 定义模型类名
     * @return class-string<Model>
     */
    protected static function defineAbstract() : string
    {
        return static::class;
    }
    
    
    /**
     * 获取模型类名
     * @return static|string
     */
    public static function abstract() : string
    {
        return Container::getInstance()->getAlias(static::defineAbstract());
    }
    
    
    /**
     * 实例化一个模型
     * @param LoggerInterface|null $log 日志接口
     * @param string               $connect 连接标识
     * @param bool                 $force 是否强制重连
     * @return static
     */
    public static function init(LoggerInterface $log = null, string $connect = '', bool $force = false)
    {
        $class = self::abstract();
        
        return new $class($log, $connect, $force);
    }
    
    
    /**
     * 获取单例模型
     * @return static
     */
    public static function instance()
    {
        return Container::getInstance()->make(static::class);
    }
    
    
    /**
     * @deprecated 该方法已过期，请使用 {@see ArrayHelper::getValueOrSelf()}，未来某个版本会删除
     */
    public static function parseVars(array $array, $var = null)
    {
        return ArrayHelper::getValueOrSelf($array, $var);
    }
    
    
    /**
     * @deprecated 该方法已过期，请使用 {@see ClassHelper::getConstAttrs()}，未来某个版本会删除
     */
    public static function parseConst($class, string $prefix, array $annotations = [], $mapping = null) : array
    {
        return ClassHelper::getConstAttrs($class === true ? static::class : $class, $prefix, $annotations, $mapping);
    }
    
    
    /**
     * 构架函数
     * @param LoggerInterface|null $log 日志接口
     * @param string               $connect 连接标识
     * @param bool                 $force 是否强制重连
     */
    public function __construct(LoggerInterface $log = null, string $connect = '', bool $force = false)
    {
        // 当前模型名
        if (empty($this->name)) {
            $this->name = basename(str_replace('\\', '/', static::defineAbstract()));
        }
        
        // 连接标识
        if ($connect) {
            $this->connect = $connect;
        }
        
        // 指定日志接口
        if ($log) {
            $this->logger = $log;
        }
        
        // 自定义日志接口
        if ($this->logger) {
            $this->manager = Container::getInstance()->make('db', [], true);
            $this->manager->setLog($this->logger);
        } else {
            if (!$this->manager) {
                $this->manager = static::$db;
            }
        }
        $this->pk($this->pk);
        
        // 执行服务注入
        if (!empty(static::$maker)) {
            foreach (static::$maker as $maker) {
                call_user_func($maker, $this);
            }
        }
        
        parent::__construct($this->manager->connect($this->connect, $force));
    }
    
    
    /**
     * @inheritDoc
     * @return $this
     */
    public function newQuery() : BaseQuery
    {
        $query = new static($this->getLogger(), $this->getConnect());
        
        if (isset($this->options['table'])) {
            $query->table($this->options['table']);
        } else {
            $query->name($this->name);
        }
        
        if (!empty($this->options['json'])) {
            $query->json($this->options['json'], $this->options['json_assoc']);
        }
        
        if (isset($this->options['field_type'])) {
            $query->setFieldType($this->options['field_type']);
        }
        
        return $query;
    }
    
    
    /**
     * 调用反射执行模型方法 支持参数绑定
     * @access public
     * @param mixed $method
     * @param array $vars 参数
     * @return mixed
     */
    public function invoke($method, array $vars = [])
    {
        if (static::$invoker) {
            $call = static::$invoker;
            
            return $call($method instanceof Closure ? $method : Closure::fromCallable([$this, $method]), $vars);
        }
        
        return call_user_func_array($method instanceof Closure ? $method : [$this, $method], $vars);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getTable(string $name = '')
    {
        if (empty($name) && isset($this->options['table'])) {
            return $this->options['table'];
        }
        
        $name = $name ?: ($this->name . $this->suffix);
        
        return $this->prefix . Str::snake($name);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return StringHelper::snake($this->name);
    }
    
    
    /**
     * 设置当前模型数据表的后缀
     * @param string $suffix 数据表后缀
     * @return $this
     */
    public function setSuffix(string $suffix)
    {
        $this->suffix = $suffix;
        
        return $this;
    }
    
    
    /**
     * 获取当前模型的数据表后缀
     * @return string
     */
    public function getSuffix() : string
    {
        return $this->suffix;
    }
    
    
    /**
     * 获取当前模型的数据库连接标识
     * @return string
     */
    public function getConnect() : string
    {
        return $this->connect;
    }
    
    
    /**
     * 获取日志接口
     * @return LoggerInterface
     */
    public function getLogger() : ?LoggerInterface
    {
        return $this->logger;
    }
    
    
    /**
     * 触发事件回调
     * @param string $event
     */
    protected function triggerEvent(string $event)
    {
        $call = 'on' . Str::studly($event);
        if (method_exists($this, $call)) {
            call_user_func([$this, $call]);
        }
    }
    
    
    public function __call(string $method, array $args)
    {
        if (isset(static::$macro[static::class][$method])) {
            return call_user_func_array(static::$macro[static::class][$method]->bindTo($this, static::class), $args);
        }
        
        $lower = strtolower($method);
        switch (true) {
            // 共享锁
            case $lower == 'lockshare':
                return $this->lock($args[0] === true ? 'LOCK IN SHARE MODE' : false);
            
            // 根据某个字段获取记录的某个值
            case substr($lower, 0, 10) == 'getfieldby':
                $name = Str::snake(substr($method, 10));
                
                return $this->where($name, '=', $args[0])->value($args[1], $args[2] ?? null);
            
            // getInfoByField
            case substr($lower, 0, 9) == 'getinfoby':
                $name = Str::snake(substr($method, 9));
                
                return $this->where($name, '=', $args[0])->failException(true, $args[1] ?? null)->findInfo();
            
            // findInfoByField
            case substr($lower, 0, 10) == 'findinfoby':
                $name = Str::snake(substr($method, 10));
                
                return $this->where($name, '=', $args[0])->findInfo();
            // getExtendInfoByField
            case substr($lower, 0, 15) == 'getextendinfoby':
                $name = Str::snake(substr($method, 15));
                
                return $this->where($name, '=', $args[0])->failException(true, $args[1] ?? null)->findExtendInfo();
            // findExtendInfoByField
            case substr($lower, 0, 16) == 'findextendinfoby':
                $name = Str::snake(substr($method, 16));
                
                return $this->where($name, '=', $args[0])->findExtendInfo();
        }
        
        return parent::__call($method, $args);
    }
    
    
    public static function __callStatic(string $method, array $args)
    {
        if (isset(static::$macro[static::class][$method])) {
            return call_user_func_array(static::$macro[static::class][$method]->bindTo(null, static::class), $args);
        }
        
        throw new MethodNotFoundException(static::class, $method);
    }
    
    
    /**
     * 将数据解析成Field对象
     * @param array $info
     * @return array|Field
     */
    private function toField(array $info)
    {
        return $this->toFieldList([$info])[0];
    }
    
    
    /**
     * 将数据解析成Field对象集合
     * @param array|Collection $list
     * @return array|Field[]
     */
    private function toFieldList($list)
    {
        if ($list instanceof Collection) {
            $list = $list->toArray();
        }
        
        // 自定义解析
        if ($this->useBindParseClass && is_subclass_of($this->useBindParseClass, Field::class)) {
            foreach ($list as $i => $r) {
                $list[$i] = $this->useBindParseClass::parse($r);
            }
            
            $list = $this->execBindParseClassHandle($this->useBindParseClass, $list);
            
            $this->useBindParseClass = null;
            
            return $list;
        }
        
        if ($this->bindParseClass && is_subclass_of($this->bindParseClass, Field::class)) {
            foreach ($list as $i => $r) {
                $list[$i] = $this->bindParseClass::parse($r);
            }
            
            $list = $this->execBindParseClassHandle($this->bindParseClass, $list);
        }
        
        $this->onParseBindList($list);
        
        return $list;
    }
    
    
    /**
     * 将数据解析成包含关联信息的Field对象
     * @param array $info
     * @return array|Field
     */
    private function toExtendField(array $info)
    {
        return $this->toExtendFieldList([$info])[0];
    }
    
    
    /**
     * 将数据解析成Field对象集合
     * @param array|Collection $list
     * @return array|Field[]
     */
    private function toExtendFieldList($list)
    {
        if ($list instanceof Collection) {
            $list = $list->toArray();
        }
        
        if ($this->bindParseExtendClass && is_subclass_of($this->bindParseExtendClass, $this->bindParseClass) && is_subclass_of($this->bindParseExtendClass, Field::class)) {
            foreach ($list as $i => $r) {
                $list[$i] = $this->bindParseExtendClass::parse($r);
            }
            $list = $this->execBindParseClassHandle($this->bindParseExtendClass, $list);
        }
        
        $this->onParseBindExtendList($list);
        
        return $list;
    }
    
    
    /**
     * 执行数据对象解析注入
     * @param class-string<Field> $class
     * @param array               $list
     * @return array
     */
    private function execBindParseClassHandle(string $class, array $list) : array
    {
        // 执行服务注入
        if (!empty(static::$bindParseClassHandle)) {
            foreach (static::$bindParseClassHandle as $handle) {
                $list = call_user_func($handle, $this, $class, $list);
            }
        }
        
        return $list;
    }
    
    
    /**
     * 解析通用信息
     * @param array $list
     */
    protected function onParseBindList(array &$list)
    {
    }
    
    
    /**
     * 解析关联信息
     * @param array $list
     */
    protected function onParseBindExtendList(array &$list)
    {
    }
    
    
    /**
     * 获取单条信息
     * @param mixed $data 主键数据，支持字符、数值、数字索引数组
     * @return array|Field|null
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function findInfo($data = null)
    {
        $info = $this->find($data);
        if (!$info) {
            return null;
        }
        
        return $this->toField($info);
    }
    
    
    /**
     * 获取单条包含关联数据的信息
     * @param mixed $data 主键数据，支持字符、数值、数字索引数组
     * @return array|Field|null
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function findExtendInfo($data = null)
    {
        $info = $this->find($data);
        if (!$info) {
            return null;
        }
        
        return $this->toExtendField($info);
    }
    
    
    /**
     * 强制获取单条信息
     * @param mixed  $data 主键数据，支持字符、数值、数字索引数组
     * @param string $notFoundMessage 数据为空异常消息
     * @return array|Field
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getInfo($data, $notFoundMessage = null)
    {
        return $this->failException(true, $notFoundMessage)->findInfo($data ?? '');
    }
    
    
    /**
     * 强制获取单条包含关联数据的信息
     * @param mixed  $data 主键数据，支持字符、数值、数字索引数组
     * @param string $notFoundMessage 数据为空异常消息
     * @return array|Field
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getExtendInfo($data, $notFoundMessage = null)
    {
        return $this->failException(true, $notFoundMessage)->findExtendInfo($data ?? '');
    }
    
    
    /**
     * 解析 增加/更新/删除 操作中的主键值并触发回调
     * @param string $method
     * @param array  $options
     */
    private function parseOnChanged($method, $options)
    {
        $id = '';
        $pk = $this->getPk();
        
        // 从data中取主键值
        $data = $options['data'] ?? [];
        if (isset($data[$pk]) && $data[$pk]) {
            if (is_string($data[$pk]) || is_numeric($data[$pk])) {
                $id = $data[$pk];
            }
        }
        
        // 从options中取主键值
        if (empty($id)) {
            $where = $options['where'] ?? [];
            $where = is_array($where) ? $where : [];
            $where = $where['AND'] ?? [];
            foreach ($where as $item) {
                // 不是数组
                if (!is_array($item)) {
                    continue;
                }
                
                // 不是主键
                if ($item[0] === $pk || $item[1] === '=') {
                    $id = $item[2];
                    break;
                }
            }
        }
        
        if (empty($id)) {
            return;
        }
        
        // 全部触发
        if (method_exists($this, 'onChanged')) {
            $this->ignoreException(function() use ($method, $id, $options) {
                $this->onChanged($method, $id, $options);
            });
        }
        
        // 写入触发
        if (($method == self::CHANGED_INSERT || $method == self::CHANGED_UPDATE) && method_exists($this, 'onAfterWrite')) {
            $this->ignoreException(function() use ($id, $options) {
                $this->onAfterWrite($id, $options);
            });
        }
        
        switch (true) {
            // 新增触发
            case $method == self::CHANGED_INSERT && method_exists($this, 'onAfterInsert'):
                $this->ignoreException(function() use ($id, $options) {
                    $this->onAfterInsert($id, $options);
                });
            break;
            // 更新触发
            case $method == self::CHANGED_UPDATE && method_exists($this, 'onAfterUpdate'):
                $this->ignoreException(function() use ($id, $options) {
                    $this->onAfterUpdate($id, $options);
                });
            break;
            // 删除触发
            case $method == self::CHANGED_DELETE && method_exists($this, 'onAfterDelete'):
                $this->ignoreException(function() use ($id, $options) {
                    $this->onAfterDelete($id, $options);
                });
            break;
        }
    }
    
    
    /**
     * 忽略异常错误并执行
     * @param callable    $closure 闭包
     * @param string|null $tag 日志标签
     * @return mixed
     */
    protected function ignoreException(callable $closure, string $tag = null)
    {
        try {
            return call_user_func($closure);
        } catch (Throwable $e) {
            try {
                $this->log($e, Log::WARNING, $tag);
            } catch (Throwable $e) {
                // 忽略错误
            }
            
            return null;
        }
    }
    
    
    /**
     * 设置查询数据不存在是否抛出异常
     * @param bool        $fail 是否抛出异常
     * @param string|null $notFoundMessage 错误消息
     * @return $this
     */
    public function failException(bool $fail = true, string $notFoundMessage = null)
    {
        $this->options['fail']         = $fail;
        $this->options['fail_message'] = $notFoundMessage;
        
        return $this;
    }
    
    
    /**
     * 查询失败 抛出异常
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    protected function throwNotFound() : void
    {
        $message  = $this->options['fail_message'] ?? '';
        $bySelect = $this->options['by_select'] ?? false;
        if ($bySelect) {
            $message = $message ?: $this->listNotFoundMessage;
        } else {
            $message = $message ?: $this->dataNotFoundMessage;
        }
        
        if (!empty($this->model)) {
            $class = get_class($this->model);
            throw new ModelNotFoundException($message ?: 'model data Not Found:' . $class, $class, $this->options);
        }
        
        $table = $this->getTable();
        throw new DataNotFoundException($message ?: 'table data not Found:' . $table, $table, $this->options);
    }
    
    
    /**
     * 记录日志
     * @param mixed        $content 日志内容
     * @param string|array $level 日志级别
     * @param string|null  $tag 日志标签
     * @param string|null  $method 所在方法(一般用于记录异常触发所在的方法)
     */
    public function log($content, $level = Log::SQL, string $tag = null, string $method = null)
    {
        $this->manager->log(LogHelper::format($content, $tag, $method), $level);
    }
    
    
    /**
     * 启动事务
     * @access public
     * @param bool   $disabled 是否禁用事物
     * @param string $alias 事物别名，用于记录SQL日志
     * @return void
     */
    public function startTrans(bool $disabled = false, string $alias = '') : void
    {
        if ($disabled) {
            return;
        }
        
        parent::startTrans();
        
        if ($this->getConfig('trigger_sql')) {
            $alias = $alias ? $alias . ' ' : '';
            $this->log("{$alias}startTrans");
        }
    }
    
    
    /**
     * 提交事务
     * @param bool   $disabled 是否禁用事物
     * @param string $alias 事物别名，用于记录SQL日志
     * @return void
     */
    public function commit(bool $disabled = false, string $alias = '') : void
    {
        if ($disabled) {
            return;
        }
        
        parent::commit();
        
        if ($this->getConfig('trigger_sql')) {
            $alias = $alias ? $alias . ' ' : '';
            $this->log("{$alias}commit");
        }
    }
    
    
    /**
     * 事务回滚
     * @param bool   $disabled 是否禁用事物
     * @param string $alias 事物别名，用于记录SQL日志
     */
    public function rollback(bool $disabled = false, string $alias = '') : void
    {
        if ($disabled) {
            return;
        }
        
        parent::rollback();
        
        if ($this->getConfig('trigger_sql')) {
            $alias = $alias ? $alias . ' ' : '';
            $this->log("{$alias}rollback");
        }
    }
    
    
    /**
     * 查找记录
     * @param mixed $data 数据
     * @return Collection
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function select($data = null) : Collection
    {
        try {
            $this->options['by_select'] = true;
            
            return parent::select($data);
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * 查找单条记录
     * @param mixed $data 查询数据
     * @return array|Model|null
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function find($data = null)
    {
        try {
            if (!is_null($data) && $this->findFilter && is_callable($this->findFilter)) {
                $data = call_user_func($this->findFilter, $data);
            }
            
            return parent::find($data);
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * 数据校验
     * @param array|Field|Validate|string $data 要验证的数据或验证器
     * @param string|Validate|Field|null  $validate 验证器、验证器类名、验证场景名称
     * @param mixed                       $sceneName 验证场景或场景数据
     * @param mixed                       $sceneData 场景数据
     * @return $this
     */
    public function validate($data, $validate = null, $sceneName = null, $sceneData = null)
    {
        $field = null;
        if (is_array($data) && $validate) {
            if (is_subclass_of($validate, Field::class)) {
                $field    = $validate;
                $validate = null;
            } elseif (!is_subclass_of($validate, Validate::class) || !$validate instanceof Validate) {
                throw new ClassNotExtendsException($validate, Validate::class);
            }
        } elseif (is_subclass_of($data, Field::class)) {
            $field     = $data;
            $sceneData = $sceneName;
            $sceneName = $validate;
            $validate  = null;
            $data      = [];
        } elseif (is_subclass_of($data, Validate::class) || $data instanceof Validate) {
            $sceneData = $sceneName;
            $sceneName = $validate;
            $validate  = $data;
            $data      = [];
        } else {
            throw new InvalidArgumentException('The $validate cannot be empty');
        }
        $data = array_merge($this->options['data'] ?? [], $data);
        
        // 如果是Field验证，则将data转为字段标准值
        if ($field) {
            // 将data合并到Field中
            if ($field instanceof Field) {
                foreach ($data as $key => $value) {
                    $field[$key] = $value;
                }
            } else {
                $field = $field::parse($data);
            }
            $validate = $field::getValidate();
        } elseif (is_string($validate)) {
            $validate = new $validate();
        }
        
        // 场景验证
        $needCheck = true;
        if ($sceneName !== '' && $sceneName !== null) {
            if ($field) {
                if ($field instanceof ModelSceneValidateInterface) {
                    $result = $field->onModelSceneValidate($this, $validate, $sceneName, $sceneData);
                    if ($result === false) {
                        $needCheck = false;
                    } elseif (is_array($result)) {
                        $field->retain($validate, ...$result);
                    }
                }
            } else {
                $validate->scene($sceneName);
            }
        }
        
        // 执行验证
        if ($needCheck) {
            $checkData = $data;
            if ($field) {
                $checkData = [];
                foreach ($field::getPropertyList() as $property) {
                    if (null !== $value = ($field[$property] ?? null)) {
                        $checkData[$property] = $value;
                    }
                }
            }
            
            $validate->setDb($this->manager);
            $validate->failException(true);
            $validate->check($checkData);
        }
        
        // 设置data
        $this->data($field ?: $data);
        
        return $this;
    }
    
    
    /**
     * 设置数据
     * @param array|Field $data 数据
     * @return $this
     */
    public function data($data)
    {
        return parent::data($this->parseData($data));
    }
    
    
    /**
     * 保存记录 自动判断insert或者update
     * @param array|Field $data 数据
     * @param bool        $forceInsert 是否强制insert
     * @return int|string
     */
    public function save($data = [], bool $forceInsert = false)
    {
        return parent::save($this->parseData($data), $forceInsert);
    }
    
    
    /**
     * 更新记录
     * @param array|Field $data 更新的数据
     * @return int
     * @throws DbException
     */
    public function update($data = []) : int
    {
        try {
            $this->triggerEvent('BeforeUpdate');
            $data = array_merge($this->options['data'] ?? [], $this->parseData($data));
            
            // 自动写入更新时间
            if ($data && $this->autoWriteTimestamp && $this->updateTime) {
                $data[$this->updateTime] = $this->autoWriteTimestamp();
            }
            
            $this->options['data'] = $data;
            
            $result  = parent::update();
            $options = $this->options;
        } finally {
            $this->removeOption();
        }
        
        $this->parseOnChanged(self::CHANGED_UPDATE, $options);
        
        return $result;
    }
    
    
    /**
     * 插入记录
     * @param array|Field $data 数据
     * @param boolean     $getLastInsID 返回自增主键
     * @return int|string
     * @throws DbException
     */
    public function insert($data = [], bool $getLastInsID = true)
    {
        try {
            $this->triggerEvent('BeforeInsert');
            $data = array_merge($this->options['data'] ?? [], $this->parseData($data));
            
            // 自动写入增加时间
            if ($data && $this->autoWriteTimestamp && $this->createTime) {
                $data[$this->createTime] = $this->autoWriteTimestamp();
            }
            
            $this->options['data'] = $data;
            
            $result  = parent::insert([], $getLastInsID);
            $options = $this->options;
        } finally {
            $this->removeOption();
        }
        
        $this->parseOnChanged(self::CHANGED_INSERT, $options);
        
        return $result;
    }
    
    
    /**
     * 插入记录并获取自增ID
     * @param array|Field $data 数据
     * @return int|string
     * @throws DbException
     */
    public function insertGetId($data)
    {
        return $this->insert($data, true);
    }
    
    
    /**
     * 批量插入记录
     * @param array|Field[] $dataSet 数据集
     * @param integer       $limit 每次写入数据限制
     * @return int
     * @throws DbException
     */
    public function insertAll(array $dataSet = [], int $limit = 0) : int
    {
        try {
            foreach ($dataSet as $index => $item) {
                $dataSet[$index] = $this->parseData($item);
            }
            
            $result = parent::insertAll($dataSet, $limit);
            
            // 触发回调
            if (method_exists($this, 'onInsertAll')) {
                $this->ignoreException(function() {
                    $this->onInsertAll();
                });
            }
            
            //
            // 兼容onAddAll
            elseif (method_exists($this, 'onAddAll')) {
                $this->ignoreException(function() {
                    $this->onAddAll();
                });
            }
            
            return $result;
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * 删除记录
     * @param mixed $data 表达式 true 表示强制删除
     * @return int
     * @throws DbException
     */
    public function delete($data = null) : int
    {
        try {
            $this->triggerEvent('BeforeDelete');
            $result  = parent::delete($data);
            $options = $this->options;
        } finally {
            $this->removeOption();
        }
        
        $this->parseOnChanged(self::CHANGED_DELETE, $options);
        
        return $result;
    }
    
    
    /**
     * 通过Select方式插入记录
     * @param array|Field[] $fields 要插入的数据表字段名
     * @param string        $table 要插入的数据表名
     * @return int
     * @throws DbException
     */
    public function selectInsert(array $fields, string $table) : int
    {
        try {
            return parent::selectInsert(Entity::parse($fields), $table);
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * 生成查询语句
     * @param bool $sub
     * @return string
     * @throws DbException
     */
    public function buildSql(bool $sub = true) : string
    {
        try {
            return parent::buildSql($sub);
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * 得到某个字段的值
     * @param string|Field $field 字段名
     * @param mixed        $default 默认值
     * @return mixed
     * @throws DbException
     */
    public function value($field, $default = null)
    {
        try {
            return parent::value(Entity::parse($field), $default);
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * @throws DbException
     * @deprecated 该方法已过期，请使用{@see Model::value()}，未来某个版本会删除
     */
    public function val($field, $default = null)
    {
        return $this->value($field, $default);
    }
    
    
    /**
     * 得到某个列的数组
     * @param string $field 字段名 多个字段用逗号分隔
     * @param string $key 索引
     * @return array
     * @throws DbException
     */
    public function column($field, string $key = '') : array
    {
        try {
            return parent::column(Entity::parse($field), $key);
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * 聚合查询
     * @param string     $aggregate 聚合方法
     * @param string|Raw $field 字段名
     * @param bool       $force 强制转为数字类型
     * @return mixed
     * @throws DbException
     */
    protected function aggregate(string $aggregate, $field, bool $force = false)
    {
        try {
            return parent::aggregate($aggregate, Entity::parse($field), $force);
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * @throws DbException
     * @deprecated 该方法已过期，请使用 {@see Model::insert()}
     */
    public function addData($data = [], bool $replace = false)
    {
        if ($replace) {
            $this->replace();
        }
        
        return $this->insert($data, true);
    }
    
    
    /**
     * @throws DbException
     * @deprecated 该方法已过期，请使用 {@see Model::insertAll()}
     */
    public function addAll(array $data = [], bool $replace = false) : int
    {
        if ($replace) {
            $this->replace();
        }
        
        return $this->insertAll($data);
    }
    
    
    /**
     * @throws DbException
     * @deprecated 该方法已过期，请使用 {@see Model::update()}
     */
    public function saveData($data = []) : int
    {
        return $this->update($data);
    }
    
    
    /**
     * 批量更新数据
     * @param array  $data 更新的数据<pre>
     * $this->updateAll([
     *     [
     *         'id'     => 1,       // 主键必选
     *         'name'   => 'test',  // 要更新的字段1
     *         'name2'  => 'test2'  // 要更新的字段2
     *     ]
     * ]);
     * </pre>
     * @param string $pk 依据$data中的哪个字段进行查询更新 如: id
     * @return int
     * @throws DbException
     */
    public function updateAll(array $data, string $pk = '') : int
    {
        $pk   = $pk ?: $this->getPk();
        $list = [];
        $idIn = [];
        foreach ($data as $values) {
            $values = $this->parseData($values);
            $idIn[] = sprintf("'%s'", $values[$pk]);
            foreach ($values as $i => $value) {
                if ($value instanceof Raw) {
                    $value = $value->getValue();
                }
                
                $list[$i][$values[$pk]] = $value;
            }
        }
        
        if (!$idIn) {
            throw new InvalidArgumentException('Primary key field must be set');
        }
        
        
        $item = [];
        foreach ($list as $key => $values) {
            $item[$key] = sprintf("%s = CASE %s ", $key, $pk) . PHP_EOL;
            foreach ($values as $i => $value) {
                if (is_array($value) && $value[0] == 'exp') {
                    $value = $value[1];
                } else {
                    $value = "'$value'";
                }
                
                $item[$key] .= sprintf("WHEN '%s' THEN %s ", $i, $value) . PHP_EOL;
            }
            $item[$key] .= ' END ' . PHP_EOL;
        }
        
        $result = $this->execute(sprintf('UPDATE %s SET %s WHERE %s in (%s)', $this->getTable(), implode(',', $item), $pk, implode(',', $idIn)));
        
        // 触发回调
        if (method_exists($this, 'onUpdateAll')) {
            $this->ignoreException(function() {
                $this->onUpdateAll();
            });
        }
        
        //
        // 兼容onSaveAll
        elseif (method_exists($this, 'onSaveAll')) {
            $this->ignoreException(function() {
                $this->onSaveAll();
            });
        }
        
        return $result;
    }
    
    
    /**
     * @throws DbException
     * @deprecated 该方法已过期，请使用 {@see Model::updateAll()}
     */
    public function saveAll(array $data, string $pk = '') : int
    {
        return $this->updateAll($data, $pk);
    }
    
    
    /**
     * 执行语句
     * @param string $sql sql指令
     * @param array  $bind 参数绑定
     * @return int
     * @throws DbException
     */
    public function execute(string $sql, array $bind = []) : int
    {
        return $this->connection->execute($sql, $bind);
    }
    
    
    /**
     * 执行查询
     * @param string $sql sql指令
     * @param array  $bind 参数绑定
     * @param bool   $master 主库读取
     * @return array
     * @throws DbException
     */
    public function query(string $sql, array $bind = [], bool $master = false) : array
    {
        return $this->connection->query($sql, $bind, $master);
    }
    
    
    /**
     * 删除信息
     * @param mixed $data 表达式 true 表示强制删除
     * <p><b>$this->remove(1)</b> 通过主键删除</p>
     * <p><b>$this->remove([1,2,3])</b> 通过主键批量删除</p>
     * <p><b>$this->remove('1,2,3')</b> 通过主键批量删除</p>
     * @return int 返回删除的记录数
     * @throws DbException
     */
    public function remove($data) : int
    {
        if (is_string($data) && false !== strpos($data, ',')) {
            $data = explode(',', $data);
        }
        
        $filter = function($data) {
            if (is_null($data)) {
                return null;
            }
            
            if (!$this->removeFilter) {
                return $data;
            }
            
            if (is_callable($this->removeFilter)) {
                return call_user_func_array($this->removeFilter, [$data]);
            } elseif (is_string($this->removeFilter) && function_exists($this->removeFilter)) {
                return call_user_func_array($this->removeFilter, [$data]);
            }
            
            return $data;
        };
        
        // 去重，去空
        if (is_array($data)) {
            $data = array_map($filter, $data);
            $data = array_filter($data);
            $data = array_unique($data);
            $data = array_values($data);
        } else {
            $data = $filter($data);
        }
        
        return $this->delete($data);
    }
    
    
    /**
     * 删除信息
     * @throws DbException
     * @deprecated 未来某个版本将删除，请使用 {@see Model::remove()}
     */
    public function deleteInfo($data) : int
    {
        $this->log('deleteInfo 方法已过期，未来某个版本会删除，请使用 remove 方法', Log::WARNING, null, __METHOD__);
        
        return $this->remove($data);
    }
    
    
    /**
     * 字段值增长
     * @param string|Entity $field 字段名
     * @param float|int     $step 增长值
     * @return $this
     */
    public function inc($field, $step = 1)
    {
        if ($field instanceof Entity) {
            $value = $field->value();
            if (is_numeric($value) && $value > 1) {
                $step = $value;
            }
        }
        
        return parent::inc(Entity::parse($field), $step);
    }
    
    
    /**
     * 字段值减少
     * @param string|Entity $field 字段名
     * @param float|int     $step 减少值
     * @return $this
     */
    public function dec($field, $step = 1)
    {
        if ($field instanceof Entity) {
            $value = $field->value();
            if (is_numeric($value) && $value > 1) {
                $step = $value;
            }
        }
        
        return parent::dec(Entity::parse($field), $step);
    }
    
    
    /**
     * @throws DbException
     * @deprecated 请使用 {@see Model::inc()}，未来某个版本会删除
     */
    public function setInc($field, $step = 1) : int
    {
        return $this->inc($field, $step)->update();
    }
    
    
    /**
     * @throws DbException
     * @deprecated 请使用 {@see Model::dec()}，未来某个版本会删除
     */
    public function setDec($field, $step = 1) : int
    {
        return $this->dec($field, $step)->update();
    }
    
    
    /**
     * 设置某个字段的值
     * @param string|Entity $field 字段名
     * @param mixed         $value 字段值
     * @return int
     * @throws DbException
     */
    public function setField($field, $value) : int
    {
        $this->data([]);
        
        return $this->update([
            Entity::parse($field) => $value
        ]);
    }
    
    
    /**
     * 查询解析后的数据
     * @return array|Field[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function selectList() : array
    {
        return $this->toFieldList($this->select());
    }
    
    
    /**
     * 查询自定义解析类解析后的数据
     * @template U
     * @param class-string<U> $parse 解析器
     * @return array<U>
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function selectParse(string $parse) : array
    {
        $this->useBindParseClass = $parse;
        
        return $this->toFieldList($this->select());
    }
    
    
    /**
     * 查询包含关联信息的Field对象集合
     * @return array|Field[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function selectExtendList() : array
    {
        return $this->toExtendFieldList($this->select());
    }
    
    
    /**
     * 分批查询解析数据
     * @param int                                $count 每一批查询多少条
     * @param callable(Field[], Collection):bool $callback 处理回调方法，接受2个参数，$list 和 $result，返回false代表阻止继续执行
     * @param string|array|Entity|Entity[]       $column 排序依据字段，默认是主键字段
     * @param string                             $order 排序方式
     * @return bool 处理回调方法是否全部处理成功
     * @throws DbException
     */
    public function chunkList(int $count, callable $callback, $column = null, string $order = 'asc') : bool
    {
        return parent::chunk($count, function(Collection $result) use ($callback) {
            return call_user_func($callback, $this->toFieldList($result), $result);
        }, Entity::parse($column), $order);
    }
    
    
    /**
     * 分批查询扩展数据
     * @param int                                $count 每一批查询多少条
     * @param callable(Field[], Collection):bool $callback 处理回调方法，接受2个参数，$list 和 $result，返回false代表阻止继续执行
     * @param string|array|Entity|Entity[]       $column 排序依据字段，默认是主键字段
     * @param string                             $order 排序方式
     * @return bool 处理回调方法是否全部处理成功
     * @throws DbException
     */
    public function chunkExtendList(int $count, callable $callback, $column = null, string $order = 'asc') : bool
    {
        return parent::chunk($count, function(Collection $result) use ($callback) {
            return call_user_func($callback, $this->toExtendFieldList($result), $result);
        }, Entity::parse($column), $order);
    }
    
    
    /**
     * 查询列表并用字段构建键
     * @param array         $values in查询的值
     * @param string|Entity $key 查询的字段，默认id
     * @param string|Entity $field 构建的字段，默认id
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function buildListWithField(array $values, $key = null, $field = null) : array
    {
        $key    = Entity::parse($key ?: 'id');
        $field  = Entity::parse($field ?: 'id');
        $values = FilterHelper::trimArray($values);
        
        return ArrayHelper::listByKey($this->where($key, 'in', $values)->selectList(), $field);
    }
    
    
    /**
     * 查询扩展列表并用字段构建键
     * @param array         $values in查询的值
     * @param string|Entity $key 查询的字段，默认id
     * @param string|Entity $field 构建的字段，默认id
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function buildExtendListWithField(array $values, $key = null, $field = null) : array
    {
        $key    = Entity::parse($key ?: 'id');
        $field  = Entity::parse($field ?: 'id');
        $values = FilterHelper::trimArray($values);
        
        return ArrayHelper::listByKey($this->where($key, 'in', $values)->selectExtendList(), $field);
    }
    
    
    /**
     * @deprecated 请使用 {@see Model::getAlias()}，未来某个版本会删除
     */
    public function getJoinAlias() : string
    {
        return $this->getAlias();
    }
    
    
    /**
     * 获取Join别名
     * @return string
     */
    public function getAlias() : string
    {
        $alias = $this->options['alias'] ?? '';
        $alias = $alias ?: $this->joinAlias;
        
        return $alias ?: $this->getName();
    }
    
    
    /**
     * 解析增加修改的数据
     * @param array|Field $data
     * @return array
     */
    protected function parseData($data = []) : array
    {
        if ($data instanceof Field) {
            $data = $data->obtain();
        }
        
        $fields = $this->getTableFields();
        $list   = [];
        
        // 支持 exp 写法 及 过滤字段
        foreach ($data as $key => $value) {
            if (!in_array($key, $fields) || is_null($value)) {
                continue;
            }
            
            if ($value instanceof Entity) {
                $value = new Raw($value->build() . $value->op() . $value->value());
            } elseif (is_array($value) && count($value) == 2 && is_string($value[0]) && strtolower($value[0]) === 'exp') {
                $value = new Raw($value[1]);
            } elseif (is_bool($value)) {
                $value = $value ? 1 : 0;
            }
            
            $list[$key] = $value;
        }
        
        return $list;
    }
    
    
    /**
     * @deprecated 该方法已过期，请使用{@see Model::where()}，未来某个版本会删除
     */
    public function whereEntity(...$entity)
    {
        foreach ($entity as $item) {
            if ($item instanceof Entity) {
                $value = $item->value();
                if ($value instanceof Entity) {
                    $this->whereRaw(sprintf('`%s` %s `%s`', $item->build(), $item->op(), $value->build()));
                } else {
                    $this->where($item->build(), $item->op(), $item->value());
                }
            }
        }
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    protected function parseWhereExp(string $logic, $field, $op, $condition, array $param = [], bool $strict = false)
    {
        if ($field instanceof Entity) {
            $value = $field->value();
            if ($value instanceof Entity) {
                return $this->whereRaw(sprintf('`%s` %s `%s`', $field->build(), $field->op(), $value->build()));
            }
            
            $condition = $condition ?: $field->value();
            $op        = $op ?: $field->op();
            $field     = $field->build();
        }
        
        return parent::parseWhereExp($logic, Entity::parse($field), $op, $condition, $param, $strict);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereNull($field, string $logic = 'AND')
    {
        return parent::whereNull(Entity::parse($field), $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereNotNull($field, string $logic = 'AND')
    {
        return parent::whereNotNull(Entity::parse($field), $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereIn($field, $condition, string $logic = 'AND')
    {
        return parent::whereIn(Entity::parse($field), $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereNotIn($field, $condition, string $logic = 'AND')
    {
        return parent::whereNotIn(Entity::parse($field), $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereLike($field, $condition, string $logic = 'AND')
    {
        return parent::whereLike(Entity::parse($field), $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereNotLike($field, $condition, string $logic = 'AND')
    {
        return parent::whereNotLike(Entity::parse($field), $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereBetween($field, $condition, string $logic = 'AND')
    {
        return parent::whereBetween(Entity::parse($field), $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereNotBetween($field, $condition, string $logic = 'AND')
    {
        return parent::whereNotBetween(Entity::parse($field), $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereFindInSet($field, $condition, string $logic = 'AND')
    {
        return parent::whereFindInSet(Entity::parse($field), $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereColumn($field1, string $operator, $field2 = null, string $logic = 'AND')
    {
        return parent::whereColumn(Entity::parse($field1), $operator, Entity::parse($field2), $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function useSoftDelete($field, $condition = null)
    {
        return parent::useSoftDelete(Entity::parse($field), $condition);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereExp($field, string $where, array $bind = [], string $logic = 'AND')
    {
        return parent::whereExp(Entity::parse($field), $where, $bind, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereFieldRaw($field, $op, $condition = null, string $logic = 'AND')
    {
        return parent::whereFieldRaw(Entity::parse($field), $op, $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function removeWhereField($field, string $logic = 'AND')
    {
        return parent::removeWhereField(Entity::parse($field), $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function join($join, string $condition = null, string $type = 'INNER', array $bind = [])
    {
        if ($join instanceof Model) {
            $model = $join;
            $join  = [$model->getTable() => $model->getAlias()];
            $model->removeOption();
        } elseif (is_string($join) && is_subclass_of($join, Model::class)) {
            /** @var Model $model */
            $model = call_user_func([$join, 'init']);
            $join  = [$model->getTable() => $model->getAlias()];
            $model->removeOption();
        }
        
        return parent::join($join, $condition, $type, $bind);
    }
    
    
    /**
     * @inheritDoc
     */
    public function order($field, string $order = '')
    {
        return parent::order(Entity::parse($field), $order);
    }
    
    
    /**
     * @inheritDoc
     */
    public function field($field)
    {
        return parent::field(Entity::parse($field));
    }
    
    
    /**
     * @inheritDoc
     */
    public function group($group)
    {
        return parent::group(Entity::parse($group));
    }
    
    
    /**
     * 指定having查询
     * @param string|Entity $having having
     */
    public function having($having)
    {
        return parent::having(Entity::parse($having));
    }
    
    
    /**
     * 执行查询但只返回PDOStatement对象
     * @return PDOStatement
     */
    public function getPdo() : PDOStatement
    {
        try {
            $this->parseOptions();
            
            return parent::getPdo();
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * 时间戳范围条件
     * @param string|Entity $field 字段
     * @param string|int    $startOrTimeRange 开始时间或时间范围
     * @param string|int    $endOrSpace 结束时间或时间范围分隔符
     * @param bool          $split 是否分割传入的时间范围
     * @return $this
     */
    public function whereTimeIntervalRange($field, $startOrTimeRange = 0, $endOrSpace = 0, bool $split = false)
    {
        $field = Entity::parse($field);
        if ($split && $endOrSpace) {
            [$start, $end] = explode($endOrSpace, $startOrTimeRange);
            $start = (int) strtotime($start);
            $end   = (int) strtotime($end);
        } else {
            $start = (int) (!is_numeric($startOrTimeRange) ? strtotime($startOrTimeRange) : $startOrTimeRange);
            $end   = (int) (!is_numeric($endOrSpace) ? strtotime($endOrSpace) : $endOrSpace);
        }
        
        if ($start > 0 && $end > 0) {
            if ($end >= $start) {
                $this->whereBetweenTime($field, $start, $end);
            } else {
                $this->whereBetweenTime($field, $end, $start);
            }
        } elseif ($start > 0) {
            $this->where($field, '>=', $start);
        } elseif ($end > 0) {
            $this->where($field, '<=', $end);
        }
        
        return $this;
    }
    
    
    /**
     * 优化数据表
     * @throws DbException
     */
    public function optimize()
    {
        $this->execute("OPTIMIZE TABLE `{$this->getTable()}`");
    }
    
    
    /**
     * 执行数据库事务
     * @param callable(Model):mixed $callback 数据操作方法回调
     * @param bool                  $disabled 是否禁用事务
     * @param string                $alias 事务日志别名
     * @return mixed
     * @throws Throwable
     */
    public function transaction(callable $callback, bool $disabled = false, string $alias = '')
    {
        $this->startTrans($disabled, $alias);
        try {
            $result = call_user_func_array($callback, [$this]);
            
            $this->commit($disabled, $alias);
            
            return $result;
        } catch (Throwable $e) {
            $this->rollback($disabled, $alias);
            
            throw $e;
        }
    }
}