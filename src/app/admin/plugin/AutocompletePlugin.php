<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\plugin;

use BusyPHP\App;
use BusyPHP\app\admin\plugin\autocomplete\AutocompleteHandler;
use BusyPHP\helper\FilterHelper;
use BusyPHP\Model;
use BusyPHP\model\Field;
use BusyPHP\Request;
use Closure;
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
     * 处理回调
     * @var AutocompleteHandler
     */
    private $handler;
    
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
     * @var string
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
        $this->request   = App::init()->request;
        $this->textField = $this->request->post('text_field/s', '', 'trim');
        $this->order     = $this->request->post('order/s', '', 'trim');
        $this->isExtend  = $this->request->post('extend/b', false);
        $this->limit     = $this->request->post('limit/d', 0);
        $this->word      = $this->request->post('word/s', '', 'trim');
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
            $model = $this->request->post('model/s', '', 'trim');
            $model = str_replace('/', '\\', $model);
            $model = class_exists($model) ? new $model() : null;
        }
        
        if ($model instanceof Model) {
            if ($this->word !== '') {
                $model->whereLike($this->textField, '%' . FilterHelper::searchWord($this->word) . '%');
            }
            
            // 自定义查询条件
            if ($this->handler) {
                $this->handler->query($this, $model);
            } elseif (is_callable($this->queryHandler)) {
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
        $hasTextCallback = is_callable($this->textHandler) || $this->handler;
        foreach ($data as $i => $item) {
            if ($this->handler) {
                $item['text'] = $this->handler->text($item, false);
            } elseif ($hasTextCallback) {
                $item['text'] = call_user_func_array($this->textHandler, [$item, false]);
            } else {
                $item['text'] = $item[$textField] ?? '';
            }
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
    
    
    /**
     * 设置处理回调
     * @param AutocompleteHandler $handler
     * @return $this
     */
    public function setHandler(AutocompleteHandler $handler) : self
    {
        $this->handler = $handler;
        
        return $this;
    }
}