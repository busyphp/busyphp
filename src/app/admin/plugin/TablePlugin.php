<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\plugin;

use BusyPHP\App;
use BusyPHP\helper\FilterHelper;
use BusyPHP\Model;
use BusyPHP\model\Field;
use BusyPHP\model\Map;
use BusyPHP\Request;
use Closure;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * Table Js 插件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/7 下午下午8:32 TablePlugin.php $
 */
class TablePlugin
{
    /**
     * @var Request
     */
    private $request;
    
    /**
     * 数据处理回调
     * @var Closure
     */
    private $listHandler;
    
    /**
     * 查询处理回调
     * @var Closure
     */
    private $queryHandler;
    
    /**
     * 排序字段
     * @var string
     */
    public $sortField;
    
    /**
     * 排序方式
     * @var string
     */
    public $sortOrder;
    
    /**
     * 偏移量
     * @var int
     */
    public $offset;
    
    /**
     * 每页显示条数
     * @var int
     */
    public $limit;
    
    /**
     * 搜索的字段
     * @var string
     */
    public $field;
    
    /**
     * 搜索的内容
     * @var string
     */
    public $word;
    
    /**
     * 是否查询扩展数据
     * @var bool
     */
    public $isExtend;
    
    /**
     * 查询字段键值对
     * @var Map
     */
    public $data;
    
    /**
     * 是否精确搜索
     * @var bool
     */
    public $accurate;
    
    /**
     * 允许参与搜索的字段
     * @var array
     */
    public $searchable;
    
    
    public function __construct()
    {
        $this->request    = App::init()->request;
        $this->isExtend   = $this->request->get('extend/b', false);
        $this->limit      = $this->request->get('limit/d', 0);
        $this->offset     = $this->request->get('offset/d', 0);
        $this->word       = $this->request->get('word/s', '', 'trim');
        $this->field      = $this->request->get('field', '', 'trim');
        $this->accurate   = $this->request->get('accurate/b', false);
        $this->sortOrder  = $this->request->get('order/s', '', 'trim');
        $this->sortOrder  = $this->sortOrder ?: 'desc';
        $this->sortField  = $this->request->get('sort/s', '', 'trim');
        $this->sortField  = $this->sortField ?: 'id';
        $this->searchable = $this->request->get('searchable/a', []);
        
        // 附加数据
        $data = $this->request->get('static/a', []);
        foreach ($data as $key => $value) {
            $data[$key] = trim($value);
        }
        $this->data = Map::parse($data);
        
        // 扩展搜索词
        if ($this->word === '') {
            $this->word = $this->request->get('search/s', '', 'trim');
        }
    }
    
    
    /**
     * 自动构建数据
     * @param Model|null $model
     * @return array|null
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function build(?Model $model = null) : ?array
    {
        if (!$model) {
            $model = $this->request->get('model/s', '', 'trim');
            $model = str_replace('/', '\\', $model);
            $model = class_exists($model) ? new $model() : null;
        }
        
        if ($model instanceof Model) {
            // 搜索
            if ($this->word !== '' && $this->field) {
                if ($this->accurate) {
                    $model->where($this->field, $this->word);
                } else {
                    $model->whereLike($this->field, '%' . FilterHelper::searchWord($this->word) . '%');
                }
            } elseif ($this->word !== '' && $this->searchable) {
                foreach ($this->searchable as $field) {
                    $model->whereLike($field, '%' . FilterHelper::searchWord($this->word) . '%');
                }
            }
            
            // 执行查询处理程序
            if (is_callable($this->queryHandler)) {
                call_user_func_array($this->queryHandler, [$model, $this->data]);
            }
            $where = $this->data->getWhere();
            foreach ($where as $key => $value) {
                $model->where($key, $value);
            }
            
            // 限制条数
            if ($this->limit > 0) {
                // 统计长度
                $totalModel = clone $model;
                $total      = $totalModel->count();
                
                $model->limit($this->offset, $this->limit);
            } else {
                $total = 0;
            }
            
            // 排序
            if ($this->sortOrder && $this->sortField) {
                $model->order($this->sortField, $this->sortOrder);
            }
            
            if ($this->isExtend) {
                $list = $model->selectExtendList();
            } else {
                $list = $model->selectList();
            }
            
            // 执行数据处理程序
            if (is_callable($this->listHandler)) {
                $resultList = call_user_func_array($this->listHandler, [&$list]);
                if (is_array($resultList)) {
                    $list = $resultList;
                }
            }
            
            return $this->result($list, $this->limit > 0 ? $total : count($list));
        }
        
        return null;
    }
    
    
    /**
     * 返回数据处理
     * @param array|Collection $data 数据
     * @param int              $total 总条数
     * @return array
     */
    public function result($data, int $total) : array
    {
        return [
            'total'            => $total,
            'totalNotFiltered' => $total,
            'rows'             => $data,
        ];
    }
    
    
    /**
     * 设置查询回调
     * @param Closure $queryHandler <p>
     * 匿名函数包涵2个参数<br />
     * <b>{@see Model} $model 当前查询模型</b><br />
     * <b>{@see Map} $data 查询参数</b><br /><br />
     * 示例：<br />
     * <pre>$this->setQueryCallback(function({@see Model} $model, {@see Map} $data) {
     *      $model->where('id', 1);
     *      $data->delete('id');
     *      $data->set('id', 2);
     *      $data->get('id', 0);
     * });</pre>
     * </p>
     */
    public function setQueryHandler(Closure $queryHandler) : self
    {
        $this->queryHandler = $queryHandler;
        
        return $this;
    }
    
    
    /**
     * 设置数据列表处理回调
     * @param Closure $listHandler <p>
     * 匿名函数包涵1个参数<br />
     * <b>{@see Field[]}|array $list 查询得到的数据</b><br />
     * 示例：<br />
     * <pre>$this->setQueryCallback(function({@see Field[]}|array &$list) {
     *      foreach($list as $i => $item) {
     *      }
     *
     *      return $list;
     * });</pre>
     * </p>
     */
    public function setListHandler(Closure $listHandler) : self
    {
        $this->listHandler = $listHandler;
        
        return $this;
    }
}