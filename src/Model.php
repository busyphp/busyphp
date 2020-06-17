<?php
declare (strict_types = 1);

namespace BusyPHP;

use ArrayAccess;
use BusyPHP\model\Field;
use BusyPHP\exception\AppException;
use BusyPHP\exception\SQLException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Arr;
use BusyPHP\helper\util\Filter;
use BusyPHP\app\admin\model\system\logs\SystemLogs;
use BusyPHP\model\Query;
use Closure;
use JsonSerializable;
use think\Collection;
use think\contract\Arrayable;
use think\contract\Jsonable;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\db\exception\PDOException;
use think\db\Raw;
use think\DbManager;
use think\facade\Db;
use think\facade\Log;
use think\helper\Str;
use think\model\concern\Attribute;
use think\model\concern\Conversion;
use think\model\concern\ModelEvent;
use think\model\concern\TimeStamp;

/**
 * 数据模型基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/30 下午9:22 上午 Model.php $
 * @method mixed onBeforeInsert() 新增前回调, 返回false阻止新增
 * @method mixed onBeforeUpdate() 更新前回调, 返回false阻止更新
 * @method mixed onBeforeDelete() 删除前回调, 返回false阻止更新
 * @method void onChanged(string $method, $id, array $options) 新增/更新/删除后回调
 * @method void onAfterWrite($id, array $options) 新增/更新完成后回调
 * @method void onAfterInsert($id, array $options) 新增完成后回调
 * @method void onAfterUpdate($id, array $options) 更新完成后回调
 * @method void onAfterDelete($id, array $options) 删除完成后回调
 * @method mixed getField(string $name, $default = null) 获取单个字段的值
 * @method mixed setField(string $name, $value) 设置单个字段
 * @method $this lockShare(boolean $isLock) 是否加共享锁，允许其它对象读不允许写
 */
abstract class Model extends Query implements JsonSerializable, ArrayAccess, Arrayable, Jsonable
{
    use Attribute;
    use ModelEvent;
    use TimeStamp;
    use Conversion;
    
    /**
     * @var App
     */
    protected $app;
    
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
     * 错误的SQL语句
     * @var string
     */
    protected $errorSQL = '';
    
    /**
     * Db对象
     * @var \BusyPHP\Db
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
     * 单例模式容器
     * @var Model[]
     */
    protected static $instances = [];
    
    /**
     * 增加删除或修改操作的数据
     * @var array
     */
    private static $handleData = [];
    
    /**
     * 回调方法
     * @var Closure[]
     */
    private $callback = [];
    
    //+--------------------------------------
    //| 回调常量
    //+--------------------------------------
    /** 操作前回调 */
    const CALLBACK_BEFORE = 0;
    
    /** 操作成功回调 */
    const CALLBACK_SUCCESS = 1;
    
    /** 操作失败回调 */
    const CALLBACK_ERROR = 2;
    
    /** 操作完成回调 */
    const CALLBACK_COMPLETE = 3;
    
    /** 操作过程中回调 */
    const CALLBACK_PROCESS = 4;
    
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
     * 架构函数
     */
    public function __construct()
    {
        $this->app = app();
        
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
        parent::__construct(self::$db->instance($this->configName));
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
     * 获取数据表名称，不包含表前缀
     * @return string
     */
    public function getTableWithoutPrefix() : string
    {
        return parse_name($this->name, 0);
    }
    
    
    /**
     * 设置当前模型数据表的后缀
     * @access public
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
     * @access public
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
     * 获取错误的SQL语句
     * @return string
     */
    public function getErrorSQL() : string
    {
        return $this->errorSQL;
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
     * @param int     $callType 回调类型
     * @param Closure $callback 回调方法，具体参数由子类定义
     * @return $this
     */
    public function setCallback(int $callType, Closure $callback) : Model
    {
        $this->callback[$callType] = $callback;
        
        return $this;
    }
    
    
    /**
     * 触发回调方法
     * @param int   $callType 回调类型
     * @param array $args 回调方法参数
     * @return mixed
     */
    protected function triggerCallback(int $callType, $args = [])
    {
        if (isset($this->callback[$callType])) {
            return call_user_func_array($this->callback[$callType], $args);
        }
        
        return null;
    }
    
    
    /**
     * 触发事件回调
     * @param string $event
     * @return bool
     */
    protected function triggerEvent(string $event) : bool
    {
        $call = 'on' . Str::studly($event);
        if (method_exists($this, $call)) {
            $result = $this->catchException(function() use ($call) {
                return $this->$call();
            }, false, static::class . '::' . $call, 'error');
            
            return $result === false ? false : true;
        }
        
        return true;
    }
    
    
    /**
     * 获取静态缓存
     * @param string $name 缓存名称
     * @return mixed
     */
    public function getCache($name)
    {
        return Cache::get(static::class, $name);
    }
    
    
    /**
     * 设置静态缓存
     * @param string $name 缓存名称
     * @param mixed  $value 缓存值
     * @param int    $expire 缓存时长, 单位秒，0为不过期, 默认过期时间10分钟
     * @return bool
     */
    public function setCache($name, $value, $expire = 600)
    {
        return Cache::set(static::class, $name, $value, $expire);
    }
    
    
    /**
     * 移除静态缓存
     * @param string $name 缓存名称
     * @return bool
     */
    public function deleteCache($name = '')
    {
        return Cache::delete(static::class, $name);
    }
    
    
    /**
     * 清理静态缓存
     */
    public function clearCache()
    {
        Cache::clear(static::class);
    }
    
    
    public function __call(string $method, array $args)
    {
        switch (strtolower($method)) {
            case 'getfield':
                return $this->value($args[0], isset($args[1]) ? $args[1] : null);
            break;
            case 'setfield':
                return $this->saveData([$args[0] => $args[1]]);
            break;
            case 'lockshare':
                return $this->lock($args[0] === true ? 'LOCK IN SHARE MODE' : false);
            break;
            default:
                return parent::__call($method, $args);
        }
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
    
    
    public function offsetSet($name, $value)
    {
        $this->setAttr($name, $value);
    }
    
    
    public function offsetExists($name) : bool
    {
        return $this->__isset($name);
    }
    
    
    public function offsetUnset($name)
    {
        $this->__unset($name);
    }
    
    
    public function offsetGet($name)
    {
        return $this->getAttr($name);
    }
    
    
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
     * @access public
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
     * @return Model
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
     * @return Model
     */
    public static function connect(string $name)
    {
        $model = new static();
        $model->setConfigName($name);
        
        return $model;
    }
    
    
    /**
     * 单例模式
     * @return $this
     */
    public static function init()
    {
        if (isset(static::$instances[static::class])) {
            return static::$instances[static::class];
        }
        
        static::$instances[static::class] = new static();
        
        return static::$instances[static::class];
    }
    
    
    /**
     * 模型列表数据解析
     * @param array $list 要解析的列表数据
     * @return array
     */
    public static function parseList($list)
    {
        $list = is_array($list) ? $list : [];
        if (func_num_args() === 2 && $callback = func_get_arg(1)) {
            if (is_callable($callback)) {
                $list = call_user_func_array($callback, [$list]);
            }
        }
        
        return $list;
    }
    
    
    /**
     * 解析扩展数据
     * @param array $list 数据列表
     * @param bool  $isOnly 是否单条数据
     * @return array
     */
    public static function parseExtendList($list, $isOnly = false)
    {
        $list = is_array($list) ? $list : [];
        $list = $isOnly ? [$list] : $list;
        if (func_num_args() == 3 && $callback = func_get_arg(2)) {
            if (is_callable($callback)) {
                $list = call_user_func_array($callback, [$list]);
            }
        }
        
        return $isOnly ? $list[0] : $list;
    }
    
    
    /**
     * 模型信息数据解析
     * @param array $info 要解析的数据
     * @return array
     */
    public static function parseInfo($info)
    {
        $list = static::parseList([0 => $info]);
        
        return $list[0];
    }
    
    
    /**
     * 获取单条信息
     * @param mixed $id 信息ID
     * @return array
     * @throws SQLException
     */
    public function getInfo($id)
    {
        $info = $this->where($this->getPk(), '=', $id)->findData();
        if (!$info) {
            $message = '信息不存在';
            if (func_num_args() === 2) {
                if ($msg = func_get_arg(1)) {
                    $message = $msg;
                }
            }
            throw new SQLException($message, $this);
        }
        
        return static::parseInfo($info);
    }
    
    
    /**
     * 获取单条信息(包含扩展信息)
     * @param mixed $id
     * @return array
     * @throws AppException
     */
    public function getAllInfo($id)
    {
        return static::parseExtendList($this->getInfo($id), true);
    }
    
    
    /**
     * 解析 增加/更新/删除 操作中的主键值并触发回调
     * @param string $method
     * @param array  $options
     */
    private function parseOnChanged($method, $options)
    {
        // 记录数据
        $this->addHandleData($method, 'method');
        if ($options['where']) {
            $this->addHandleData($options['where'], 'where');
        }
        if ($options['data']) {
            $this->addHandleData($options['data'], 'data');
        }
        
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
            $where = isset($where['AND']) ? $where['AND'] : [];
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
    protected function catchException(Closure $closure, $errorReturn = false, $errorPrefix = '', $type = 'sql')
    {
        try {
            return call_user_func($closure);
        } catch (PDOException $e) {
            $this->errorSQL = 'PDO ERROR: ' . $e->getMessage();
            
            $data     = $e->getData();
            $errorSql = $data['Database Status']['Error SQL'] ?? '';
            $message  = $this->errorSQL . ($errorSql ? ", [ SQL : {$errorSql}]" : '');
        } catch (DbException $e) {
            $this->errorSQL = 'DB ERROR: ' . $e->getMessage();
            
            $data     = $e->getData();
            $errorSql = $data['Database Status']['Error SQL'] ?? '';
            $message  = $this->errorSQL . ($errorSql ? ", [ SQL : {$errorSql}]" : '');
        } catch (\Exception | \Throwable $e) {
            $this->errorSQL = 'ERROR: ' . $e->getMessage();
            $message        = $this->errorSQL;
        }
        
        if ($type == 'sql') {
            if ($this->getConfig('trigger_sql')) {
                Log::record($message, 'sql');
            }
        } else {
            Log::record(($errorPrefix ? "[ {$errorPrefix} ] " : '') . $message, $type);
        }
        
        
        return $errorReturn;
    }
    
    
    /**
     * 启动事务
     * @access public
     * @param bool   $disabled 是否禁用事物
     * @param string $alias 事物别名，用于记录SQL日志
     * @return void
     */
    public function startTrans($disabled = false, $alias = '') : void
    {
        if ($disabled) {
            return;
        }
        
        parent::startTrans();
        
        if ($this->getConfig('trigger_sql')) {
            $alias = $alias ? $alias . ' ' : '';
            Log::record("{$alias}startTrans", 'sql');
        }
    }
    
    
    /**
     * 提交事务
     * @param bool   $disabled 是否禁用事物
     * @param string $alias 事物别名，用于记录SQL日志
     * @return void
     */
    public function commit($disabled = false, $alias = '') : void
    {
        if ($disabled) {
            return;
        }
        
        parent::commit();
        
        if ($this->getConfig('trigger_sql')) {
            $alias = $alias ? $alias . ' ' : '';
            Log::record("{$alias}commit", 'sql');
        }
    }
    
    
    /**
     * 事务回滚
     * @param bool   $disabled 是否禁用事物
     * @param string $alias 事物别名，用于记录SQL日志
     */
    public function rollback($disabled = false, $alias = '') : void
    {
        if ($disabled) {
            return;
        }
        
        parent::rollback();
        
        if ($this->getConfig('trigger_sql')) {
            $alias = $alias ? $alias . ' ' : '';
            Log::record("{$alias}rollback", 'sql');
        }
    }
    
    
    /**
     * 查找记录
     * @access public
     * @param mixed $data 数据
     * @return Collection
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    public function select($data = null) : Collection
    {
        $result = parent::select($data);
        $this->removeOption();
        
        return $result;
    }
    
    
    /**
     * 查找单条记录
     * @param mixed $data 查询数据
     * @return array|Model|null
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    public function find($data = null)
    {
        $result = parent::find($data);
        $this->removeOption();
        
        return $result;
    }
    
    
    /**
     * 更新记录
     * @param mixed $data 数据
     * @return integer
     * @throws DbException
     */
    public function update(array $data = []) : int
    {
        if (false === $this->triggerEvent('BeforeUpdate')) {
            return -1;
        }
        
        $result  = parent::update($data);
        $options = $this->options;
        $this->removeOption();
        $this->parseOnChanged(self::CHANGED_UPDATE, $options);
        
        return $result;
    }
    
    
    /**
     * 插入记录
     * @param array   $data 数据
     * @param boolean $getLastInsID 返回自增主键
     * @return int|false
     */
    public function insert(array $data = [], bool $getLastInsID = false)
    {
        if (false === $this->triggerEvent('BeforeInsert')) {
            return false;
        }
        
        $result  = parent::insert($data, $getLastInsID);
        $options = $this->options;
        $this->removeOption();
        $this->parseOnChanged(self::CHANGED_INSERT, $options);
        
        return $result;
    }
    
    
    /**
     * 批量插入记录
     * @param array   $dataSet 数据集
     * @param integer $limit 每次写入数据限制
     * @return integer
     */
    public function insertAll(array $dataSet = [], int $limit = 0) : int
    {
        $result = parent::insertAll($dataSet, $limit);
        $this->addHandleData($dataSet, 'insert all');
        $this->removeOption();
        
        return $result;
    }
    
    
    /**
     * 删除记录
     * @param mixed $data 表达式 true 表示强制删除
     * @return int
     * @throws DbException
     */
    public function delete($data = null) : int
    {
        if (false === $this->triggerEvent('BeforeDelete')) {
            return 0;
        }
        
        $result  = parent::delete($data);
        $options = $this->options;
        $this->removeOption();
        $this->parseOnChanged(self::CHANGED_DELETE, $options);
        
        return $result;
    }
    
    
    /**
     * 通过Select方式插入记录
     * @param array  $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @return integer
     */
    public function selectInsert(array $fields, string $table) : int
    {
        $result = parent::selectInsert($fields, $table);
        $this->removeOption();
        
        return $result;
    }
    
    
    /**
     * 得到某个字段的值
     * @param string $field 字段名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function value(string $field, $default = null)
    {
        $result = parent::value($field, $default);
        $this->removeOption();
        
        return $result;
    }
    
    
    /**
     * 得到某个列的数组
     * @param string $field 字段名 多个字段用逗号分隔
     * @param string $key 索引
     * @return array
     */
    public function column(string $field, string $key = '') : array
    {
        $result = parent::column($field, $key);
        $this->removeOption();
        
        return $result;
    }
    
    
    /**
     * 聚合查询
     * @access protected
     * @param string     $aggregate 聚合方法
     * @param string|Raw $field 字段名
     * @param bool       $force 强制转为数字类型
     * @return mixed
     */
    protected function aggregate(string $aggregate, $field, bool $force = false)
    {
        $result = parent::aggregate($aggregate, $field, $force);
        $this->removeOption();
        
        return $result;
    }
    
    
    /**
     * 执行查询并输出数组
     * @return array|false
     */
    public function selecting()
    {
        return $this->catchException(function() {
            $result = $this->select();
            if ($result->isEmpty()) {
                return [];
            }
            
            return $result->toArray();
        });
    }
    
    
    /**
     * 查找单条数据并返回数组
     * @param mixed $data
     * @return array|false
     */
    public function findData($data = null)
    {
        return $this->catchException(function() use ($data) {
            $info = $this->find($data);
            
            return is_array($info) ? $info : [];
        });
    }
    
    
    /**
     * 插入操作
     * @param array|Field $data 数据
     * @param bool        $replace 是否replace
     * @return mixed|false 自增ID或false
     */
    public function addData($data = [], bool $replace = false)
    {
        if ($replace) {
            $this->replace();
        }
        
        return $this->catchException(function() use ($data) {
            return $this->insertGetId($this->parseData($data));
        });
    }
    
    
    /**
     * 批量插入数据
     * @param array $data 插入的数据集合
     * @param bool  $replace 是否替换方式插入
     * @return int|false 返回插入的条数或false
     */
    public function addAll(array $data = [], bool $replace = false)
    {
        if ($replace) {
            $this->replace();
        }
        
        return $this->catchException(function() use ($data) {
            return $this->insertAll($data);
        });
    }
    
    
    /**
     * 保存数据
     * @param array|Field $data
     * @return int|false 返回0说明没有更新，返回大于0则代表更新了的记录数，false则代表更新失败
     */
    public function saveData($data = [])
    {
        return $this->catchException(function() use ($data) {
            $result = $this->update($this->parseData($data));
            if ($result === -1) {
                return false;
            }
            
            return $result;
        });
    }
    
    
    /**
     * 批量更新，不支持连贯操作
     * @param array  $data 更新的数据<pre>
     * array(
     *     array(
     *         'id' => 1,
     *         'name' => 'test',
     *         'name2' => 'test2'
     *     )
     * )
     * </pre>
     * @param string $pk 依据$data中的哪个字段进行查询更新 如: id
     * @return false|int
     */
    public function saveAll($data = [], $pk = '')
    {
        return $this->catchException(function() use ($data, $pk) {
            $pk   = $pk ? $pk : $this->getPk();
            $list = [];
            $idIn = [];
            foreach ($data as $values) {
                $idIn[] = "'{$values[$pk]}'";
                foreach ($values as $i => $value) {
                    $list[$i][$values[$pk]] = $value;
                }
            }
            
            if (!$idIn) {
                throw new DbException('没有主键字段, 无法批量更新');
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
            if (false !== $result) {
                $this->addHandleData($data, 'save all');
            }
            
            return $result;
        });
    }
    
    
    /**
     * 删除记录
     * @param mixed $data 表达式 true 表示强制删除
     * @return int|false
     */
    public function deleteData($data = null)
    {
        return $this->catchException(function() use ($data) {
            return $this->delete($data);
        });
    }
    
    
    /**
     * 执行删除，支持传入数组、字符串(可包含逗号)
     * @param string|int|array $id
     * @return int 返回删除的记录数
     * @throws VerifyException
     * @throws SQLException
     */
    public function del($id)
    {
        if (is_string($id) && false !== strpos($id, ',')) {
            $id = explode(',', trim($id, ','));
        }
        if (!$id) {
            throw new VerifyException(func_get_arg(1) ?: '请选择要删除的信息', $this->getPk());
        }
        
        if (false === $result = $this->deleteData($id)) {
            throw new SQLException(func_get_arg(2) ?: '删除信息失败', $this);
        }
        
        return $result;
    }
    
    
    /**
     * 字段值增长
     * @access public
     * @param string  $field 字段名
     * @param integer $step 增长值
     * @return false|int
     */
    public function setInc($field, $step = 1)
    {
        return $this->inc($field, $step)->saveData();
    }
    
    
    /**
     * 字段值减少
     * @param string  $field 字段名
     * @param integer $step 减少值
     * @return false|int
     */
    public function setDec($field, $step = 1)
    {
        return $this->dec($field, $step)->saveData();
    }
    
    
    /**
     * 查询解析后的数据
     * @return array
     */
    public function selectList()
    {
        return static::parseList($this->selecting());
    }
    
    
    /**
     * 查询解析后(包含扩展信息)的数据
     * @return array
     */
    public function selectExtendList()
    {
        return static::parseExtendList($this->selectList());
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
     * 查询列表并用字段构建
     * @param array       $values in查询的值
     * @param string|null $key 查询的字段，默认id
     * @param string|null $field 构建的字段，默认id
     * @param bool        $isExtends 是否查询扩展数据
     * @return array
     */
    public function buildListWithField($values, $key = null, $field = null, $isExtends = false)
    {
        $key    = $key ? $key : 'id';
        $field  = $field ? $field : 'id';
        $list   = [];
        $values = Filter::trimArray($values);
        if ($values) {
            $this->whereof([$key => ['in', $values]]);
            $list = $isExtends ? $this->selectExtendList() : $this->selectList();
            $list = Arr::listByKey($list, $field);
        }
        
        return $list;
    }
    
    
    /**
     * 构建IN查询语句
     * @param string $field 给In查询用的字段
     * @param string $key 查询条件字段
     * @param mixed  $value 查询条件值
     * @return array
     */
    public function buildInSql($field, $key, $value) : array
    {
        return ['in', $this->field($field)->whereof([$key => $value])->buildSql(false), true];
    }
    
    
    /**
     * 解析增加修改的数据
     * @param array|Field $data
     * @return array
     */
    protected function parseData($data = []) : array
    {
        $data = $data ?? [];
        if ($data instanceof Field) {
            $data = $data->getDBData();
        }
        
        // 支持 exp 写法
        foreach ($data as $key => $value) {
            if (is_array($value) && count($value) == 2 && is_string($value[0]) && strtolower($value[0]) === 'exp') {
                $data[$key] = Db::raw($value[1]);
            }
        }
        
        return $data;
    }
    
    
    /**
     * 优化数据表
     */
    final public function optimize()
    {
        $this->execute("OPTIMIZE TABLE `{$this->getTable()}`");
    }
    
    
    /**
     * 获取增加或修改的处理数据
     * @return array
     */
    final public function getHandleData()
    {
        return self::$handleData;
    }
    
    
    /**
     * 设置增加或修改的处理数据
     * @param mixed  $data 要追加的数据
     * @param string $name 数据标识
     * @return $this
     */
    final public function addHandleData($data, $name = '')
    {
        if ($this instanceof SystemLogs) {
            return $this;
        }
        
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if ($value instanceof Raw) {
                    $data[$key] = $value->getValue();
                }
            }
        }
        
        $table                    = $this->getTable();
        self::$handleData[$table] = self::$handleData[$table] ?? [];
        if ($name) {
            self::$handleData[$table][$name] = $data;
        } else {
            self::$handleData[$table][] = $data;
        }
        
        return $this;
    }
    
    
    /**
     * 清理增加或修改的处理数据
     */
    final public function clearHandleData()
    {
        self::$handleData = [];
    }
    
    
    /**
     * 打印DI结构
     */
    final public function __printField()
    {
        $list = $this->getFields();
        
        $string = "";
        foreach ($list as $i => $r) {
            $r['type']    = explode('(', $r['type']);
            $r['type']    = strtoupper($r['type'][0]);
            $r['comment'] = trim($r['comment']);
            
            $type      = 'string';
            $r['name'] = lcfirst(parse_name($r['name'], 1));
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
            
            $string .= "/**<br />";
            if ($r['comment']) {
                $string .= "* {$r['comment']}<br />";
            }
            $string   .= "* @var {$r['type']} <br />";
            $string   .= "*/<br />";
            $string   .= "public \${$r['name']};<br />";
            $list[$i] = $r;
        }
        
        foreach ($list as $i => $r) {
            $string .= "/**<br />";
            $string .= "* 设置{$r['comment']}<br />";
            $string .= "* @param {$r['type']} \${$r['name']}<br />";
            $string .= "* @return \$this<br />";
            $string .= "*/<br />";
            
            $string .= "public function set" . ucfirst($r['name']) . "(\${$r['name']}) {<br />";
            if ($r['type'] == 'string') {
                $string .= "&nbsp;&nbsp;&nbsp;&nbsp;\$this->{$r['name']} = trim(\${$r['name']});<br />";
            } else {
                $string .= "&nbsp;&nbsp;&nbsp;&nbsp;\$this->{$r['name']} = floatval(\${$r['name']});<br />";
            }
            $string .= "&nbsp;&nbsp;&nbsp;&nbsp;return \$this;<br />";
            $string .= "}<br />";
        }
        
        echo $string;
    }
}