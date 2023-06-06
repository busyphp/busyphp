<?php
declare (strict_types = 1);

namespace BusyPHP;

use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\exception\MethodNotFoundException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\LogHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\interfaces\ModelValidateInterface;
use BusyPHP\model\annotation\relation\Relation;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use BusyPHP\model\JoinClause;
use BusyPHP\model\traits\Event;
use BusyPHP\traits\Cache;
use BusyPHP\traits\ContainerDefine;
use BusyPHP\traits\ContainerInstance;
use Closure;
use PDOStatement;
use RuntimeException;
use think\Collection;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\InvalidArgumentException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\db\Raw;
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
    use ContainerDefine;
    use ContainerInstance;
    
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
     * 指定字段结构类
     * @var class-string<Field>|Field
     */
    protected string $fieldClass = '';
    
    /**
     * 单条信息不存在的错误消息
     * @var string
     */
    protected string $dataNotFoundMessage = '';
    
    /**
     * 数据集信息不存在错误消息
     * @var string
     */
    protected string $listNotFoundMessage = '';
    
    /**
     * 数据表后缀
     * @var string
     */
    protected string $suffix = '';
    
    /**
     * 当前模型的数据库连接标识
     * @var string
     */
    protected string $connect = '';
    
    /**
     * 只读字段
     * @var array
     */
    protected array $readonly = [];
    
    /**
     * 软删除字段
     * @var string
     */
    protected string $softDeleteField = '';
    
    /**
     * 软删除字段默认值
     * @var int|null
     */
    protected ?int $softDeleteDefault = 0;
    
    /**
     * 是否启用软删除机制
     * @var bool
     */
    protected bool $softDelete = false;
    
    /**
     * 自动写入时间字段update_time时间字段在insert时是否同步create_time字段值
     * @var bool|mixed
     */
    protected bool $autoWriteUpdateTimeSync = true;
    
    /**
     * 输出场景
     * @var array{name: string, relation: array<string, string>}
     */
    protected array $scene = [];
    
    /**
     * Db对象
     * @var Db
     */
    protected static Db $db;
    
    /**
     * 容器对象的依赖注入方法
     * @var callable
     */
    protected static $invoker;
    
    /**
     * 服务注入
     * @var Closure[]
     */
    protected static array $maker = [];
    
    /**
     * 方法注入
     * @var Closure[][]
     */
    protected static array $macro = [];
    
    
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
     * 设置Db对象
     * @param Db $db Db对象
     * @return void
     */
    public static function setDb(Db $db)
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
     * @param string $suffix 切换的表后缀
     * @param string $connect 数据库连接标识
     * @param bool   $force 是否强制重连
     * @return static
     */
    public static function suffix(string $suffix, string $connect = '', bool $force = false) : static
    {
        return self::init($connect, $force)->setSuffix($suffix);
    }
    
    
    /**
     * 切换数据库连接进行查询
     * @param string $connect 数据库连接标识
     * @param bool   $force 是否强制重连
     * @return static
     */
    public static function connect(string $connect, bool $force = false) : static
    {
        return self::init($connect, $force);
    }
    
    
    /**
     * 实例化一个模型
     * @param string $connect 连接标识
     * @param bool   $force 是否强制重连
     * @return static
     */
    public static function init(string $connect = '', bool $force = false) : static
    {
        return self::makeContainer([$connect, $force], true);
    }
    
    
    /**
     * 构架函数
     * @param string $connect 连接标识
     * @param bool   $force 是否强制重连
     */
    public function __construct(string $connect = '', bool $force = false)
    {
        // 当前模型名
        if (empty($this->name)) {
            $this->name = basename(str_replace('\\', '/', self::getDefineContainer()));
        }
        
        // 连接标识
        if ($connect) {
            $this->connect = $connect;
        }
        
        // 初始化字段结构参数
        $createTimeField = '';
        $updateTimeField = '';
        if ($this->fieldClass) {
            if (!is_subclass_of($this->fieldClass, Field::class)) {
                throw new ClassNotExtendsException($this->fieldClass, Field::class);
            }
            
            $modelParams     = $this->fieldClass::getModelParams();
            $createTimeField = $modelParams['create_time_field'];
            $updateTimeField = $modelParams['update_time_field'];
            $this->readonly  = $modelParams['readonly'];
            $this->pk($modelParams['pk']);
            $this->setFieldType($modelParams['type']);
            
            // 自动写入时间
            $this->autoWriteUpdateTimeSync = $modelParams['update_time_sync'];
            if ($modelParams['auto_timestamp']) {
                $this->isAutoWriteTimestamp($modelParams['auto_timestamp']);
            }
            if ($modelParams['date_format']) {
                $this->setDateFormat($modelParams['date_format']);
            }
            
            // 定义软删除
            if ($modelParams['soft_delete']) {
                $this->softDeleteField   = $modelParams['soft_delete_field'];
                $this->softDeleteDefault = $modelParams['soft_delete_default'];
                $this->softDelete        = true;
            }
        }
        
        // 执行服务注入
        if (!empty(static::$maker)) {
            foreach (static::$maker as $maker) {
                call_user_func($maker, $this);
            }
        }
        
        // 自动写入时间
        if ($createTimeField) {
            $this->createTime = $createTimeField;
        }
        if ($updateTimeField) {
            $this->updateTime = $updateTimeField;
        }
        
        parent::__construct(static::$db->connect($this->connect, $force));
    }
    
    
    /**
     * 获取绑定的字段结构类
     * @param bool $fail
     * @return class-string<Field>|Field|null
     */
    public function getFieldClass(bool $fail = true) : mixed
    {
        if (!$this->fieldClass) {
            if ($fail) {
                throw new RuntimeException(sprintf('Field structure class is not set in the model "%s"', get_class($this)));
            }
            
            return null;
        }
        
        return $this->fieldClass;
    }
    
    
    /**
     * @inheritDoc
     * @return $this
     */
    public function newQuery() : static
    {
        $query = self::init($this->getConnect());
        
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
     * @param mixed $method
     * @param array $vars 参数
     * @return mixed
     */
    public function invoke($method, array $vars = []) : mixed
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
    public function getName() : string
    {
        return StringHelper::snake($this->name);
    }
    
    
    /**
     * 设置当前模型数据表的后缀
     * @param string $suffix 数据表后缀
     * @return $this
     */
    public function setSuffix(string $suffix) : static
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
            case str_starts_with($lower, 'getfieldby'):
                $name = Str::snake(substr($method, 10));
                
                return $this->where($name, '=', $args[0])->value($args[1], $args[2] ?? null);
            
            // getInfoByField
            case str_starts_with($lower, 'getinfoby'):
                $name = Str::snake(substr($method, 9));
                
                return $this->where($name, '=', $args[0])->failException(true, $args[1] ?? null)->findInfo();
            
            // findInfoByField
            case str_starts_with($lower, 'findinfoby'):
                $name = Str::snake(substr($method, 10));
                
                return $this->where($name, '=', $args[0])->findInfo();
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
     * 查询是否载入关联信息
     * @param bool $extend
     * @return $this
     */
    public function extend(bool $extend = true) : static
    {
        $this->options['extend'] = $extend;
        
        return $this;
    }
    
    
    /**
     * 输出场景
     * @param string|array<class-string<Model>,string> $name 场景名称
     * @param array<class-string<Model>,string>        $relation 关联场景
     * @return $this
     */
    public function scene(string|array $name, array $relation = []) : static
    {
        if (is_array($name)) {
            $relation = $name;
            $name     = $relation[static::class] ?? '';
        }
        
        $this->scene = [
            'name'     => $name,
            'relation' => $relation
        ];
        
        return $this;
    }
    
    
    /**
     * 将数据解析成Field对象集合
     * @param array|Collection $list 数据集
     * @param bool             $extend 是否解析关联数据
     * @return T[]
     */
    public function resultSetToFields(array|Collection $list, bool $extend = false) : array
    {
        $scene         = $this->scene;
        $sceneName     = $scene['name'] ?? '';
        $sceneRelation = $scene['relation'] ?? [];
        $this->scene   = [];
        
        if ($list instanceof Collection) {
            $list = $list->toArray();
        }
        
        // 关联数据
        $fieldClass = $this->getFieldClass();
        if ($extend) {
            /** @var Relation $vo */
            foreach ($fieldClass::getModelParams()['relation'] as $vo) {
                $vo->setSceneMap($sceneRelation)->handle($this, $list);
            }
        }
        
        foreach ($list as &$vo) {
            $vo = $fieldClass::parse($vo)->scene($sceneName, $sceneRelation);
        }
        
        $fieldClass::onParseList($this, $list, $extend, $sceneRelation);
        
        return $list;
    }
    
    
    /**
     * 将数据解析成Field对象
     * @param array $result 数据
     * @param bool  $extend 是否解析关联数据
     * @return T
     */
    public function resultToField(array $result, bool $extend = false) : Field
    {
        return $this->resultSetToFields([$result], $extend)[0];
    }
    
    
    /**
     * 获取单条信息
     * @param mixed $data 主键数据，支持字符、数值、数字索引数组
     * @return T|null
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function findInfo($data = null) : ?Field
    {
        $extend = $this->getOptions('extend') ?? false;
        if (!$info = $this->find($data)) {
            return null;
        }
        
        return $this->resultToField($info, $extend);
    }
    
    
    /**
     * 强制获取单条信息
     * @param mixed  $data 主键数据，支持字符、数值、数字索引数组
     * @param string $notFoundMessage 数据为空异常消息
     * @return T
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getInfo($data, $notFoundMessage = null) : Field
    {
        return $this->failException(true, $notFoundMessage)->findInfo($data);
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
    protected function ignoreException(callable $closure, string $tag = null) : mixed
    {
        try {
            return call_user_func($closure);
        } catch (Throwable $e) {
            try {
                $this->log($e, Log::WARNING, $tag);
            } catch (Throwable) {
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
    public function failException(bool $fail = true, string $notFoundMessage = null) : static
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
        static::$db->log(LogHelper::format($content, $tag, $method), $level);
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
     * @return array|null
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function find($data = null) : ?array
    {
        try {
            return parent::find($data);
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * 数据校验
     * @param array|Field                                           $data 要验证的数据
     * @param string|Validate|Closure(Validate $validate):void|null $validate 验证器/验证器类名/或验证器回调/验证场景名称
     * @param mixed                                                 $sceneName 验证场景/场景数据
     * @param mixed                                                 $sceneData 场景数据
     * @return $this
     */
    public function validate(array|Field $data, string|Validate|Closure $validate = null, mixed $sceneName = null, mixed $sceneData = null) : static
    {
        $validateCallback = null;
        
        // 如果 $data 是 Field 对象
        if ($data instanceof Field) {
            // 如果 $validate 不是闭包
            // 则 $validate 必须是场景名称, $sceneName 必须是场景数据
            if (!$validate instanceof Closure) {
                $sceneData = $sceneName;
                $sceneName = $validate;
            } else {
                $validateCallback = $validate;
            }
            
            $validate = $data::getValidate();
        } elseif (is_string($validate) && is_subclass_of($validate, Validate::class)) {
            $validate = new $validate;
        } elseif (!$validate instanceof Validate) {
            throw new ClassNotExtendsException($validate, Validate::class);
        }
        
        // 场景验证
        $sceneName = (string) $sceneName;
        $needCheck = true;
        if ($data instanceof ModelValidateInterface) {
            $result = $data->onModelValidate($this, $validate, $sceneName, $sceneData);
            if ($result === false) {
                $needCheck = false;
            } elseif (is_array($result)) {
                $data->retain($validate, ...$result);
            }
        } elseif ($sceneName) {
            $validate->scene($sceneName);
        }
        
        // 执行验证
        if ($needCheck || $validateCallback) {
            $checkData = $data;
            if ($data instanceof Field) {
                $checkData = [];
                foreach ($data::getPropertyList() as $property) {
                    if (null !== $value = ($data[$property] ?? null)) {
                        $checkData[$property] = $value;
                    }
                }
            }
            
            if ($validateCallback) {
                $validateCallback($validate);
            }
            $validate->setDb(static::$db);
            $validate->failException(true);
            $validate->check($checkData);
        }
        
        // 设置data
        $this->data($data);
        
        return $this;
    }
    
    
    /**
     * 设置数据
     * @param array|Field $data 数据
     * @return $this
     */
    public function data($data) : static
    {
        return parent::data($this->parseData($data));
    }
    
    
    /**
     * 保存记录 自动判断insert或者update
     * @param array|Field $data 数据
     * @param bool        $forceInsert 是否强制insert
     * @return int|string
     */
    public function save($data = [], bool $forceInsert = false) : int|string
    {
        return parent::save($this->parseData($data), $forceInsert);
    }
    
    
    /**
     * 更新记录
     * @param array|Field $data 更新的数据
     * @return int
     * @throws DbException
     */
    public function update(array|Field $data = []) : int
    {
        try {
            $this->triggerEvent('BeforeUpdate');
            $data = array_merge($this->options['data'] ?? [], $this->parseData($data));
            
            // 自动写入更新时间
            if ($this->autoWriteTimestamp && $this->updateTime && !isset($data[$this->updateTime])) {
                $data[$this->updateTime] = $this->autoWriteTimestamp();
            }
            
            // 只读字段不允许更新
            foreach ($this->readonly as $field) {
                if (array_key_exists($field, $data)) {
                    unset($data[$field]);
                }
            }
            
            // 设置更新数据
            $this->data($data);
            
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
     * @param bool        $getLastInsID 返回自增主键
     * @return int|string
     * @throws DbException
     */
    public function insert(array|Field $data = [], bool $getLastInsID = true) : int|string
    {
        try {
            $this->triggerEvent('BeforeInsert');
            $data = array_merge($this->options['data'] ?? [], $this->parseData($data));
            
            // 自动写入增加时间
            if ($this->autoWriteTimestamp) {
                $time = $this->autoWriteTimestamp();
                if ($this->createTime && !isset($data[$this->createTime])) {
                    $data[$this->createTime] = $time;
                }
                if ($this->autoWriteUpdateTimeSync && $this->updateTime && !isset($data[$this->updateTime])) {
                    $data[$this->updateTime] = $time;
                }
            }
            
            $this->data($data);
            
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
    public function insertGetId(array|Field $data) : int|string
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
        // 定义软删除
        if ($this->softDelete) {
            $this->withTrashed();
            
            // 不强制删除
            if (!isset($this->options['force']) || $this->options['force'] === false) {
                $this->useSoftDelete($this->getSoftDeleteField(false), time());
            }
        }
        
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
    public function value($field, $default = null) : mixed
    {
        try {
            return parent::value(Entity::parse($field), $default);
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * 得到某个列的数组
     * @param array|string  $field 字段名 多个字段用逗号分隔
     * @param string|Entity $key 索引
     * @return array
     * @throws DbException
     */
    public function column(mixed $field, string|Entity $key = '') : array
    {
        try {
            return parent::column(Entity::parse($field === true ? '*' : $field), Entity::parse($key));
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
     * 批量更新数据
     * @param array         $data 更新的数据<pre>
     * $this->updateAll([
     *     [
     *         'id'     => 1,       // 主键必选
     *         'name'   => 'test',  // 要更新的字段1
     *         'name2'  => 'test2'  // 要更新的字段2
     *     ]
     * ]);
     * </pre>
     * @param string|Entity $pk 依据$data中的哪个字段进行查询更新 如: id
     * @return int
     * @throws DbException
     */
    public function updateAll(array $data, $pk = '') : int
    {
        $pk    = $pk ?: $this->getPk();
        $list  = [];
        $range = [];
        foreach ($data as $values) {
            $values  = $this->parseData($values);
            $range[] = "'$values[$pk]'";
            foreach ($values as $field => $value) {
                if ($value instanceof Raw) {
                    $bind  = $value->getBind();
                    $value = $value->getValue();
                    $this->bindParams($value, $bind);
                }
                
                $list[$field][$values[$pk]] = $value;
            }
        }
        
        if (!$list) {
            throw new DbException('miss update condition');
        }
        
        if (!$range) {
            throw new InvalidArgumentException('Primary key field must be set');
        }
        
        $item = [];
        foreach ($list as $key => $values) {
            $item[$key] = "$key = CASE $pk " . PHP_EOL;
            foreach ($values as $field => $value) {
                if (is_array($value) && $value[0] == 'exp') {
                    $value = $value[1];
                } else {
                    $value = "'$value'";
                }
                
                $item[$key] .= "WHEN '$field' THEN $value " . PHP_EOL;
            }
            $item[$key] .= 'END ' . PHP_EOL;
        }
        
        $result = $this->execute(sprintf('UPDATE %s SET %s WHERE %s in (%s)', $this->getTable(), implode(',', $item), $pk, implode(',', $range)));
        
        // 触发回调
        if (method_exists($this, 'onUpdateAll')) {
            $this->ignoreException(function() {
                $this->onUpdateAll();
            });
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
     * 字段值增长
     * @param string|Entity $field 字段名
     * @param float|int     $step 增长值
     * @return $this
     */
    public function inc(string|Entity $field, float|int $step = 1) : static
    {
        if ($field instanceof Entity) {
            $value = $field->getValue();
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
    public function dec(string|Entity $field, float|int $step = 1) : static
    {
        if ($field instanceof Entity) {
            $value = $field->getValue();
            if (is_numeric($value) && $value > 1) {
                $step = $value;
            }
        }
        
        return parent::dec(Entity::parse($field), $step);
    }
    
    
    /**
     * 设置某个字段的值
     * @param string|Entity $field 字段名
     * @param mixed         $value 字段值
     * @return int
     * @throws DbException
     */
    public function setField(string|Entity $field, mixed $value) : int
    {
        $this->data([]);
        
        return $this->update([
            Entity::parse($field) => $value
        ]);
    }
    
    
    /**
     * 查询解析后的数据
     * @return T[]|Field[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function selectList() : array
    {
        $extend = $this->getOptions('extend') ?? false;
        
        return $this->resultSetToFields($this->select(), $extend);
    }
    
    
    /**
     * 分批查询解析数据
     * @param int                            $count 每一批查询多少条
     * @param callable(T[], Collection):bool $callback 处理回调方法，接受2个参数，$list 和 $result，返回false代表阻止继续执行
     * @param string|array|Entity|Entity[]   $column 排序依据字段，默认是主键字段
     * @param string                         $order 排序方式
     * @return bool 处理回调方法是否全部处理成功
     * @throws DbException
     */
    public function chunkList(int $count, callable $callback, $column = null, string $order = 'asc') : bool
    {
        $extend = $this->getOptions('extend') ?? false;
        
        return parent::chunk($count, function(Collection $result) use ($callback, $extend) {
            return call_user_func($callback, $this->resultSetToFields($result, $extend), $result);
        }, Entity::parse($column), $order);
    }
    
    
    /**
     * 按索引键输出数据
     * @param string|Entity $key 索引的字段，默认为 id
     * @return T[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function indexList(string|Entity $key = 'id') : array
    {
        return ArrayHelper::listByKey($this->selectList(), Entity::parse($key ?: 'id'));
    }
    
    
    /**
     * 使用IN查询并按索引键输出数据
     * @param array         $range in查询的值
     * @param string|Entity $key 索引的字段，默认为 id
     * @param string|Entity $field in查询的字段，默认为 id
     * @return T[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function indexListIn(array $range, string|Entity $key = 'id', string|Entity $field = 'id') : array
    {
        $this->where(Entity::parse($field ?: 'id'), 'in', $range);
        if ($this->fieldClass) {
            return $this->indexList($key);
        } else {
            return $this->column(true, Entity::parse($key ?: 'id'));
        }
    }
    
    
    /**
     * 获取Join别名
     * @return string
     */
    public function getAlias() : string
    {
        return ($this->options['alias'][$this->getTable()] ?? '') ?: ($this->fieldClass ? $this->fieldClass::getModelParams()['alias'] : $this->getName());
    }
    
    
    /**
     * 解析增加修改的数据
     * @param array|Field $data
     * @return array
     */
    protected function parseData($data = []) : array
    {
        if ($data instanceof Field) {
            $data = $data->getModelData();
        }
        
        $fields = $this->getTableFields();
        $list   = [];
        
        // 支持 exp 写法 及 过滤字段
        foreach ($data as $key => $value) {
            if (!in_array($key, $fields) || is_null($value)) {
                continue;
            }
            
            if ($value instanceof Entity) {
                $value = new Raw($value->field() . $value->getOp() . $value->getValue());
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
     * @inheritDoc
     */
    public function getTableFields($tableName = '') : array
    {
        if ($this->fieldClass && !$tableName) {
            return $this->fieldClass::getFieldList();
        }
        
        return parent::getTableFields($tableName);
    }
    
    
    /**
     * @inheritDoc
     */
    protected function parseWhereExp(string $logic, $field, $op, $condition, array $param = [], bool $strict = false) : static
    {
        if ($field instanceof Entity) {
            $value = $field->getValue();
            if ($value instanceof Entity) {
                return $this->whereRaw(sprintf('`%s` %s `%s`', $field->field(), $field->getOp(), $value->field()));
            }
            
            $condition = $condition ?: $field->getValue();
            $op        = $op ?: $field->getOp();
            $field     = $field->field();
        }
        
        return parent::parseWhereExp($logic, $field, $op, $condition, $param, $strict);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereNull(string|Entity $field, string $logic = 'AND') : static
    {
        return parent::whereNull(Entity::parse($field), $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereNotNull(string|Entity $field, string $logic = 'AND') : static
    {
        return parent::whereNotNull(Entity::parse($field), $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereIn(string|Entity $field, $condition, string $logic = 'AND') : static
    {
        return parent::whereIn(Entity::parse($field), $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereNotIn(string|Entity $field, $condition, string $logic = 'AND') : static
    {
        return parent::whereNotIn(Entity::parse($field), $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereLike(string|Entity $field, $condition, string $logic = 'AND') : static
    {
        return parent::whereLike(Entity::parse($field), $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereNotLike(string|Entity $field, $condition, string $logic = 'AND') : static
    {
        return parent::whereNotLike(Entity::parse($field), $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereBetween(string|Entity $field, $condition, string $logic = 'AND') : static
    {
        return parent::whereBetween(Entity::parse($field), $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereNotBetween(string|Entity $field, $condition, string $logic = 'AND') : static
    {
        return parent::whereNotBetween(Entity::parse($field), $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereFindInSet(string|Entity $field, $condition, string $logic = 'AND') : static
    {
        return parent::whereFindInSet(Entity::parse($field), $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereColumn(string|Entity $field1, string $operator, string|Entity $field2 = null, string $logic = 'AND') : static
    {
        return parent::whereColumn(Entity::parse($field1), $operator, Entity::parse($field2), $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function useSoftDelete(string|Entity $field, $condition = null) : static
    {
        return parent::useSoftDelete(Entity::parse($field), $condition);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereExp(string|Entity $field, string $where, array $bind = [], string $logic = 'AND') : static
    {
        return parent::whereExp(Entity::parse($field), $where, $bind, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function whereFieldRaw(string|Entity $field, $op, $condition = null, string $logic = 'AND') : static
    {
        return parent::whereFieldRaw(Entity::parse($field), $op, $condition, $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function removeWhereField(string|Entity $field, string $logic = 'AND') : static
    {
        return parent::removeWhereField(Entity::parse($field), $logic);
    }
    
    
    /**
     * @inheritDoc
     */
    public function join($join, $condition = null, string $type = 'INNER', array $bind = []) : static
    {
        if ($join instanceof Model) {
            $model = $join;
            $join  = [$model->getTable() => $model->getAlias()];
        } elseif (is_string($join) && is_subclass_of($join, Model::class)) {
            $model = $join::init();
            $join  = [$model->getTable() => $model->getAlias()];
        }
        
        if ($condition instanceof Entity) {
            $value = $condition->getValue();
            if ($value instanceof Entity) {
                $value = $value->field();
            }
            
            $condition = $condition->field() . $condition->getOp() . $value;
        } elseif ($condition instanceof Closure) {
            $condition($clause = new JoinClause());
            $condition = $clause->build();
        }
        
        return parent::join($join, $condition, $type, $bind);
    }
    
    
    /**
     * @inheritDoc
     */
    public function leftJoin($join, $condition = null, array $bind = []) : static
    {
        return parent::leftJoin($join, $condition, $bind);
    }
    
    
    /**
     * @inheritDoc
     */
    public function rightJoin($join, $condition = null, array $bind = []) : static
    {
        return parent::rightJoin($join, $condition, $bind);
    }
    
    
    /**
     * @inheritDoc
     */
    public function fullJoin($join, $condition = null, array $bind = []) : static
    {
        return parent::fullJoin($join, $condition, $bind);
    }
    
    
    /**
     * @inheritDoc
     */
    public function order($field, string $order = '') : static
    {
        return parent::order(Entity::parse($field), $order);
    }
    
    
    /**
     * @inheritDoc
     */
    public function field($field) : static
    {
        return parent::field(Entity::parse($field));
    }
    
    
    /**
     * @inheritDoc
     */
    public function group($group) : static
    {
        return parent::group(Entity::parse($group));
    }
    
    
    /**
     * 指定having查询
     * @param string|Entity $having having
     */
    public function having($having) : static
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
    public function whereTimeIntervalRange(string|Entity $field, string|int $startOrTimeRange = 0, string|int $endOrSpace = 0, bool $split = false) : static
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
    public function transaction(callable $callback, bool $disabled = false, string $alias = '') : mixed
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
    
    
    /**
     * 获取软删除数据的查询条件
     * @return array
     */
    protected function getWithTrashedExp() : array
    {
        return is_null($this->softDeleteDefault) ? ['notnull', ''] : ['<>', $this->softDeleteDefault];
    }
    
    
    /**
     * 获取软删除字段
     * @param bool $read 是否查询操作 写操作的时候会自动去掉表别名
     * @return string
     */
    protected function getSoftDeleteField(bool $read = false) : string
    {
        $field = $this->softDeleteField ?: 'delete_time';
        if (!str_contains($field, '.')) {
            $field = '__TABLE__.' . $field;
        }
        
        if (!$read && strpos($field, '.')) {
            $array = explode('.', $field);
            $field = array_pop($array);
        }
        
        return $field;
    }
    
    
    /**
     * @inheritDoc
     */
    public function parseOptions() : array
    {
        // 软删除
        if ($this->softDelete && !isset($this->options['with_trashed']) && !isset($this->options['soft_delete'])) {
            $this->withNoTrashed();
        }
        
        // 移除强制删除标识
        if (isset($this->options['force']) && is_bool($this->options['force'])) {
            unset($this->options['force']);
        }
        
        // join自动加别名
        if (isset($this->options['join']) && $this->options['join']) {
            $this->alias($this->getAlias());
        }
        
        return parent::parseOptions();
    }
    
    
    /**
     * 查询不包含软删除的数据
     * @return $this
     */
    public function withNoTrashed() : static
    {
        if ($this->softDelete) {
            $condition = is_null($this->softDeleteDefault) ? ['null', ''] : ['=', $this->softDeleteDefault];
            $this->useSoftDelete($this->getSoftDeleteField(true), $condition);
        }
        
        return $this;
    }
    
    
    /**
     * 查询包含软删除的数据
     * @return $this
     */
    public function withTrashed() : static
    {
        if ($this->softDelete) {
            $this->options['with_trashed'] = true;
        }
        
        return $this;
    }
    
    
    /**
     * 仅查询软删除的数据
     * @return $this
     */
    public function onlyTrashed() : static
    {
        if ($this->softDelete) {
            $this->useSoftDelete($this->getSoftDeleteField(true), $this->getWithTrashedExp());
        }
        
        return $this;
    }
    
    
    /**
     * 恢复被软删除的数据
     * @param mixed $data 表达式 true 表示强制删除
     * @return int
     * @throws DbException
     */
    public function restore($data) : int
    {
        if (!$this->softDelete) {
            return 0;
        }
        
        try {
            // AR模式分析主键条件
            if (!is_null($data) && true !== $data) {
                $this->parsePkWhere($data);
            }
            
            // 如果条件为空 不进行删除操作 除非设置 1=1
            if (true !== $data && empty($this->options['where'])) {
                throw new DbException('delete without condition');
            }
            
            $this->options['data'] = [$this->getSoftDeleteField(false) => $this->softDeleteDefault];
            
            return $this->onlyTrashed()->connection->update($this);
        } finally {
            $this->removeOption();
        }
    }
    
    
    /**
     * 指定强制索引或强制删除(忽略软删除)
     * @param string|bool $force 索引名称或强制删除
     * @return $this
     */
    public function force(string|bool $force = true) : static
    {
        $this->options['force'] = $force;
        
        return $this;
    }
}