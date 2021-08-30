<?php

namespace BusyPHP\app\admin\js;

use BusyPHP\helper\util\Filter;
use BusyPHP\Model;
use BusyPHP\Request;
use Closure;
use think\Container;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * SelectPicker Js 插件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/8/30 下午下午1:55 SelectPicker.php $
 */
class SelectPicker
{
    /**
     * @var Request
     */
    private $request;
    
    /**
     * 选项文本处理回调
     * @var Closure
     */
    private $textCallback;
    
    /**
     * 选项ID处理回调
     * @var Closure
     */
    private $idCallback;
    
    /**
     * 查询处理回调
     * @var Closure
     */
    private $queryCallback;
    
    /**
     * 请求类型
     * @var bool
     */
    public $isValue;
    
    /**
     * 分页
     * @var int
     */
    public $page;
    
    /**
     * 每页显示条数
     * @var int
     */
    public $length;
    
    /**
     * id字段
     * @var string
     */
    public $idField;
    
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
    
    
    public function __construct()
    {
        $this->request = Container::getInstance()->make(Request::class);
        
        $this->isValue   = $this->request->post('action', '', 'trim') === 'value';
        $this->page      = $this->request->post('page', 1, 'intval');
        $this->length    = $this->request->post('length', 0, 'intval');
        $this->idField   = $this->request->post('id_field', '', 'trim');
        $this->textField = $this->request->post('text_field', '', 'trim');
        $this->order     = $this->request->post('order', '', 'trim');
        $this->isExtend  = $this->request->post('extend', 0, 'intval') > 0;
        $this->word      = $this->request->post('word');
        
        $this->idField   = $this->idField ?: 'id';
        $this->textField = $this->textField ?: 'name';
        $this->length    = $this->length < 0 ? 20 : $this->length;
        $this->order     = Filter::trimArray(explode(',', $this->order));
        $this->word      = is_array($this->word) ? Filter::trimArray($this->word) : trim($this->word);
        $this->page      = $this->page <= 1 ? 1 : $this->page;
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
            // 查询值
            if ($this->isValue) {
                if ($this->word) {
                    if (is_array($this->word)) {
                        $model->whereIn($this->idField, $this->word);
                    } else {
                        $model->where($this->idField, $this->word);
                    }
                }
            }
            
            //
            // 查询列表
            else {
                if ($this->word) {
                    $model->whereLike($this->textField, '%' . Filter::searchWord($this->word) . '%');
                }
                
                if ($this->length > 0) {
                    $model->page($this->page, $this->length);
                }
            }
            
            // 自定义查询条件
            if (is_callable($this->queryCallback)) {
                call_user_func_array($this->queryCallback, [$model]);
            }
            
            // 统计总数
            $total = 0;
            if (!$this->isValue && $this->length > 0) {
                $totalModel = clone $model;
                $total      = $totalModel->count();
            }
            
            $model->order($this->order);
            if ($this->isExtend) {
                $list = $model->selectExtendList();
            } else {
                $list = $model->selectList();
            }
            
            return $this->result($list, $this->idField, $this->textField, !$this->isValue && $this->page * $this->length < $total);
        }
        
        return null;
    }
    
    
    /**
     * 返回数据处理
     * @param array       $data 数据
     * @param string      $idField id字段
     * @param string      $textField text字段
     * @param bool        $more 是否有更多数据
     * @param bool|string $child 是否包涵下级，true包涵，false不包含，设置字符串则为下级的字段名称，默认为 child
     * @param string      $groupTextField 分组text字段
     * @return array
     */
    public function result(array $data, string $idField = 'id', string $textField = 'name', bool $more = false, $child = false, $groupTextField = 'name') : array
    {
        $hasTextCallback = is_callable($this->textCallback);
        $hasIdCallback   = is_callable($this->idCallback);
        foreach ($data as $i => $item) {
            if ($child) {
                $child     = $child === true ? 'child' : $child;
                $childList = $item[$child] ?? [];
                foreach ($childList as $j => $vo) {
                    $vo['id']   = $hasIdCallback ? call_user_func_array($this->idCallback, [
                        $item,
                        false
                    ]) : ($vo[$idField] ?? '');
                    $vo['text'] = $hasTextCallback ? call_user_func_array($this->textCallback, [
                        $vo,
                        false
                    ]) : ($vo[$textField] ?? '');
                    
                    $childList[$j] = $vo;
                }
                $item['children'] = $childList;
                $item['text']     = $hasTextCallback ? call_user_func_array($this->textCallback, [
                    $item,
                    true
                ]) : ($item[$groupTextField] ?? '');
            } else {
                $item['id']   = $hasIdCallback ? call_user_func_array($this->idCallback, [
                    $item,
                    false
                ]) : ($item[$idField] ?? '');
                $item['text'] = $hasTextCallback ? call_user_func_array($this->textCallback, [
                    $item,
                    false
                ]) : ($item[$textField] ?? '');
            }
            
            $data[$i] = $item;
        }
        
        return [
            'results'    => $data,
            'pagination' => [
                'more' => $more
            ]
        ];
    }
    
    
    /**
     * 设置选项ID处理回调
     * @param Closure $idCallback <p>
     * 匿名函数包涵2个参数，并返回处理后的ID<br />
     * <b>array $item 信息</b><br />
     * <b>boolean $isGroup 当前信息是否分组</b><br /><br />
     * 示例：<br />
     * <pre>$this->setIdCallback(function(mixed $item, boolean $isGroup) {
     *      return $item['id'] . '_' . $item['param1'];
     * });</pre>
     * </p>
     */
    public function setIdCallback(Closure $idCallback) : void
    {
        $this->idCallback = $idCallback;
    }
    
    
    /**
     * 设置选项文本处理回调
     * @param Closure $textCallback <p>
     * 匿名函数包涵2个参数，并返回处理后的文本<br />
     * <b>array $item 信息</b><br />
     * <b>boolean $isGroup 当前信息是否分组</b><br /><br />
     * 示例：<br />
     * <pre>$this->setTextCallback(function(mixed $item, boolean $isGroup) {
     *      return $item['name'] . '-' . $item['param1']
     * });</pre>
     * </p>
     */
    public function setTextCallback(Closure $textCallback) : void
    {
        $this->textCallback = $textCallback;
    }
    
    
    /**
     * 设置查询处理回调
     * @param Closure $queryCallback <p>
     * 匿名函数包涵1个参数<br />
     * <b>Model $model 当前查询模型</b><br /><br />
     * 示例：<br />
     * <pre>$this->setQueryCallback(function(Model $model) {
     *      $model->where('id', 1)
     * });</pre>
     * </p>
     */
    public function setQueryCallback(Closure $queryCallback) : void
    {
        $this->queryCallback = $queryCallback;
    }
}