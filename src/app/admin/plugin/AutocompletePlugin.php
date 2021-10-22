<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\plugin;

use BusyPHP\helper\FilterHelper;
use BusyPHP\Model;
use BusyPHP\model\Field;
use BusyPHP\Request;
use Closure;
use think\Container;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * Autocomplete Js 插件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/8/30 下午下午5:23 AutocompletePlugin.php $
 */
class AutocompletePlugin
{
    /**
     * @var Request
     */
    private $request;
    
    /**
     * 选项文本处理回调
     * @var Closure
     */
    private $textHandler;
    
    /**
     * 查询处理回调
     * @var Closure
     */
    private $queryHandler;
    
    /**
     * text字段
     * @var string
     */
    public $textField;
    
    /**
     * 排序方式
     * @var array
     */
    public $order;
    
    /**
     * 搜索关键词或默认值
     * @var mixed
     */
    public $word;
    
    /**
     * 是否查询扩展数据
     * @var bool
     */
    public $isExtend;
    
    /**
     * 最大条数限制，0为不限
     * @var int
     */
    public $limit;
    
    
    public function __construct()
    {
        $this->request   = Container::getInstance()->make(Request::class);
        $this->textField = $this->request->post('text_field', '', 'trim');
        $this->order     = $this->request->post('order', '', 'trim');
        $this->isExtend  = $this->request->post('extend', 0, 'intval') > 0;
        $this->limit     = $this->request->post('limit', 0, 'intval');
        $this->word      = $this->request->post('word');
        $this->textField = $this->textField ?: 'name';
        $this->limit     = $this->limit < 0 ? 20 : $this->limit;
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
            $model = $this->request->post('model', '', 'trim');
            $model = str_replace('/', '\\', $model);
            $model = class_exists($model) ? new $model() : null;
        }
        
        if ($model instanceof Model) {
            if ($this->word) {
                $model->whereLike($this->textField, '%' . FilterHelper::searchWord($this->word) . '%');
            }
            
            // 自定义查询条件
            if (is_callable($this->queryHandler)) {
                call_user_func_array($this->queryHandler, [$model]);
            }
            
            if ($this->limit > 0) {
                $model->limit($this->limit);
            }
            
            $model->order($this->order);
            if ($this->isExtend) {
                $list = $model->selectExtendList();
            } else {
                $list = $model->selectList();
            }
            
            return $this->result($list, $this->textField);
        }
        
        return null;
    }
    
    
    /**
     * 返回数据处理
     * @param array  $data 数据
     * @param string $textField text字段
     * @return array
     */
    public function result(array $data, string $textField = 'name') : array
    {
        $hasTextCallback = is_callable($this->textHandler);
        foreach ($data as $i => $item) {
            $item['text'] = $hasTextCallback ? call_user_func_array($this->textHandler, [
                $item,
                false
            ]) : ($item[$textField] ?? '');
            
            $data[$i] = $item;
        }
        
        return [
            'results' => $data
        ];
    }
    
    
    /**
     * 设置选项文本处理回调
     * @param Closure $textHandler <p>
     * 匿名函数包涵2个参数，并返回处理后的文本<br />
     * <b>{@see Field}|array $item 信息</b><br /><br />
     * 示例：<br />
     * <pre>$this->setTextCallback(function({@see Field}|array $item, boolean $isGroup) {
     *      return $item['name'] . '-' . $item['param1']
     * });</pre>
     * </p>
     * @return AutocompletePlugin
     */
    public function setTextHandler(Closure $textHandler) : self
    {
        $this->textHandler = $textHandler;
        
        return $this;
    }
    
    
    /**
     * 设置查询处理回调
     * @param Closure $queryHandler <p>
     * 匿名函数包涵1个参数<br />
     * <b>{@see Model} $model 当前查询模型</b><br /><br />
     * 示例：<br />
     * <pre>$this->setQueryCallback(function({@see Model} $model) {
     *      $model->where('id', 1)
     * });</pre>
     * </p>
     * @return AutocompletePlugin
     */
    public function setQueryHandler(Closure $queryHandler) : self
    {
        $this->queryHandler = $queryHandler;
        
        return $this;
    }
}