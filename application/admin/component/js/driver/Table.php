<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\driver;

use BusyPHP\app\admin\component\js\Driver;
use BusyPHP\app\admin\component\js\driver\table\TableHandler;
use BusyPHP\app\admin\component\js\traits\Lists;
use BusyPHP\app\admin\component\js\traits\ModelSelect;
use BusyPHP\app\admin\component\js\traits\ModelTotal;
use BusyPHP\helper\FilterHelper;
use BusyPHP\interfaces\ContainerInterface;
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
class Table extends Driver implements ContainerInterface
{
    use ModelSelect;
    use ModelTotal;
    use Lists;
    
    /**
     * 排序字段
     * @var string
     */
    protected string $orderField;
    
    /**
     * 排序方式
     * @var string
     */
    protected string $orderType;
    
    /**
     * 偏移量
     * @var int
     */
    protected int $offset;
    
    /**
     * 每页显示条数
     * @var int
     */
    protected int $limit;
    
    /**
     * 是否启用最多查询，即$limit设置多少就最多查多少
     * @var bool
     */
    protected bool $maxLimit = false;
    
    /**
     * 搜索的字段
     * @var string
     */
    protected string $field;
    
    /**
     * 搜索的内容
     * @var string
     */
    protected string $word;
    
    /**
     * 查询字段选项
     * @var ArrayOption
     */
    protected ArrayOption $map;
    
    /**
     * 是否精确搜索
     * @var bool
     */
    protected bool $accurate;
    
    /**
     * 允许参与搜索的字段
     * @var array
     */
    protected array $searchable;
    
    /**
     * 字段处理回调
     * @var Closure|null
     */
    protected ?Closure $fieldCallback = null;
    
    /**
     * 查询处理回调
     * @var Closure|null
     */
    protected ?Closure $queryCallback = null;
    
    /**
     * 请求类型
     * @var string
     */
    protected string $action;
    
    /**
     * Tree 子节点是否懒加载
     * @var bool
     */
    protected bool $treeLazy;
    
    /**
     * Tree 节点ID字段名
     * @var string
     */
    protected string $treeIdField;
    
    /**
     * Tree 子节点定义的父节点ID的字段名
     * @var string
     */
    protected string $treeParentField;
    
    /**
     * Tree 根节点默认值
     * @var string
     */
    protected string $treeParentRoot;
    
    /**
     * Tree 子节点的父节点ID
     * @var string
     */
    protected string $treeParentId;
    
    /**
     * Tree 当前节点是否还有子节点的字段名
     * @var string
     */
    protected string $treeHasChildrenField;
    
    /**
     * Tree 当前数据集中是否已包含 {@see Table::$treeHasChildrenField} 字段
     * @var bool
     */
    protected bool $treeAlreadyHaveHasChildrenField = false;
    
    /**
     * Tree 是否需要将节点的父ID清理为根节点默认值
     * @var bool
     */
    protected bool $treeNeedClearParentToRoot = false;
    
    /**
     * 查询根节点处理回调
     * @var Closure|null
     */
    protected ?Closure $treeLazyRootQueryCallback = null;
    
    
    final public static function defineContainer() : string
    {
        return self::class;
    }
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->limit      = $this->request->param('limit/d', 0);
        $this->maxLimit   = $this->request->param('max_limit/b', false);
        $this->offset     = $this->request->param('offset/d', 0);
        $this->word       = $this->request->param('word/s', '', 'trim');
        $this->field      = $this->request->param('field', '', 'trim');
        $this->accurate   = $this->request->param('accurate/b', false);
        $this->orderType  = $this->request->param('order/s', '', 'trim');
        $this->orderField = $this->request->param('sort/s', '', 'trim');
        $this->searchable = $this->request->param('searchable/a', []);
        
        $this->orderType  = $this->orderType ?: 'desc';
        $this->orderField = $this->orderField ?: 'id';
        
        $this->action               = $this->request->param('action/s', '', 'trim');
        $this->treeParentField      = $this->request->param('tree_parent_field/s', '', 'trim');
        $this->treeParentRoot       = $this->request->param('tree_parent_root/s', '', 'trim');
        $this->treeParentId         = $this->request->param('tree_parent_id/s', '', 'trim');
        $this->treeIdField          = $this->request->param('tree_id_field/s', '', 'trim');
        $this->treeHasChildrenField = $this->request->param('tree_has_children_field/s', '', 'trim');
        $this->treeLazy             = $this->request->param('tree_lazy/b', false);
        
        $this->treeIdField          = $this->treeIdField ?: 'id';
        $this->treeParentField      = $this->treeParentField ?: 'parent_id';
        $this->treeHasChildrenField = $this->treeHasChildrenField ?: 'has_children';
        
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
     * @param array|Collection|Closure(array $list):array $list 数据集或处理回调
     * @param null|Closure(array $list):array|void        $listCallback 处理回调，回调参数：<p>
     * - {@see array} $list 处理的数据<br />
     * <b>示例：</b>
     * <pre>
     * $this->list(function({@see array} $list) {
     *      return $list;
     * })
     * </pre>
     * </p>
     * @return static
     */
    public function list(array|Collection|Closure $list, ?Closure $listCallback = null) : static
    {
        if ($list instanceof Closure) {
            $listCallback = $list;
            $list         = null;
        }
        
        $this->list         = $list;
        $this->listCallback = $listCallback;
        
        return $this;
    }
    
    
    /**
     * 指定查询处理回调
     * @param Closure(Model $model, ArrayOption $option):void $callback 查询处理回调，回调参数：<p>
     * - {@see Model} $model 模型<br />
     * - {@see ArrayOption} $option 查询选项<br />
     * <b>示例：</b>
     * <pre>
     * $this->query(function({@see Model} $model, {@see ArrayOption} $option) {
     * })
     * </pre>
     * </p>
     * @return static
     */
    public function query(Closure $callback) : static
    {
        $this->queryCallback = $callback;
        
        return $this;
    }
    
    
    /**
     * 指定搜索字段或搜索字段处理回调
     * @param string|Entity|Closure(Model $model, string $field, string $word, string $op, string $source):mixed $field 字段名称或处理回调
     * @param null|Closure(Model $model, string $field, string $word, string $op, string $source):mixed          $callback 处理回调，回调参数：<p>
     * - {@see Model} $model 模型<br />
     * - {@see string} $field 字段<br />
     * - {@see string} $word 搜索关键词<br />
     * - {@see string} $op 搜索条件<br />
     * - {@see string} $source 搜索词原文<br />
     * <b>示例：</b>
     * <pre>
     * $this->fieldQuery(function({@see Model} $model, {@see string} $field, {@see string} $word, {@see string} $op, {@see string} $source) {
     *      // 返回false代表阻止系统处理字段查询处理
     *      return false;
     * })
     * </pre>
     * </p>
     * @return static
     */
    public function fieldQuery(string|Entity|Closure $field, Closure $callback = null) : static
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
     * 处理字段
     * @param string $field 处理的字段
     * @param bool   $accurate 是否精确搜索
     * @param string $sourceWord 未处理的关键词
     * @param string $likeWord 模糊查询的关键词
     * @return mixed
     */
    protected function handleField(string $field, bool $accurate, string $sourceWord, string $likeWord) : mixed
    {
        $op   = 'like';
        $word = $likeWord;
        if ($accurate) {
            $op   = '=';
            $word = $sourceWord;
        }
        
        // 触发字段处理回调
        if ($this->handler) {
            $field = $this->handler->fieldQuery($field, $op, $word);
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
     * 设置是否精确搜索
     * @param bool $accurate
     * @return static
     */
    public function setAccurate(bool $accurate) : static
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
     * 设置查询关键词
     * @param string $word
     * @return static
     */
    public function setWord(string $word) : static
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
     * @return static
     */
    public function setOrderField(string|Entity $orderField) : static
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
     * @return static
     */
    public function setOrderType(string $orderType) : static
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
     * 设置查询限制条数
     * @param int  $limit 每页查询条数，0为全部
     * @param bool $max 是否启用最多查询，即$limit设置多少就最多查多少
     * @return static
     */
    public function setLimit(int $limit, bool $max = false) : static
    {
        $this->limit    = $limit;
        $this->maxLimit = $max;
        
        return $this;
    }
    
    
    /**
     * 是否启用最多查询，即$limit设置多少就最多查多少
     * @return bool
     */
    public function isMaxLimit() : bool
    {
        return $this->maxLimit;
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
     * @return static
     */
    public function setOffset(int $offset) : static
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
     * @return static
     */
    public function setSearchable(array|string|Entity $searchable, bool $merge = true) : static
    {
        if (!is_array($searchable)) {
            $searchable = [$searchable];
        }
        
        $searchable = Entity::parse($searchable);
        if ($merge) {
            $this->searchable = array_merge($this->searchable, $searchable);
        } else {
            $this->searchable = $searchable;
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
     * Tree 是否懒加载子节点
     * @return bool
     */
    public function isTreeLazy() : bool
    {
        return $this->treeLazy;
    }
    
    
    /**
     * Tree 是否请求子节点
     * @return bool
     */
    public function isGetTreeChildren() : bool
    {
        return $this->action === 'get_children';
    }
    
    
    /**
     * Tree 设置数据集合中是否已包含 hasChildren 字段
     * @param bool $treeAlreadyHaveHasChildrenField
     * @return $this
     */
    public function setTreeAlreadyHaveHasChildrenField(bool $treeAlreadyHaveHasChildrenField) : static
    {
        $this->treeAlreadyHaveHasChildrenField = $treeAlreadyHaveHasChildrenField;
        
        return $this;
    }
    
    
    /**
     * Tree 获取当前节点是否还有子节点的字段名
     * @return string
     */
    public function getTreeHasChildrenField() : string
    {
        return $this->treeHasChildrenField;
    }
    
    
    /**
     * Tree 获取节点ID字段名
     * @return string
     */
    public function getTreeIdField() : string
    {
        return $this->treeIdField;
    }
    
    
    /**
     * Tree 获取子节点的父节点ID
     * @return string
     */
    public function getTreeParentId() : string
    {
        return $this->treeParentId;
    }
    
    
    /**
     * Tree 获取子节点定义的父节点ID的字段名
     * @return string
     */
    public function getTreeParentField() : string
    {
        return $this->treeParentField;
    }
    
    
    /**
     * Tree 获取根节点默认值
     * @return string
     */
    public function getTreeParentRoot() : string
    {
        return $this->treeParentRoot;
    }
    
    
    /**
     * Tree 设置根节点查询处理
     * @param Closure(string $field, string $root):mixed $callback 处理回调，回调参数：<p>
     * - {@see string} $field 字段名称<br />
     * - {@see string} $root 根节点默认值<br /><br />
     * <b>示例：</b>
     * <pre>
     * $this->treeLazyRootQuery(function(string $field, string $root) {
     *      // 返回false代表阻止系统处理根节点查询处理
     *      return false;
     * })
     * </pre>
     * </p>
     * @return static
     */
    public function treeLazyRootQuery(Closure $callback) : static
    {
        $this->treeLazyRootQueryCallback = $callback;
        
        return $this;
    }
    
    
    /**
     * Tree 查询根节点处理
     * @return mixed
     */
    protected function handleTreeLazyRootQuery() : mixed
    {
        $field = $this->treeParentField;
        if ($this->handler) {
            $field = $this->handler->treeLazyRootQuery($field, $this->treeParentRoot);
        } elseif ($this->treeLazyRootQueryCallback) {
            $field = call_user_func_array($this->treeLazyRootQueryCallback, [
                $this->model,
                $field,
                $this->treeParentRoot
            ]);
        }
        
        return $field;
    }
    
    
    /**
     * @inheritDoc
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function build() : ?array
    {
        $total = false;
        $this->prepareHandler();
        
        // 查询模型
        if ($this->model && is_null($this->list)) {
            $this->buildCondition();
            
            // 限制条数
            if ($this->limit > 0) {
                if ($this->maxLimit) {
                    $this->model->limit($this->limit);
                } else {
                    $total = $this->modelTotal();
                    $this->model->limit($this->offset, $this->limit);
                }
            }
            
            // 查询扩展信息
            $this->list = $this->modelSelect();
        }
        
        // 数据集处理
        if (!$this->handleList()) {
            return null;
        }
        
        // 处理是否含有子节点
        if (!$this->treeAlreadyHaveHasChildrenField && ($this->isTreeLazy() || $this->isGetTreeChildren())) {
            $treeIds = [];
            foreach ($this->list as $item) {
                $treeIds[] = $item[$this->treeIdField];
            }
            
            $groups = $this->model
                ->field([
                    $this->treeParentField,
                    sprintf('COUNT(`%s`) AS total', $this->treeParentField)
                ])
                ->where($this->treeParentField, 'in', $treeIds)
                ->group($this->treeParentField)
                ->select()
                ->toArray();
            $groups = array_column($groups, null, $this->treeParentField);
            foreach ($this->list as $item) {
                $item[$this->treeHasChildrenField] = ($groups[$item[$this->treeIdField]]['total'] ?? 0) > 0;
            }
        }
        
        // 需要清理root
        if ($this->treeNeedClearParentToRoot) {
            foreach ($this->list as $item) {
                $item[$this->treeParentField] = $this->treeParentRoot;
            }
        }
        
        if ($this->isGetTreeChildren()) {
            return [
                'list' => $this->list,
            ];
        } else {
            $total = $total === false ? count($this->list) : $total;
            
            return [
                'total'            => $total,
                'totalNotFiltered' => $total,
                'rows'             => $this->list
            ];
        }
    }
    
    
    /**
     * 构建查询条件
     * @return static
     */
    public function buildCondition() : static
    {
        $this->prepareHandler();
        
        if (!$this->model) {
            return $this;
        }
        
        // 查询子节点
        if ($this->isGetTreeChildren()) {
            $this->model->where($this->treeParentField, '=', $this->treeParentId);
        } else {
            // 指定字段搜索
            if ($this->word !== '' && ($this->searchable || $this->field)) {
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
                
                $this->treeNeedClearParentToRoot = $this->isTreeLazy();
            }
            
            // tree
            // 懒加载
            elseif ($this->isTreeLazy() && $field = $this->handleTreeLazyRootQuery()) {
                $this->model->where($field, '=', $this->treeParentRoot);
                $this->treeNeedClearParentToRoot = false;
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
        }
        
        // 排序
        if ($this->orderField && $this->orderType && !$this->model->getOptions('order')) {
            $this->model->order($this->orderField, $this->orderType);
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
}