<?php

namespace BusyPHP\app\admin\plugin;

use BusyPHP\App;
use BusyPHP\app\admin\plugin\linkagePicker\LinkageFlatItem;
use BusyPHP\app\admin\plugin\linkagePicker\LinkageHandler;
use BusyPHP\Model;
use BusyPHP\Request;
use Closure;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * LinkagePicker JS插件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/3/13 9:27 AM LinkagePickerPlugin.php $
 */
class LinkagePickerPlugin
{
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var LinkageHandler|null
     */
    protected $handler;
    
    /**
     * @var Closure|null
     */
    protected $queryHandler;
    
    /**
     * @var Closure|null
     */
    protected $nodeHandler;
    
    /**
     * 是否查询扩展数据
     * @var bool
     */
    public $extend;
    
    
    public function __construct()
    {
        $this->request = App::getInstance()->request;
        $this->extend  = $this->request->param('extend/b', false);
    }
    
    
    /**
     * 自动构建数据
     * @param Model|null $model 模型
     * @return array
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
            // 执行查询处理程序
            if ($this->handler) {
                $this->handler->query($this, $model);
            } elseif (is_callable($this->queryHandler)) {
                call_user_func_array($this->queryHandler, [$model]);
            }
            
            $list = $this->extend ? $model->selectExtendList() : $model->selectList();
            $data = [];
            foreach ($list as $item) {
                $node = LinkageFlatItem::init();
                
                // 执行节点处理回调
                if ($this->handler) {
                    $this->handler->node($item, $node);
                } elseif (is_callable($this->nodeHandler)) {
                    call_user_func_array($this->nodeHandler, [$item, $node]);
                }
                
                $data[] = $node;
            }
            
            return $this->result($data);
        }
        
        return null;
    }
    
    
    /**
     * 返回数据处理
     * @param array $data 数据
     * @return array
     */
    public function result(array $data) : array
    {
        return [
            'data' => $data,
        ];
    }
    
    
    /**
     * 设置查询回调
     * @param Closure $queryHandler <p>
     * 匿名函数包涵1个参数<br />
     * <b>{@see \BusyPHP\model\Model} $model 当前查询模型</b><br /><br />
     * 示例：<br />
     * <pre>$this->setQueryCallback(function({@see \BusyPHP\model\Model} $model) {
     *      $model->where('id', 1);
     *      $model->order('id', 'desc');
     *      $model->limit('10');
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
     * 设置节点处理回调
     * @param Closure $nodeHandler <p>
     * 匿名函数包涵1个参数<br />
     * <b>{@see Field} $item 当前遍历的数据对象</b><br />
     * <b>{@see LinkageFlatItem} $node 返回的节点对象</b><br /><br />
     * 示例：<br />
     * <pre>$this->setQueryCallback(function({@see Field}|array $item, {@see LinkageFlatItem} $node) {
     *      $node->setId($item->id);
     * });</pre>
     * </p>
     * @return $this
     */
    public function setNodeHandler(Closure $nodeHandler) : self
    {
        $this->nodeHandler = $nodeHandler;
        
        return $this;
    }
    
    
    /**
     * 设置处理回调
     * @param LinkageHandler $handler
     * @return $this
     */
    public function setHandler(LinkageHandler $handler) : self
    {
        $this->handler = $handler;
        
        return $this;
    }
}