<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\plugin;

use BusyPHP\App;
use BusyPHP\app\admin\js\driver\SelectPicker;
use BusyPHP\app\admin\plugin\selectPicker\SelectPickerHandler;
use BusyPHP\helper\FilterHelper;
use BusyPHP\Model;
use BusyPHP\model\Field;
use BusyPHP\Request;
use Closure;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * SelectPicker Js 插件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/8/30 下午下午1:55 SelectPickerPlugin.php $
 * @deprecated 已过期，请使用 {@see SelectPicker}，未来某个版本会删除
 */
class SelectPickerPlugin
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
     * 选项ID处理回调
     * @var Closure
     */
    private $idHandler;
    
    /**
     * 查询处理回调
     * @var Closure
     */
    private $queryHandler;
    
    /**
     * 处理回调
     * @var SelectPickerHandler
     */
    private $handler;
    
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
        $this->request   = App::getInstance()->request;
        $this->isValue   = $this->request->get('action/s', '', 'trim') === 'value';
        $this->page      = $this->request->get('page/d', 1);
        $this->length    = $this->request->get('length/d', 0);
        $this->idField   = $this->request->get('id_field/s', '', 'trim');
        $this->textField = $this->request->get('text_field/s', '', 'trim');
        $this->order     = $this->request->get('order/s', '', 'trim');
        $this->isExtend  = $this->request->get('extend/b', false);
        $this->word      = $this->request->get('word');
        
        $this->idField   = $this->idField ?: 'id';
        $this->textField = $this->textField ?: 'name';
        $this->length    = $this->length < 0 ? 20 : $this->length;
        $this->order     = FilterHelper::trimArray(explode(',', $this->order));
        $this->word      = is_array($this->word) ? FilterHelper::trimArray($this->word) : trim((string) $this->word);
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
            $model = $this->request->get('model/s', '', 'trim');
            $model = str_replace('/', '\\', $model);
            $model = class_exists($model) ? new $model() : null;
        }
        
        if ($model instanceof Model) {
            // 查询值
            if ($this->isValue) {
                if ($this->word || (is_string($this->word) && $this->word !== '')) {
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
                if ($this->word !== '') {
                    $model->whereLike($this->textField, '%' . FilterHelper::searchWord($this->word) . '%');
                }
                
                if ($this->length > 0) {
                    $model->page($this->page, $this->length);
                }
            }
            
            // 自定义查询条件
            if ($this->handler) {
                $this->handler->query($this, $model);
            } elseif (is_callable($this->queryHandler)) {
                call_user_func_array($this->queryHandler, [$model]);
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
        $hasTextCallback = is_callable($this->textHandler);
        $hasIdCallback   = is_callable($this->idHandler);
        foreach ($data as $i => $item) {
            if ($child) {
                $child     = $child === true ? 'child' : $child;
                $childList = $item[$child] ?? [];
                foreach ($childList as $j => $vo) {
                    if ($this->handler) {
                        $vo['id']   = $this->handler->id($vo, false);
                        $vo['text'] = $this->handler->text($vo, false);
                    } else {
                        if ($hasTextCallback) {
                            $vo['text'] = call_user_func_array($this->textHandler, [$vo, false]);
                        } else {
                            $vo['text'] = $vo[$textField] ?? ($vo['text'] ?? '');
                        }
                        
                        if ($hasIdCallback) {
                            $vo['id'] = call_user_func_array($this->idHandler, [$vo, false]);
                        } else {
                            $vo['id'] = $vo[$idField] ?? ($vo['text'] ?? '');
                        }
                    }
                    
                    $childList[$j] = $vo;
                }
                
                $item['children'] = $childList;
                if ($this->handler) {
                    $item['text'] = $this->handler->text($item, true);
                } elseif ($hasTextCallback) {
                    $item['text'] = call_user_func_array($this->textHandler, [$item, true]);
                } else {
                    $item['text'] = $item[$groupTextField] ?? ($item['text'] ?? '');
                }
            } else {
                if ($this->handler) {
                    $item['id']   = $this->handler->id($item, false);
                    $item['text'] = $this->handler->text($item, false);
                } else {
                    if ($hasTextCallback) {
                        $item['text'] = call_user_func_array($this->textHandler, [$item, false]);
                    } else {
                        $item['text'] = $item[$textField] ?? ($item['text'] ?? '');
                    }
                    
                    if ($hasIdCallback) {
                        $item['id'] = call_user_func_array($this->idHandler, [$item, false]);
                    } else {
                        $item['id'] = $item[$idField] ?? ($item['id'] ?? '');
                    }
                }
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
     * @param Closure $idHandler <p>
     * 匿名函数包涵2个参数，并返回处理后的ID<br />
     * <b>{@see Field}|array $item 信息</b><br />
     * <b>boolean $isGroup 当前信息是否分组</b><br /><br />
     * 示例：<br />
     * <pre>$this->setIdCallback(function({@see Field}|array $item, boolean $isGroup) {
     *      return $item['id'] . '_' . $item['param1'];
     * });</pre>
     * </p>
     * @return $this
     */
    public function setIdHandler(Closure $idHandler) : self
    {
        $this->idHandler = $idHandler;
        
        return $this;
    }
    
    
    /**
     * 设置选项文本处理回调
     * @param Closure $textHandler <p>
     * 匿名函数包涵2个参数，并返回处理后的文本<br />
     * <b>{@see Field}|array $item 信息</b><br />
     * <b>boolean $isGroup 当前信息是否分组</b><br /><br />
     * 示例：<br />
     * <pre>$this->setTextCallback(function({@see Field}|array $item, boolean $isGroup) {
     *      return $item['name'] . '-' . $item['param1']
     * });</pre>
     * </p>
     * @return $this
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
     * @return $this
     */
    public function setQueryHandler(Closure $queryHandler) : self
    {
        $this->queryHandler = $queryHandler;
        
        return $this;
    }
    
    
    /**
     * 设置处理回调
     * @param SelectPickerHandler $handler
     * @return $this
     */
    public function setHandler(SelectPickerHandler $handler) : self
    {
        $this->handler = $handler;
        
        return $this;
    }
}