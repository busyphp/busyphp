<?php
declare (strict_types = 1);

namespace BusyPHP;

use ArrayAccess;
use BusyPHP\helper\CacheHelper;
use BusyPHP\helper\LogHelper;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\FilterHelper;
use Closure;
use DateInterval;
use DateTimeInterface;
use Exception;
use JsonSerializable;
use PDOStatement;
use ReflectionClass;
use ReflectionException;
use think\Collection;
use think\Container;
use think\contract\Arrayable;
use think\contract\Jsonable;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\InvalidArgumentException;
use think\db\Query;
use think\db\Raw;
use think\DbManager;
use think\helper\Str;
use think\model\concern\Attribute;
use think\model\concern\Conversion;
use think\model\concern\ModelEvent;
use think\model\concern\TimeStamp;
use Throwable;

/**
 * 数据模型基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/30 下午9:22 上午 Model.php $
 * @method mixed onBeforeInsert() 新增前回调, 返回false阻止新增
 * @method mixed onBeforeUpdate() 更新前回调, 返回false阻止更新
 * @method mixed onBeforeDelete() 删除前回调, 返回false阻止更新
 * @method void onChanged(string $method, mixed $id, array $options) 新增/更新/删除后回调
 * @method void onSaveAll() 批量更新回调
 * @method void onAddAll() 批量更新回调
 * @method void onAfterWrite($id, array $options) 新增/更新完成后回调
 * @method void onAfterInsert($id, array $options) 新增完成后回调
 * @method void onAfterUpdate($id, array $options) 更新完成后回调
 * @method void onAfterDelete($id, array $options) 删除完成后回调
 * @method $this lockShare(boolean $isLock) 是否加共享锁，允许其它对象读不允许写
 * @template T
 */
abstract class Model extends Query implements JsonSerializable, ArrayAccess, Arrayable, Jsonable
{
    use Attribute;
    use ModelEvent;
    use TimeStamp;
    use Conversion;
    
    /**
     * findInfo方法参数过滤器
     * @var string|callable|Closure
     */
    protected $findInfoFilter = 'trim';
    
    /**
     * deleteInfo方法参数过滤器
     * @var string|callable|Closure
     */
    protected $deleteInfoFilter = 'trim';
    
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
     * join查询别名
     * @var string
     */
    protected $joinAlias;
    
    /**
     * 当前数据表主键
     * @var string|array
     */
    protected $pk = 'id';
    
    /**
     * 数据表名称
     * @var string
     */
    protected $table;
    
    /**
     * 数据表后缀
     * @var string
     */
    protected $suffix;
    
    /**
     * 当前模型的数据库连接标识
     * @var string
     */
    protected $configName;
    
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
     * 回调方法
     * @var Closure[]
     */
    private $callback = [];
    
    //+--------------------------------------
    //| 回调常量
    //+--------------------------------------
    /** 操作前回调，一般用于创建/更新/删除前 */
    const CALLBACK_BEFORE = 'before';
    
    /** 操作后回调，一般用于创建/更新/删除后 */
    const CALLBACK_AFTER = 'after';
    
    /** 操作失败回调，一般用于失败的情况下 */
    const CALLBACK_ERROR = 'error';
    
    /** 操作完成回调，一般用于成功/失败的情况下 */
    const CALLBACK_COMPLETE = 'complete';
    
    /** 处理过程中回调，一般用于业务逻辑中 */
    const CALLBACK_PROCESS = 'process';
    
    //+--------------------------------------
    //| 数据库回调常量
    //+--------------------------------------
    /** @var string 新增完成事件 */
    const CHANGED_INSERT = 'insert';
    
    /** @var string 更新完成事件 */
    const CHANGED_UPDATE = 'update';
    
    /** @var string 删除完成事件 */
    const CHANGED_DELETE = 'delete';
    
    
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
        self::$db = $db;
    }
    
    
    /**
     * 设置容器对象的依赖注入方法
     * @access public
     * @param callable $callable 依赖注入方法
     * @return void
     */
    public static function setInvoker(callable $callable) : void
    {
        self::$invoker = $callable;
    }
    
    
    /**
     * 切换后缀进行查询
     * @param string $suffix 切换的表后缀
     * @return $this
     */
    public static function suffix(string $suffix)
    {
        $model = new static();
        $model->setSuffix($suffix);
        
        return $model;
    }
    
    
    /**
     * 切换数据库连接进行查询
     * @param string $name 数据库连接标识
     * @return $this
     */
    public static function connect(string $name)
    {
        $model = new static();
        $model->setConfigName($name);
        
        return $model;
    }
    
    
    /**
     * 快速实例化
     * @return $this
     */
    public static function init()
    {
        return new static();
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
        if (self::$invoker) {
            $call = self::$invoker;
            
            return $call($method instanceof Closure ? $method : Closure::fromCallable([$this, $method]), $vars);
        }
        
        return call_user_func_array($method instanceof Closure ? $method : [$this, $method], $vars);
    }
    
    
    /**
     * 架构函数
     */
    public function __construct()
    {
        // 当前模型名
        if (empty($this->name)) {
            $name       = str_replace('\\', '/', static::class);
            $this->name = basename($name);
        }
        
        // 设置表名称
        $this->name($this->name . $this->suffix);
        $this->pk($this->pk);
        
        // 设置表名称
        if (!empty($this->table)) {
            $this->table($this->table . $this->suffix);
        }
        
        // 执行服务注入
        if (!empty(static::$maker)) {
            foreach (static::$maker as $maker) {
                call_user_func($maker, $this);
            }
        }
        
        // 初始化父类
        parent::__construct(self::$db->connect($this->configName));
    }
    
    
    /**
     * 获取数据表名称，不包含表前缀
     * @return string
     */
    public function getTableWithoutPrefix() : string
    {
        return Str::snake($this->name);
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
        return $this->suffix ?: '';
    }
    
    
    /**
     * 获取当前模型的数据库连接标识
     * @return string
     */
    public function getConfigName() : string
    {
        return $this->configName;
    }
    
    
    /**
     * 设置当前模型的数据库连接标识名称
     * @param string $configName 数据表连接标识
     * @return $this
     */
    public function setConfigName(string $configName) : Model
    {
        $this->configName = $configName;
        
        return $this;
    }
    
    
    /**
     * 设置操作前回调方法
     * @param mixed   $callType 回调类型
     * @param Closure $callback 回调方法，具体参数由子类定义
     * @return $this
     */
    public function setCallback($callType, Closure $callback) : Model
    {
        $this->callback[$callType] = $callback;
        
        return $this;
    }
    
    
    /**
     * 触发回调方法
     * @param mixed $callType 回调类型
     * @param mixed ...$args 回调方法参数
     * @return mixed
     */
    protected function triggerCallback($callType, ...$args)
    {
        if (isset($this->callback[$callType])) {
            return Container::getInstance()->invokeFunction($this->callback[$callType], $args);
        }
        
        return null;
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
    
    
    /**
     * 获取静态缓存
     * @param string $name 缓存名称
     * @return mixed
     */
    public function getCache($name)
    {
        return CacheHelper::get(static::class, $name);
    }
    
    
    /**
     * 设置静态缓存
     * @param string                                  $name 缓存名称
     * @param mixed                                   $value 缓存内容
     * @param int|DateTimeInterface|DateInterval|null $expire 有效时间（秒）
     * @return bool
     */
    public function setCache(string $name, $value, $expire = 600) : bool
    {
        return CacheHelper::set(static::class, $name, $value, $expire);
    }
    
    
    /**
     * 移除静态缓存
     * @param string $name 缓存名称
     * @return bool
     */
    public function deleteCache(string $name = '') : bool
    {
        return CacheHelper::delete(static::class, $name);
    }
    
    
    /**
     * 清理静态缓存
     */
    public function clearCache() : bool
    {
        return CacheHelper::clear(static::class);
    }
    
    
    public function __call(string $method, array $args)
    {
        $lowerMethod = strtolower($method);
        if ($lowerMethod == 'lockshare') {
            return $this->lock($args[0] === true ? 'LOCK IN SHARE MODE' : false);
        }
        
        if (isset(static::$macro[static::class][$method])) {
            return call_user_func_array(static::$macro[static::class][$method]->bindTo($this, static::class), $args);
        }
        
        return parent::__call($method, $args);
    }
    
    
    public static function __callStatic(string $method, array $args)
    {
        if (isset(static::$macro[static::class][$method])) {
            return call_user_func_array(static::$macro[static::class][$method]->bindTo(null, static::class), $args);
        }
        
        throw new DbException('method not exist:' . static::class . '::' . $method);
    }
    
    
    /**
     * 修改器 设置数据对象的值
     * @param string $name 名称
     * @param mixed  $value 值
     * @return void
     */
    public function __set(string $name, $value) : void
    {
        $this->setAttr($name, $value);
    }
    
    
    /**
     * 获取器 获取数据对象的值
     * @param string $name 名称
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->getAttr($name);
    }
    
    
    /**
     * 检测数据对象的值
     * @param string $name 名称
     * @return bool
     */
    public function __isset(string $name) : bool
    {
        return !is_null($this->getAttr($name));
    }
    
    
    /**
     * 销毁数据对象的值
     * @param string $name 名称
     * @return void
     */
    public function __unset(string $name) : void
    {
        unset($this->data[$name], $this->relation[$name]);
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetSet($name, $value)
    {
        $this->setAttr($name, $value);
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetExists($name) : bool
    {
        return $this->__isset($name);
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetUnset($name)
    {
        $this->__unset($name);
    }
    
    
    #[\ReturnTypeWillChange]
    public function offsetGet($name)
    {
        return $this->getAttr($name);
    }
    
    
    /**
     * 过滤findInfo参数
     * @param $data
     * @return mixed
     */
    private function filterFindInfoData($data)
    {
        if (is_null($data)) {
            return null;
        }
        
        if (!$this->findInfoFilter) {
            return $data;
        }
        
        if (is_callable($this->findInfoFilter)) {
            return call_user_func_array($this->findInfoFilter, [$data]);
        } elseif (is_string($this->findInfoFilter) && function_exists($this->findInfoFilter)) {
            return call_user_func_array($this->findInfoFilter, [$data]);
        } else {
            return $data;
        }
    }
    
    
    /**
     * 设置信息解析类
     * @param string $class
     * @return $this
     */
    public function parse(string $class) : self
    {
        $this->useBindParseClass = $class;
        
        return $this;
    }
    
    
    /**
     * 将数据解析成Field对象
     * @param array $info
     * @return array|Field
     */
    public function toField(array $info)
    {
        return $this->toFieldList([$info])[0];
    }
    
    
    /**
     * 将数据解析成Field对象集合
     * @param array|Collection $list
     * @return array|Field[]
     */
    public function toFieldList($list)
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
    public function toExtendField(array $info)
    {
        return $this->toExtendFieldList([$info])[0];
    }
    
    
    /**
     * 将数据解析成Field对象集合
     * @param array|Collection $list
     * @return array|Field[]
     */
    public function toExtendFieldList($list)
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
     * @param $list
     */
    protected function onParseBindList(array &$list)
    {
    }
    
    
    /**
     * 解析关联信息
     * @param $list
     */
    protected function onParseBindExtendList(array &$list)
    {
    }
    
    
    /**
     * 获取单条信息
     * @param mixed  $data 主键数据，支持字符、数值、数字索引数组
     * @param string $notFoundMessage 数据为空异常消息
     * @return array|Field|null
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function findInfo($data = null, $notFoundMessage = null)
    {
        try {
            $info = $this->find($this->filterFindInfoData($data));
            if (!$info) {
                return null;
            }
        } catch (DataNotFoundException $e) {
            $notFoundMessage = $notFoundMessage ?: $this->dataNotFoundMessage;
            if ($notFoundMessage) {
                throw new DataNotFoundException($notFoundMessage, $e->getTable(), $e->getData()['Database Config']);
            }
            
            throw $e;
        }
        
        return $this->toField($info);
    }
    
    
    /**
     * 获取单条包含关联数据的信息
     * @param mixed  $data 主键数据，支持字符、数值、数字索引数组
     * @param string $notFoundMessage 数据为空异常消息
     * @return array|Field|null
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function findExtendInfo($data = null, $notFoundMessage = null)
    {
        try {
            $info = $this->find($this->filterFindInfoData($data));
            if (!$info) {
                return null;
            }
        } catch (DataNotFoundException $e) {
            $notFoundMessage = $notFoundMessage ?: $this->dataNotFoundMessage;
            if ($notFoundMessage) {
                throw new DataNotFoundException($notFoundMessage, $e->getTable(), $e->getData()['Database Config']);
            }
            
            throw $e;
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
        return $this->failException(true)->findInfo($data ?? '', $notFoundMessage);
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
        return $this->failException(true)->findExtendInfo($data ?? '', $notFoundMessage);
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
            $this->catchException(function() use ($method, $id, $options) {
                $this->onChanged($method, $id, $options);
            }, false, static::class . '::onChanged', 'error');
        }
        
        // 写入触发
        if (($method == self::CHANGED_INSERT || $method == self::CHANGED_UPDATE) && method_exists($this, 'onAfterWrite')) {
            $this->catchException(function() use ($id, $options) {
                $this->onAfterWrite($id, $options);
            }, false, static::class . '::onAfterWrite', 'error');
        }
        
        // 新增触发
        if ($method == self::CHANGED_INSERT && method_exists($this, 'onAfterInsert')) {
            $this->catchException(function() use ($id, $options) {
                $this->onAfterInsert($id, $options);
            }, false, static::class . '::onAfterInsert', 'error');
        }
        
        // 更新触发
        if ($method == self::CHANGED_UPDATE && method_exists($this, 'onAfterUpdate')) {
            $this->catchException(function() use ($id, $options) {
                $this->onAfterUpdate($id, $options);
            }, false, static::class . '::onAfterUpdate', 'error');
        }
        
        // 删除触发
        if ($method == self::CHANGED_DELETE && method_exists($this, 'onAfterDelete')) {
            $this->catchException(function() use ($id, $options) {
                $this->onAfterDelete($id, $options);
            }, false, static::class . '::onAfterDelete', 'error');
        }
    }
    
    
    /**
     * 捕获错误并执行
     * @param Closure $closure 闭包
     * @param mixed   $errorReturn 错误返回值
     * @param string  $errorPrefix 系统异常消息前缀
     * @param string  $type 记录类型
     * @return mixed
     */
    protected function catchException(Closure $closure, $errorReturn = false, string $errorPrefix = '', string $type = 'sql')
    {
        try {
            return call_user_func($closure);
        } catch (Exception | Throwable $e) {
            if ($type == 'sql') {
                if ($this->getConfig('trigger_sql')) {
                    LogHelper::default()->record($e, 'sql');
                }
            } else {
                LogHelper::default()->tag($errorPrefix)->record($e, $type);
            }
            
            return $errorReturn;
        }
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
            LogHelper::default()->record("{$alias}startTrans", 'sql');
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
            LogHelper::default()->record("{$alias}commit", 'sql');
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
            LogHelper::default()->record("{$alias}rollback", 'sql');
        }
    }
    
    
    /**
     * 查找记录
     * @access public
     * @param mixed $data 数据
     * @return Collection
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function select($data = null) : Collection
    {
        try {
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
            return parent::find($data);
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * 更新记录
     * @param array $data 更新的数据
     * @return int
     * @throws DbException
     */
    public function update(array $data = []) : int
    {
        $this->triggerEvent('BeforeUpdate');
        
        try {
            $result  = parent::update($data);
            $options = $this->options;
        } finally {
            $this->removeOption();
        }
        
        $this->parseOnChanged(self::CHANGED_UPDATE, $options);
        
        return $result;
    }
    
    
    /**
     * 插入记录
     * @param array   $data 数据
     * @param boolean $getLastInsID 返回自增主键
     * @return int|string
     * @throws DbException
     */
    public function insert(array $data = [], bool $getLastInsID = false)
    {
        $this->triggerEvent('BeforeInsert');
        
        try {
            $result  = parent::insert($data, $getLastInsID);
            $options = $this->options;
        } finally {
            $this->removeOption();
        }
        
        $this->parseOnChanged(self::CHANGED_INSERT, $options);
        
        return $result;
    }
    
    
    /**
     * 批量插入记录
     * @param array   $dataSet 数据集
     * @param integer $limit 每次写入数据限制
     * @return int
     * @throws DbException
     */
    public function insertAll(array $dataSet = [], int $limit = 0) : int
    {
        try {
            return parent::insertAll($dataSet, $limit);
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * 删除记录
     * @param mixed $data 表达式 true 表示强制删除
     * <p><b>$this->delete(1)</b> 通过主键删除</p>
     * <p><b>$this->delete([1,2,3])</b> 通过主键批量删除</p>
     * <p><b>$this->delete(true)</b> 强制删除</p>
     * @return int
     * @throws DbException
     */
    public function delete($data = null) : int
    {
        $this->triggerEvent('BeforeDelete');
        
        try {
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
     * @param array  $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
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
     * @param string $field 字段名
     * @param mixed  $default 默认值
     * @return mixed
     * @throws DbException
     */
    public function value(string $field, $default = null)
    {
        try {
            return parent::value(Entity::parse($field), $default);
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * 得到某个字段的值，{@see Model::value()} 别名
     * @param Entity|string $field 字段名
     * @param mixed         $default 默认值
     * @return mixed
     * @throws DbException
     */
    public function val($field, $default = null)
    {
        return $this->value(Entity::parse($field), $default);
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
     * 插入操作
     * @param array|Field $data 数据
     * @param bool        $replace 是否replace
     * @return int|string
     * @throws DbException
     */
    public function addData($data = [], bool $replace = false)
    {
        if ($replace) {
            $this->replace();
        }
        
        return $this->insert($this->parseData($data), true);
    }
    
    
    /**
     * 批量插入数据
     * @param array $data 插入的数据集合
     * @param bool  $replace 是否替换方式插入
     * @return int 返回插入的条数
     * @throws DbException
     */
    public function addAll(array $data = [], bool $replace = false) : int
    {
        if ($replace) {
            $this->replace();
        }
        
        foreach ($data as $index => $item) {
            $data[$index] = $this->parseData($item);
        }
        
        $result = $this->insertAll($data);
        
        // 触发回调
        if (method_exists($this, 'onAddAll')) {
            $this->catchException(function() {
                $this->onAddAll();
            }, false, static::class . '::onAddAll', 'error');
        }
        
        return $result;
    }
    
    
    /**
     * 保存数据
     * @param array|Field $data
     * @return int 0没有更新任何数据，大于0则代表更新的记录数
     * @throws DbException
     */
    public function saveData($data = []) : int
    {
        return $this->update($this->parseData($data));
    }
    
    
    /**
     * 批量更新，不支持链式操作
     * @param array  $data 更新的数据<pre>
     * $this->saveAll([
     *     [
     *         'id'     => 1,       // 主键比选
     *         'name'   => 'test',  // 要更新的字段1
     *         'name2'  => 'test2'  // 要更新的字段2
     *     ]
     * ]);
     * </pre>
     * @param string $pk 依据$data中的哪个字段进行查询更新 如: id
     * @return int
     * @throws InvalidArgumentException
     * @throws DbException
     */
    public function saveAll(array $data, string $pk = '') : int
    {
        $pk   = $pk ?: $this->getPk();
        $list = [];
        $idIn = [];
        foreach ($data as $values) {
            $values = $this->parseData($values);
            
            $idIn[] = "'{$values[$pk]}'";
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
            $item[$key] = "{$key} = CASE {$pk} " . PHP_EOL;
            foreach ($values as $i => $value) {
                if (is_array($value) && $value[0] == 'exp') {
                    $value = $value[1];
                } else {
                    $value = "'{$value}'";
                }
                
                $item[$key] .= "WHEN '{$i}' THEN {$value} " . PHP_EOL;
            }
            $item[$key] .= ' END ' . PHP_EOL;
        }
        
        $result = $this->execute("UPDATE {$this->getTable()} SET " . implode(',', $item) . " WHERE {$pk} in (" . implode(',', $idIn) . ")");
        
        // 触发回调
        if (method_exists($this, 'onSaveAll')) {
            $this->catchException(function() {
                $this->onSaveAll();
            }, false, static::class . '::onSaveAll', 'error');
        }
        
        return $result;
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
     * <p><b>$this->deleteInfo(1)</b> 通过主键删除</p>
     * <p><b>$this->deleteInfo([1,2,3])</b> 通过主键批量删除</p>
     * <p><b>$this->deleteInfo('1,2,3')</b> 通过主键批量删除</p>
     * @return int 返回删除的记录数
     * @throws DbException
     */
    public function deleteInfo($data) : int
    {
        if (is_string($data) && false !== strpos($data, ',')) {
            $data = explode(',', $data);
        }
        
        // 去重，去空
        if (is_array($data)) {
            $data = array_map(function($val) {
                return $this->filterDeleteInfoData($val);
            }, $data);
            $data = array_filter($data);
            $data = array_unique($data);
            $data = array_values($data);
        } else {
            $data = $this->filterDeleteInfoData($data);
        }
        
        return $this->delete($data);
    }
    
    
    /**
     * 过滤deleteInfo参数
     * @param $data
     * @return mixed
     */
    private function filterDeleteInfoData($data)
    {
        if (is_null($data)) {
            return null;
        }
        
        if (!$this->deleteInfoFilter) {
            return $data;
        }
        
        if (is_callable($this->deleteInfoFilter)) {
            return call_user_func_array($this->deleteInfoFilter, [$data]);
        } elseif (is_string($this->deleteInfoFilter) && function_exists($this->deleteInfoFilter)) {
            return call_user_func_array($this->deleteInfoFilter, [$data]);
        } else {
            return $data;
        }
    }
    
    
    /**
     * 字段值增长
     * @param string|Entity $field 字段名
     * @param float|int     $step 增长值
     * @return int
     * @throws DbException
     */
    public function setInc($field, $step = 1) : int
    {
        return $this->inc(Entity::parse($field), floatval($step))->update();
    }
    
    
    /**
     * 字段值减少
     * @param string|Entity $field 字段名
     * @param float|int     $step 减少值
     * @return int
     * @throws DbException
     */
    public function setDec($field, $step = 1) : int
    {
        return $this->dec(Entity::parse($field), floatval($step))->update();
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
        $this->options['data'][Entity::parse($field)] = $value;
        
        return $this->update();
    }
    
    
    /**
     * 查询解析后的数据
     * @return array|Field[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function selectList()
    {
        try {
            return $this->toFieldList($this->select());
        } catch (DataNotFoundException $e) {
            $notFoundMessage = $this->listNotFoundMessage ?: $this->dataNotFoundMessage;
            if ($notFoundMessage) {
                throw new DataNotFoundException($notFoundMessage, $e->getTable(), $e->getData()['Database Config']);
            }
            
            throw $e;
        }
    }
    
    
    /**
     * 查询自定义解析类解析后的数据
     * @param class-string<T> $parse 解析器
     * @param string          $notFoundMessage 数据为空提示
     * @return array<T>
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function selectParse(string $parse, $notFoundMessage = null)
    {
        try {
            $this->parse($parse);
            
            return $this->toFieldList($this->select());
        } catch (DataNotFoundException $e) {
            $notFoundMessage = $notFoundMessage ?: ($this->listNotFoundMessage ?: $this->dataNotFoundMessage);
            if ($notFoundMessage) {
                throw new DataNotFoundException($notFoundMessage, $e->getTable(), $e->getData()['Database Config']);
            }
            
            throw $e;
        }
    }
    
    
    /**
     * 查询包含关联信息的Field对象集合
     * @return array|Field[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function selectExtendList()
    {
        try {
            return $this->toExtendFieldList($this->select());
        } catch (DataNotFoundException $e) {
            $notFoundMessage = $this->listNotFoundMessage ?: $this->dataNotFoundMessage;
            if ($notFoundMessage) {
                throw new DataNotFoundException($notFoundMessage, $e->getTable(), $e->getData()['Database Config']);
            }
            
            throw $e;
        }
    }
    
    
    /**
     * 分批查询解析数据
     * @param int                          $count 每一批查询多少条
     * @param callable                     $callback 处理回调方法，接受2个参数，$list 和 $result，返回false代表阻止继续执行
     * @param string|array|Entity|Entity[] $column 排序依据字段，默认是主键字段
     * @param string                       $order 排序方式
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
     * @param int                          $count 每一批查询多少条
     * @param callable                     $callback 处理回调方法，接受2个参数，$list 和 $result，返回false代表阻止继续执行
     * @param string|array|Entity|Entity[] $column 排序依据字段，默认是主键字段
     * @param string                       $order 排序方式
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
     * 解析静态一维数组数据
     * @param array $array
     * @param mixed $var
     * @return array|mixed
     */
    public static function parseVars($array, $var = null)
    {
        if (is_null($var)) {
            return $array;
        }
        
        return isset($array[$var]) ? $array[$var] : null;
    }
    
    
    /**
     * 解析类常量
     * @param string|true $class 类，传入true则代表本类
     * @param string      $prefix 常量前缀
     * @param array       $annotations 其他注解
     * @param mixed       $mapping 数据映射，指定字段名则获取的到数据就是 值 = 字段数据，指定回调则会将数据传入回调以返回为结果
     * @return array
     */
    public static function parseConst($class, string $prefix, array $annotations = [], $mapping = null) : array
    {
        try {
            $reflect = new ReflectionClass($class === true ? static::class : $class);
        } catch (ReflectionException $e) {
            return [];
        }
        
        $list = [];
        foreach ($reflect->getConstants() as $key => $value) {
            if (0 !== strpos($key, $prefix)) {
                continue;
            }
            
            $constant = $reflect->getReflectionConstant($key);
            $doc      = $constant->getDocComment();
            $name     = '';
            $item     = [];
            if (false === strpos($doc, PHP_EOL)) {
                if (preg_match('/\/\*\*\s@.*?\s[int|float|string|bool|boolean|null]+(.*?)\*\//i', $doc, $match)) {
                    $name = trim($match[1] ?? '');
                } else {
                    preg_match('/\/\*\*(.*?)\*\//i', $doc, $match);
                    $name = trim($match[1] ?? '');
                }
            } else {
                if (preg_match('/\/\*\*(.*?)(@.*?)\*\//is', $doc, $match)) {
                    $name = preg_replace('/\n.*?\*/', '', $match[1] ?? '');
                    $name = trim($name);
                    
                    if ($annotations) {
                        $extendRegex = implode('|', $annotations);
                        preg_match_all('/@([' . $extendRegex . ']+)(.*?)\*+/is', $match[2] . '*', $extendMatch);
                        foreach ($extendMatch[1] ?? [] as $i => $extendKey) {
                            $item[$extendKey] = trim($extendMatch[2][$i] ?? '');
                        }
                    }
                }
            }
            
            foreach ($annotations as $extendKey) {
                $item[$extendKey] = $item[$extendKey] ?? '';
            }
            $item['name']  = $name;
            $item['key']   = $key;
            $item['value'] = $value;
            
            
            if (is_callable($mapping)) {
                $item = call_user_func_array($mapping, [$item]);
            } elseif (is_string($mapping) && !empty($mapping)) {
                $item = $item[$mapping];
            }
            
            $list[$value] = $item;
        }
        
        return $list;
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
     * 获取Join别名
     * @return string
     */
    public function getJoinAlias() : string
    {
        $alias = $this->options['alias'] ?? '';
        $alias = $alias ?: $this->joinAlias;
        
        return $alias ?: $this->getTableWithoutPrefix();
    }
    
    
    /**
     * 解析增加修改的数据
     * @param array|Field $data
     * @return array
     */
    protected function parseData($data = []) : array
    {
        if ($data instanceof Field) {
            $data = $data->getDBData();
        }
        
        $fields = $this->getTableFields();
        $list   = [];
        
        // 支持 exp 写法 及 过滤字段
        foreach ($data as $key => $value) {
            if (!in_array($key, $fields) || is_null($value)) {
                continue;
            }
            
            if ($value instanceof Entity) {
                $value = new Raw($value->field() . $value->op() . $value->value());
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
     * 模型字段实体条件
     * @param mixed ...$entity
     * @return $this
     */
    public function whereEntity(...$entity)
    {
        foreach ($entity as $item) {
            if ($item instanceof Entity) {
                $value = $item->value();
                if ($value instanceof Entity) {
                    $this->whereRaw(sprintf('`%s` %s `%s`', $item->field(), $item->op(), $value->field()));
                } else {
                    $this->where($item->field(), $item->op(), $item->value());
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
        return parent::parseWhereExp($logic, Entity::parse($field), $op, $condition, $param, $strict);
    }
    
    
    /**
     * @inheritDoc
     */
    public function join($join, string $condition = null, string $type = 'INNER', array $bind = [])
    {
        if ($join instanceof Model) {
            $model = $join;
            $join  = [$model->getTable() => $model->getJoinAlias()];
            $model->removeOption();
        } elseif (is_string($join) && is_subclass_of($join, Model::class)) {
            /** @var Model $model */
            $model = call_user_func([$join, 'init']);
            $join  = [$model->getTable() => $model->getJoinAlias()];
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
     * 执行查询但只返回PDOStatement对象
     * todo 目前使用where指定条件不会解析参数
     * @return PDOStatement
     */
    public function getPdo() : PDOStatement
    {
        $this->options['distinct'] = $this->options['distinct'] ?? false;
        $this->options['extra']    = $this->options['extra'] ?? '';
        $this->options['join']     = $this->options['join'] ?? [];
        $this->options['where']    = $this->options['where'] ?? [];
        $this->options['having']   = $this->options['having'] ?? '';
        $this->options['order']    = $this->options['order'] ?? [];
        $this->options['limit']    = $this->options['limit'] ?? '';
        $this->options['union']    = $this->options['union'] ?? [];
        $this->options['comment']  = $this->options['comment'] ?? '';
        $this->options['table']    = $this->options['table'] ?? $this->getTable();
        
        return parent::getPdo();
    }
    
    
    /**
     * 时间戳范围条件
     * @param string|Entity $field 字段
     * @param string|int    $startOrTimeRange 开始时间或时间范围
     * @param string|int    $endOrSpace 结束时间或时间范围分隔符
     * @param bool          $split 是否分割传入的时间范围
     * @return $this
     */
    public function whereTimeIntervalRange($field, $startOrTimeRange = 0, $endOrSpace = 0, $split = false)
    {
        $field = (string) $field;
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
    final public function optimize()
    {
        $this->execute("OPTIMIZE TABLE `{$this->getTable()}`");
    }
    
    
    /**
     * 打印结构
     */
    final public function printField()
    {
        $list   = $this->getFields();
        $br     = PHP_EOL;
        $string = '<pre contenteditable="true" style="background-color: #F3F3F3; margin: 15px; padding: 15px; border-radius: 5px; border: 1px #BBB solid;">';
        foreach ($list as $i => $r) {
            $r['type']    = explode('(', $r['type']);
            $r['type']    = strtoupper($r['type'][0]);
            $r['comment'] = trim($r['comment']);
            
            $type      = 'string';
            $r['name'] = Str::camel($r['name']);
            if (in_array($r['type'], [
                'TINYINT',
                'SMALLINT',
                'MEDIUMINT',
                'INT',
                'BIGINT',
                'SERIAL'
            ])) {
                $type = 'int';
            } elseif (in_array($r['type'], ['DECIMAL', 'FLOAT', 'DOUBLE', 'REAL'])) {
                $type = 'float';
            }
            $r['type'] = $type;
            
            $string   .= "* @method static \BusyPHP\model\Entity {$r['name']}(\$op = null, \$value = null) {$r['comment']}{$br}";
            $list[$i] = $r;
        }
        $string .= '</pre>';
        
        $string .= '<pre contenteditable="true" style="background-color: #F3F3F3; margin: 15px; padding: 15px; border-radius: 5px; border: 1px #BBB solid;">';
        foreach ($list as $i => $r) {
            $string .= "/**{$br}";
            if ($r['comment']) {
                $string .= " * {$r['comment']}{$br}";
            }
            $string .= " * @var {$r['type']} {$br}";
            $string .= " */{$br}";
            $string .= "public \${$r['name']};{$br}";
        }
        $string .= '</pre>';
        
        $string .= '<pre contenteditable="true" style="background-color: #F3F3F3; margin: 15px; padding: 15px; border-radius: 5px; border: 1px #BBB solid;">';
        foreach ($list as $i => $r) {
            $string .= "/**{$br}";
            $string .= " * 设置{$r['comment']}{$br}";
            $string .= " * @param {$r['type']} \${$r['name']}{$br}";
            $string .= " * @return \$this{$br}";
            $string .= " */{$br}";
            
            $string .= "public function set" . ucfirst($r['name']) . "(\${$r['name']}) {{$br}";
            if ($r['type'] == 'int') {
                $string .= "&nbsp;&nbsp;&nbsp;&nbsp;\$this->{$r['name']} = intval(\${$r['name']});{$br}";
            } elseif ($r['type'] == 'float') {
                $string .= "&nbsp;&nbsp;&nbsp;&nbsp;\$this->{$r['name']} = floatval(\${$r['name']});{$br}";
            } else {
                $string .= "&nbsp;&nbsp;&nbsp;&nbsp;\$this->{$r['name']} = trim(\${$r['name']});{$br}";
            }
            $string .= "&nbsp;&nbsp;&nbsp;&nbsp;return \$this;{$br}";
            $string .= "}<br />";
        }
        $string .= '</pre>';
        
        echo $string;
    }
}