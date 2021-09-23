<?php

namespace BusyPHP\app\admin\js;

use BusyPHP\App;
use BusyPHP\app\admin\js\struct\TreeFlatItemStruct;
use BusyPHP\Model;
use BusyPHP\Request;
use Closure;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * Tree Js插件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/23 下午上午9:32 TreePlugin.php $
 */
class TreePlugin
{
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * 是否查询扩展数据
     * @var bool
     */
    protected $isExtend;
    
    /**
     * 查询处理回调
     * @var Closure
     */
    protected $queryHandler;
    
    /**
     * 节点处理回调
     * @var Closure
     */
    protected $nodeHandler;
    
    
    public function __construct()
    {
        $this->request  = App::getInstance()->request;
        $this->isExtend = $this->request->get('extend', 0, 'intval') > 0;
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
            $model = $this->request->get('model', '', 'trim');
            $model = str_replace('/', '\\', $model);
            $model = class_exists($model) ? new $model() : null;
        }
        
        if ($model instanceof Model) {
            // 执行查询处理程序
            if (is_callable($this->queryHandler)) {
                call_user_func_array($this->queryHandler, [$model]);
            }
            
            $list = $this->isExtend ? $model->selectExtendList() : $model->selectList();
            $data = [];
            foreach ($list as $item) {
                $node = TreeFlatItemStruct::init();
                if (is_callable($this->nodeHandler)) {
                    call_user_func_array($this->nodeHandler, [$item, $node]);
                }
                
                $node->parent = trim($node->parent) ?: '#';
                
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
     * <pre>$this->setQueryCallback(function(\BusyPHP\model\Model $model) {
     *      $model->where('id', 1);
     *      $model->order('id', 'desc');
     *      $model->limit('10');
     * });</pre>
     * </p>
     */
    public function setQueryHandler(Closure $queryHandler) : void
    {
        $this->queryHandler = $queryHandler;
    }
    
    
    /**
     * 设置节点处理回调
     * @param Closure $nodeHandler <p>
     * 匿名函数包涵1个参数<br />
     * <b>{@see \BusyPHP\model\Field} $item 当前遍历的数据对象</b><br />
     * <b>{@see \BusyPHP\app\admin\js\struct\TreeFlatItemStruct} $node 返回的节点对象</b><br /><br />
     * 示例：<br />
     * <pre>$this->setQueryCallback(function(\BusyPHP\model\Field|array $item, \BusyPHP\app\admin\js\struct\TreeFlatItemStruct $node) {
     *      $node->setId($item->id);
     * });</pre>
     * </p>
     */
    public function setNodeHandler(Closure $nodeHandler) : void
    {
        $this->nodeHandler = $nodeHandler;
    }
}