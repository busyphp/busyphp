<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\common;

use BusyPHP\App;
use BusyPHP\app\admin\component\common\SimpleQuery\SimpleQueryHandler;
use BusyPHP\app\admin\component\common\SimpleQuery\SimpleQueryBuildResult;
use BusyPHP\app\admin\component\js\traits\ModelOrder;
use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\helper\FilterHelper;
use BusyPHP\Model;
use BusyPHP\model\ArrayOption;
use BusyPHP\model\Entity;
use BusyPHP\Request;
use Closure;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Paginator;

/**
 * 简单快捷的数据查询器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/15 19:56 SimpleQuery.php $
 */
class SimpleQuery
{
    use ModelOrder;
    
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var Model
     */
    protected $model;
    
    /**
     * 搜索的字段
     * @var string
     */
    protected $field;
    
    /**
     * 搜索的内容
     * @var string
     */
    protected $word;
    
    /**
     * 查询字段键值对
     * @var ArrayOption
     */
    protected $map;
    
    /**
     * 是否精确搜索
     * @var bool
     */
    protected $accurate;
    
    /**
     * 分页码
     * @var int
     */
    protected $page;
    
    /**
     * 是否查询扩展数据
     * @var bool
     */
    protected $extend;
    
    /**
     * 每页显示条数
     * @var int
     */
    protected $limit = 20;
    
    /**
     * 是否启用最多查询，即$limit设置多少就最多查多少
     * @var bool
     */
    protected $maxLimit = false;
    
    /**
     * 是否使用简洁分页
     * @var bool
     */
    protected $simple = false;
    
    /**
     * 字段处理回调
     * @var callable($model Model, $field string, $word string, $op string, $source string):mixed
     */
    protected $fieldCallback;
    
    /**
     * 查询处理回调
     * @var null|callable($model Model, $option ArrayOption):void
     */
    protected $queryCallback;
    
    /**
     * 数据集处理回调
     * @var callable($list array|Collection):mixed
     */
    protected $listCallback;
    
    /**
     * 处理回调
     * @var SimpleQueryHandler
     */
    protected $handler;
    
    /**
     * 分页驱动类
     * @var class-string<Paginator>
     */
    protected $paginator;
    
    
    public function __construct(Model $model)
    {
        $this->model   = $model;
        $this->request = App::getInstance()->request;
        
        $this->word     = $this->request->get('word/s', '', 'trim');
        $this->field    = $this->request->get('field/s', '', 'trim');
        $this->accurate = $this->request->get('accurate/b', false);
        $this->limit    = $this->request->get('limit/d', $this->limit);
        $this->order    = $this->request->get('order/s', $this->order, 'trim');
        $this->page     = max($this->request->get('page/d', 1), 1);
        
        // 查询的条件
        $data = $this->request->get('static/a', []);
        foreach ($data as $key => $value) {
            $data[$key] = trim($value);
        }
        $this->map = ArrayOption::init($data);
    }
    
    
    /**
     * 指定处理回调
     * @param SimpleQueryHandler $handler
     * @return $this
     */
    public function handler(SimpleQueryHandler $handler)
    {
        $this->handler = $handler;
        
        return $this;
    }
    
    
    /**
     * 指定搜索字段或搜索字段处理回调
     * @param string|Entity|callable($model Model, $field string, $word string, $op string, $source string):mixed $field 字段名称或处理回调
     * @param null|callable($model Model, $field string, $word string, $op string, $source string):mixed          $callback 处理回调
     * @return $this
     */
    public function field($field, callable $callback = null) : self
    {
        if ($field instanceof Closure) {
            $this->fieldCallback = $field;
        } else {
            $this->fieldCallback = $callback;
            if ($field) {
                $this->field = (string) $field;
            }
        }
        
        return $this;
    }
    
    
    /**
     * 指定查询处理回调
     * @param null|callable($model Model, $option ArrayOption):void $callback 查询处理回调
     * @return $this
     */
    public function query(callable $callback) : self
    {
        $this->queryCallback = $callback;
        
        return $this;
    }
    
    
    /**
     * 指定数据集处理回调
     * @param callable($list array|Collection):mixed $callback 数据集处理回调
     * @return $this
     */
    public function list(callable $callback)
    {
        $this->listCallback = $callback;
        
        return $this;
    }
    
    
    /**
     * 设置是否简洁模式分页
     * @param bool $simple
     * @return $this
     */
    public function simple(bool $simple) : self
    {
        $this->simple = $simple;
        
        return $this;
    }
    
    
    /**
     * 设置分页驱动类
     * @param class-string<Paginator> $paginator
     * @return $this
     */
    public function paginator(string $paginator) : self
    {
        if (!is_subclass_of($this->paginator, Paginator::class)) {
            throw new ClassNotExtendsException($paginator, Paginator::class);
        }
        
        $this->paginator = $paginator;
        
        return $this;
    }
    
    
    /**
     * 获取搜索关键词
     * @return string
     */
    public function getWord() : string
    {
        return $this->word;
    }
    
    
    /**
     * 设置搜索关键词
     * @param string $word
     * @return $this
     */
    public function setWord(string $word) : self
    {
        $this->word = $word;
        
        return $this;
    }
    
    
    /**
     * 是否精确搜索
     * @return bool
     */
    public function isAccurate() : bool
    {
        return $this->accurate;
    }
    
    
    /**
     * 设置是否精确搜索
     * @param bool $accurate
     * @return $this
     */
    public function setAccurate(bool $accurate) : self
    {
        $this->accurate = $accurate;
        
        return $this;
    }
    
    
    /**
     * 获取页码
     * @return int
     */
    public function getPage() : int
    {
        return $this->page;
    }
    
    
    /**
     * 设置页码
     * @param int $page
     * @return $this
     */
    public function setPage(int $page) : self
    {
        $this->page = $page;
        
        return $this;
    }
    
    
    /**
     * 是否查询扩展数据
     * @return bool
     */
    public function isExtend() : bool
    {
        return $this->extend;
    }
    
    
    /**
     * 设置是否查询扩展数据
     * @param bool $extend
     * @return $this
     */
    public function setExtend(bool $extend) : self
    {
        $this->extend = $extend;
        
        return $this;
    }
    
    
    /**
     * 获取每页显示条数
     * @return int
     */
    public function getLimit() : int
    {
        return $this->limit;
    }
    
    
    /**
     * 设置每页显示条数
     * @param int  $limit 每页查询条数，0为全部
     * @param bool $max 是否启用最多查询，即$limit设置多少就最多查多少
     * @return $this
     */
    public function setLimit(int $limit, bool $max = false) : self
    {
        $this->limit    = $limit;
        $this->maxLimit = $max;
        
        return $this;
    }
    
    
    /**
     * 构建数据
     * @return SimpleQueryBuildResult
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function build() : SimpleQueryBuildResult
    {
        $this->buildCondition();
        
        // 限制条数
        $total = 0;
        if ($this->limit > 0) {
            if ($this->maxLimit) {
                $this->model->limit($this->limit);
            } elseif ($this->simple) {
                $this->model->limit(($this->page - 1) * $this->limit, $this->limit + 1);
            } else {
                $totalModel = clone $this->model;
                $totalModel->removeOption('order');
                $totalModel->removeOption('limit');
                $totalModel->removeOption('page');
                $totalModel->removeOption('field');
                $total = $totalModel->count();
                $this->model->page($this->page, $this->limit);
            }
        }
        
        $list = $this->extend ? $this->model->selectExtendList() : $this->model->selectList();
        
        // 数据集处理回调
        $result = null;
        if ($this->handler) {
            $result = $this->handler->list($list);
        } elseif ($this->listCallback) {
            $result = call_user_func_array($this->listCallback, [&$list]);
        }
        if (is_array($result) || $result instanceof Collection) {
            $list = $result;
        }
        
        return new SimpleQueryBuildResult(
            $list,
            $this->limit,
            $this->page,
            $total,
            $this->simple,
            $this->paginator
        );
    }
    
    
    /**
     * 构建查询条件
     * @return $this
     */
    public function buildCondition() : self
    {
        if ($this->handler) {
            $this->handler->prepare($this, $this->model);
        }
        
        // 搜索关键词
        $likeWord = '%' . FilterHelper::searchWord($this->word) . '%';
        if ($this->word !== '' && $this->field && $field = $this->handleField($this->field, $this->accurate, $this->word, $likeWord)) {
            if ($this->accurate) {
                $this->model->where($field, '=', $this->word);
            } else {
                $this->model->whereLike($field, $likeWord);
            }
        }
        
        // 处理查询条件
        if ($this->handler) {
            $this->handler->query($this->map);
        } elseif ($this->queryCallback) {
            call_user_func_array($this->queryCallback, [$this->model, $this->map]);
        }
        
        // 将未处理的条件按照 field = value 进行查询
        foreach ($this->map as $field => $value) {
            if (is_null($value)) {
                continue;
            }
            
            if ($field = $this->handleField($field, true, $value, '%' . FilterHelper::searchWord($value) . '%')) {
                $this->model->where($field, $value);
            }
        }
        
        return $this->modelOrder();
    }
    
    
    /**
     * 处理字段
     * @param string $field 处理的字段
     * @param bool   $accurate 是否精确搜索
     * @param string $sourceWord 未处理的关键词
     * @param string $likeWord 模糊查询的关键词
     * @return mixed
     */
    protected function handleField(string $field, bool $accurate, string $sourceWord, string $likeWord)
    {
        $op   = 'like';
        $word = $likeWord;
        if ($accurate) {
            $op   = '=';
            $word = $sourceWord;
        }
        
        // 触发字段处理回调
        if ($this->handler) {
            $field = $this->handler->field($field, $op, $word);
        } elseif ($this->fieldCallback) {
            $field = call_user_func_array($this->fieldCallback, [
                $this->model,
                $field,
                $op,
                $word,
                $sourceWord
            ]);
        }
        
        return $field;
    }
    
    
    /**
     * 实例化
     * @param Model $model
     * @return static
     */
    public static function init(Model $model)
    {
        return new static($model);
    }
}