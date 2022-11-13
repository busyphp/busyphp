<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\js\driver;

use BusyPHP\app\admin\js\Driver;
use BusyPHP\app\admin\js\driver\table\TableHandler;
use BusyPHP\helper\FilterHelper;
use BusyPHP\Model;
use BusyPHP\model\ArrayOption;
use BusyPHP\model\Entity;
use Closure;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * JS组件[busyAdmin.plugins.Table]
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/12 13:33 Table.php $
 * @property TableHandler $handler
 * @method Table handler(TableHandler $handler)
 */
class Table extends Driver
{
    /**
     * 排序字段
     * @var string
     */
    protected $orderField;
    
    /**
     * 排序方式
     * @var string
     */
    protected $orderType;
    
    /**
     * 偏移量
     * @var int
     */
    protected $offset;
    
    /**
     * 每页显示条数
     * @var int
     */
    protected $limit;
    
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
     * 是否查询扩展数据
     * @var bool
     */
    protected $extend;
    
    /**
     * 查询字段选项
     * @var ArrayOption
     */
    protected $map;
    
    /**
     * 是否精确搜索
     * @var bool
     */
    protected $accurate;
    
    /**
     * 允许参与搜索的字段
     * @var array
     */
    protected $searchable;
    
    /**
     * 数据集
     * @var array|Collection|null
     */
    protected $list = null;
    
    /**
     * 字段处理回调
     * @var callable($model Model, $field string, $word string, $op string, $source string):mixed
     */
    protected $fieldCallback;
    
    /**
     * 数据集处理回调
     * @var callable($list array):array|void
     */
    protected $listCallback;
    
    /**
     * 查询处理回调
     * @var null|callable($model Model, $option ArrayOption):void
     */
    protected $queryCallback;
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->extend     = $this->request->param('extend/b', false);
        $this->limit      = $this->request->param('limit/d', 0);
        $this->offset     = $this->request->param('offset/d', 0);
        $this->word       = $this->request->param('word/s', '', 'trim');
        $this->field      = $this->request->param('field', '', 'trim');
        $this->accurate   = $this->request->param('accurate/b', false);
        $this->orderType  = $this->request->param('order/s', '', 'trim');
        $this->orderField = $this->request->param('sort/s', '', 'trim');
        $this->searchable = $this->request->param('searchable/a', []);
        
        $this->orderType  = $this->orderType ?: 'desc';
        $this->orderField = $this->orderField ?: 'id';
        
        // 查询的条件
        $whereData = $this->request->param('static/a', []);
        foreach ($whereData as $key => $value) {
            $whereData[$key] = trim($value);
        }
        $this->map = ArrayOption::init($whereData);
        
        // 扩展搜索词
        if ($this->word === '') {
            $this->word = $this->request->param('search/s', '', 'trim');
        }
    }
    
    
    /**
     * 指定数据集
     * @param array|Collection|callable($list array):array $list 数据集或处理回调
     * @param null|callable($list array):array|void        $callback 处理回调
     * @return $this
     */
    public function list($list, callable $callback = null) : self
    {
        if ($list instanceof Closure) {
            $callback = $list;
            $list     = null;
        }
        
        $this->list         = $list;
        $this->listCallback = $callback;
        
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
     * 指定搜索字段或搜索字段处理回调
     * @param string|Entity|callable($model Model, $field string, $word string, $op string, $source string):mixed $field 字段名称或处理回调
     * @param null|callable($model Model, $field string, $word string, $op string, $source string):mixed          $callback 处理回调
     * @return $this
     */
    public function field($field, callable $callback = null) : self
    {
        if ($callback) {
            $this->fieldCallback = $callback;
            if ($field) {
                $this->field = (string) $this->field;
            }
        } elseif ($field instanceof Closure) {
            $this->fieldCallback = $field;
        }
        
        return $this;
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
     * 是否精确搜索
     * @return bool
     */
    public function isAccurate() : bool
    {
        return $this->accurate;
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
     * 是否查询扩展数据
     * @return bool
     */
    public function isExtend() : bool
    {
        return $this->extend;
    }
    
    
    /**
     * 设置查询关键词
     * @param string $word
     * @return $this
     */
    public function setWord(string $word) : self
    {
        $this->word = $word;
        
        return $this;
    }
    
    
    /**
     * 获取查询关键词
     * @return string
     */
    public function getWord() : string
    {
        return $this->word;
    }
    
    
    /**
     * 设置排序字段
     * @param string|Entity $orderField
     * @return $this
     */
    public function setOrderField($orderField) : self
    {
        $this->orderField = (string) $orderField;
        
        return $this;
    }
    
    
    /**
     * 获取排序字段
     * @return string
     */
    public function getOrderField() : string
    {
        return $this->orderField;
    }
    
    
    /**
     * 设置排序方式
     * @param string $orderType
     * @return $this
     */
    public function setOrderType(string $orderType) : self
    {
        $this->orderType = $orderType;
        
        return $this;
    }
    
    
    /**
     * 获取排序方式
     * @return string
     */
    public function getOrderType() : string
    {
        return $this->orderType;
    }
    
    
    /**
     * 设置查询限制条数，设为0则不限
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit) : self
    {
        $this->limit = $limit;
        
        return $this;
    }
    
    
    /**
     * 获取查询限制条数
     * @return int
     */
    public function getLimit() : int
    {
        return $this->limit;
    }
    
    
    /**
     * 设置查询偏移量
     * @param int $offset
     * @return $this
     */
    public function setOffset(int $offset) : self
    {
        $this->offset = $offset;
        
        return $this;
    }
    
    
    /**
     * 获取查询偏移量
     * @return int
     */
    public function getOffset() : int
    {
        return $this->offset;
    }
    
    
    /**
     * 设置参与搜索的字段
     * @param array|string|Entity $searchable
     * @param bool                $merge 是否合并
     * @return $this
     */
    public function setSearchable($searchable, bool $merge = true) : self
    {
        if (!is_array($searchable)) {
            $searchable = [$searchable];
        }
        
        $searchable = Entity::parse($searchable);
        if ($merge) {
            $this->searchable = array_merge($this->searchable, $searchable);
        } else {
            $this->searchable = $merge;
        }
        
        return $this;
    }
    
    
    /**
     * 获取参与搜索的字段
     * @return array
     */
    public function getSearchable() : array
    {
        return $this->searchable;
    }
    
    
    /**
     * @inheritDoc
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function build() : ?array
    {
        $total = false;
        if ($this->handler) {
            $this->handler->prepare($this);
        }
        
        // 查询模型
        if ($this->model && is_null($this->list)) {
            $this->buildCondition();
            
            // 限制条数
            if ($this->limit > 0) {
                $totalModel = clone $this->model;
                $total      = $totalModel->count();
                $this->model->limit($this->offset, $this->limit);
            }
            
            // 查询扩展信息
            if ($this->extend) {
                $this->list = $this->model->selectExtendList();
            } else {
                $this->list = $this->model->selectList();
            }
        }
        
        if (is_null($this->list)) {
            return null;
        }
        
        // 数据集处理
        $list = null;
        if ($this->handler) {
            $list = $this->handler->list($this->list);
        } elseif ($this->listCallback) {
            $list = call_user_func_array($this->listCallback, [&$this->list]);
        }
        if (is_array($list) || $list instanceof Collection) {
            $this->list = $list;
        }
        
        $total = $total === false ? count($this->list) : $total;
        
        return [
            'total'            => $total,
            'totalNotFiltered' => $total,
            'rows'             => $this->list,
        ];
    }
    
    
    /**
     * 构建查询条件
     */
    public function buildCondition() : self
    {
        if ($this->handler) {
            $this->handler->prepare($this);
        }
        
        if (!$this->model) {
            return $this;
        }
        
        // 指定字段搜索
        if ($this->word !== '') {
            $sourceWord = $this->word;
            $likeWord   = '%' . FilterHelper::searchWord($sourceWord) . '%';
            
            // 多字段搜索
            if ($this->searchable) {
                $searchable = [];
                foreach ($this->searchable as $field) {
                    if ($field = $this->handleField($field, false, $sourceWord, $likeWord)) {
                        $searchable[] = $field;
                    }
                }
                
                if ($searchable) {
                    $this->model->where(function(Model $model) use ($searchable, $likeWord) {
                        foreach ($searchable as $field) {
                            $model->whereLike($field, $likeWord);
                        }
                    });
                }
            }
            
            // has field
            // 指定字段搜索
            elseif ($this->field && $field = $this->handleField($this->field, $this->accurate, $sourceWord, $likeWord)) {
                if ($this->accurate) {
                    $this->model->where($field, '=', $sourceWord);
                } else {
                    $this->model->whereLike($field, $likeWord);
                }
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
        
        // 排序
        if ($this->orderField && $this->orderType && !$this->model->getOptions('order')) {
            $this->model->order($this->castField($this->orderField), $this->orderType);
        }
        
        return $this;
    }
    
    
    /**
     * 获取 Model 的 where 查询条件
     * @return array
     */
    public function getModelWhere() : array
    {
        if (!$this->model) {
            return [];
        }
        
        return $this->model->getOptions('where') ?? [];
    }
    
    
    /**
     * 获取 Model 的 order 查询条件
     * @return array
     */
    public function getModelOrder() : array
    {
        if (!$this->model) {
            return [];
        }
        
        return $this->model->getOptions('order') ?? [];
    }
    
    
    /**
     * 获取 Model 的所有查询条件
     * @return array
     */
    public function getModelOptions() : array
    {
        if (!$this->model) {
            return [];
        }
        
        return $this->model->getOptions();
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
        
        if (!$field) {
            return null;
        }
        
        return $this->castField($field);
    }
}