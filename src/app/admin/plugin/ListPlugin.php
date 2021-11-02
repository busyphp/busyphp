<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\plugin;

use BusyPHP\App;
use BusyPHP\app\admin\plugin\lists\ListHandler;
use BusyPHP\app\admin\plugin\lists\ListSelectResult;
use BusyPHP\exception\ClassNotExtendsException;
use BusyPHP\helper\FilterHelper;
use BusyPHP\Model;
use BusyPHP\model\Map;
use BusyPHP\Request;
use Closure;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Paginator;

/**
 * 通用列表查询插件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/10/8 下午上午9:51 ListPlugin.php $
 */
class ListPlugin
{
    /**
     * @var Model
     */
    protected $model;
    
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
     * 字段查询处理回调
     * @var Closure
     */
    private $fieldHandler;
    
    /**
     * 处理回调
     * @var ListHandler
     */
    private $handler;
    
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
     * 分页码
     * @var int
     */
    public $page;
    
    /**
     * 每页显示条数
     * @var int
     */
    public $limit = 30;
    
    /**
     * 排序方式 键值对 ["字段" => "排序方式"]
     * @var array
     */
    public $sorts;
    
    /**
     * 是否查询扩展数据
     * @var bool
     */
    public $isExtend;
    
    /**
     * 是否统计条数
     * @var bool
     */
    public $simple;
    
    /**
     * 分页驱动
     * @var string
     */
    private $paginator;
    
    
    /**
     * ListPlugin constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model    = $model;
        $this->request  = App::init()->request;
        $this->word     = $this->request->get('word/s', '', 'trim');
        $this->field    = $this->request->get('field/s', '', 'trim');
        $this->accurate = $this->request->get('accurate/b', false);
        $this->page     = $this->request->get('page/d', 0);
        $this->page     = FilterHelper::min($this->page, 1);
        $this->limit    = $this->request->get('limit/d', $this->limit);
        $this->sorts    = $this->request->get('sorts/list');
        $this->isExtend = $this->request->get('extend/b', false);
        $this->simple   = $this->request->get('simple/b', false);
        
        // 附加数据
        $data = $this->request->get('static/a', []);
        foreach ($data as $key => $value) {
            $data[$key] = trim($value);
        }
        $this->data = Map::parse($data);
    }
    
    
    /**
     * 执行查询
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function select() : ListSelectResult
    {
        // 搜索
        if ($this->word !== '' && $this->field) {
            // 处理回调
            if ($this->handler || is_callable($this->fieldHandler)) {
                $sourceWord = $this->word;
                $word       = $this->accurate ? $this->word : '%' . FilterHelper::searchWord($this->word) . '%';
                $op         = $this->accurate ? '=' : 'like';
                
                if ($this->handler) {
                    $this->field = $this->handler->field($this->model, $this->field, $word, $op, $sourceWord);
                } elseif (is_callable($this->queryHandler)) {
                    $this->field = call_user_func_array($this->fieldHandler, [
                        $this->model,
                        $this->field,
                        $word,
                        $op,
                        $sourceWord
                    ]);
                }
            }
            
            // 返回字段才查询
            if ($this->field) {
                if ($this->accurate) {
                    $this->model->where($this->field, $this->word);
                } else {
                    $this->model->whereLike($this->field, '%' . FilterHelper::searchWord($this->word) . '%');
                }
            }
        }
        
        // 执行查询处理程序
        if ($this->handler) {
            $this->handler->query($this, $this->model, $this->data);
        } elseif (is_callable($this->queryHandler)) {
            call_user_func_array($this->queryHandler, [$this->model, $this->data]);
        }
        
        $where = $this->data->getWhere();
        foreach ($where as $key => $value) {
            $this->model->where($key, $value);
        }
        
        // 限制条数
        $totalModel = null;
        if ($this->limit > 0) {
            $totalModel = clone $this->model;
            $totalModel->removeOption('order');
            $totalModel->removeOption('limit');
            $totalModel->removeOption('page');
            $totalModel->removeOption('field');
            
            if ($this->simple) {
                $this->model->limit(($this->page - 1) * $this->limit, $this->limit + 1);
            } else {
                $this->model->page($this->page, $this->limit);
            }
        }
        
        // 排序
        $sorts = $this->sorts ?: ['id' => 'desc'];
        foreach ($sorts as $field => $desc) {
            $desc = trim($desc);
            $this->model->order($field, $desc ?: 'desc');
        }
        
        // 执行查询
        $list = $this->isExtend ? $this->model->selectExtendList() : $this->model->selectList();
        
        // 执行数据处理程序
        if ($this->handler) {
            $resultList = $this->handler->list($list);
            if (is_array($resultList)) {
                $list = $resultList;
            }
        } elseif (is_callable($this->listHandler)) {
            $resultList = call_user_func_array($this->listHandler, [&$list]);
            if (is_array($resultList)) {
                $list = $resultList;
            }
        }
        
        return new ListSelectResult($list, $this->limit, $this->page, $totalModel && !$this->simple ? $totalModel->count() : 0, $this->simple, $this->paginator);
    }
    
    
    /**
     * 设置每页显示条数，0为全部
     * @param int $limit
     * @return ListPlugin
     */
    public function setLimit(int $limit) : self
    {
        $this->limit = $limit;
        
        return $this;
    }
    
    
    /**
     * 设置是否查询扩展数据
     * @param bool $isExtend
     * @return ListPlugin
     */
    public function setExtend(bool $isExtend) : self
    {
        $this->isExtend = $isExtend;
        
        return $this;
    }
    
    
    /**
     * 添加排序方式
     * @param mixed  $field
     * @param string $order
     * @return ListPlugin
     */
    public function addSort($field, string $order) : self
    {
        $this->sorts[(string) $field] = $order;
        
        return $this;
    }
    
    
    /**
     * 设置是否展示简单的分页
     * @param bool $simple
     * @return ListPlugin
     */
    public function setSimple(bool $simple) : self
    {
        $this->simple = $simple;
        
        return $this;
    }
    
    
    /**
     * 设置分页驱动
     * @param string $paginator
     * @return $this
     * @throws ClassNotExtendsException
     */
    public function setPaginator(string $paginator) : self
    {
        if (!is_subclass_of($paginator, Paginator::class)) {
            throw new ClassNotExtendsException($paginator, Paginator::class);
        }
        
        $this->paginator = $paginator;
        
        return $this;
    }
    
    
    /**
     * 设置查询回调
     * @param Closure $queryHandler <p>
     * 匿名函数包涵2个参数<br />
     * <b>{@see \BusyPHP\model\Model} $model 当前查询模型</b><br />
     * <b>{@see \BusyPHP\model\Map} $data 查询参数</b><br /><br />
     * 示例：<br />
     * <pre>$this->setQueryCallback(function(\BusyPHP\model\Model $model, \BusyPHP\model\Map $data) {
     *      $model->where('id', 1);
     *      $data->delete('id');
     *      $data->set('id', 2);
     *      $data->get('id', 0);
     * });</pre>
     * </p>
     * @return ListPlugin
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
     * <b>{@see \BusyPHP\model\Field[]}|array $list 查询得到的数据</b><br />
     * 示例：<br />
     * <pre>$this->setQueryCallback(function(array &$list) {
     *      foreach($list as $i => $item) {
     *      }
     *
     *      return $list;
     * });</pre>
     * </p>
     * @return ListPlugin
     */
    public function setListHandler(Closure $listHandler) : self
    {
        $this->listHandler = $listHandler;
        
        return $this;
    }
    
    
    /**
     * 设置处理回调
     * @param ListHandler $handler
     * @return $this
     */
    public function setHandler(ListHandler $handler) : self
    {
        $this->handler = $handler;
        
        return $this;
    }
    
    
    /**
     * 设置字段查询处理回调
     * @param Closure $fieldHandler <p>
     * 匿名函数包涵5个参数<br />
     * <b>{@see Model} $model 查询模型</b><br />
     * <b>string $field 查询字段</b><br />
     * <b>string $word 关键词</b><br />
     * <b>string $op 条件</b><br />
     * <b>string $sourceWord 原关键词</b><br />
     * 示例：<br />
     * <pre>$this->setFieldHandler(function({@see Model} $model, string $field, string $word, string $op, string $sourceWord) {
     *      return $field;
     * });</pre>
     * </p>
     * @return $this
     */
    public function setFieldHandler(Closure $fieldHandler) : self
    {
        $this->fieldHandler = $fieldHandler;
        
        return $this;
    }
}