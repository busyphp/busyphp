<?php

namespace BusyPHP\app\admin\js\driver;

use BusyPHP\app\admin\js\Driver;
use BusyPHP\app\admin\js\driver\tree\TreeDeepNode;
use BusyPHP\app\admin\js\driver\tree\TreeFlatNode;
use BusyPHP\app\admin\js\driver\tree\TreeHandler;
use BusyPHP\app\admin\js\driver\tree\TreeNode;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use Closure;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * JS组件[busyAdmin.plugins.Tree]
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 12:57 Tree.php $
 * @property TreeHandler $handler
 * @method Tree handler(TreeHandler $handler)
 */
class Tree extends Driver
{
    /**
     * 是否查询扩展数据
     * @var bool
     */
    protected $extend;
    
    /**
     * 获取id的字段
     * @var string
     */
    protected $idField;
    
    /**
     * 获取上级ID的字段
     * @var string
     */
    protected $parentField;
    
    /**
     * 获取name的字段
     * @var string
     */
    protected $nameField;
    
    /**
     * 获取是否禁用的字段
     * @var string
     */
    protected $disabledField;
    
    /**
     * 获取图标的字段
     * @var string
     */
    protected $iconField;
    
    /**
     * 是否异步请求节点
     * @var bool
     */
    protected $asyncNode;
    
    /**
     * 异步请求节点的上级ID
     * @var string
     */
    protected $asyncParentId;
    
    /**
     * 排序方式
     * @var string
     */
    protected $order;
    
    /**
     * 数据集
     * @var array|Collection|null
     */
    protected $list = null;
    
    /**
     * 查询处理回调
     * @var null|callable($model Model):void
     */
    protected $queryCallback;
    
    /**
     * 数据集处理回调
     * @var callable($list array|Collection):mixed
     */
    protected $listCallback;
    
    /**
     * 数据集的Item处理回调
     * @var callable($node TreeNode, $item array|Field, $index int):mixed
     */
    protected $itemCallback;
    
    /**
     * 节点数据集后置处理回调
     * @var callable($list TreeNode[]):mixed
     */
    protected $afterCallback;
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->idField       = $this->request->param('id_field/s', '', 'trim');
        $this->parentField   = $this->request->param('parent_field/s', '', 'trim');
        $this->nameField     = $this->request->param('name_field/s', '', 'trim');
        $this->disabledField = $this->request->param('disabled_field/s', '', 'trim');
        $this->iconField     = $this->request->param('icon_field/s', '', 'trim');
        $this->extend        = $this->request->param('extend/b', false);
        $this->asyncNode     = $this->request->param('async_node/b', false);
        $this->asyncParentId = $this->request->param('async_parent_id/s', '', 'trim');
        $this->order         = $this->request->param('order', '', 'trim');
        
        $this->idField       = $this->idField ?: 'id';
        $this->parentField   = $this->parentField ?: 'parent_id';
        $this->nameField     = $this->nameField ?: 'name';
        $this->disabledField = $this->disabledField ?: 'disabled';
        $this->iconField     = $this->iconField ?: 'icon';
        $this->order         = $this->order ?: 'id DESC';
    }
    
    
    /**
     * 指定数据集
     * @param array|Collection|callable($node TreeNode, $item array|Field, $index int):mixed                            $list 数据集或数据集的Item处理回调
     * @param null|callable($node TreeNode, $item array|Field, $index int):mixed|callable($list array|Collection):mixed $itemCallback 数据集的Item处理回调
     * @param null|callable($list array|Collection):mixed                                                               $listCallback 数据集处理回调
     * @return $this
     */
    public function list($list, callable $itemCallback = null, callable $listCallback = null) : self
    {
        if ($list instanceof Closure) {
            $listCallback = $itemCallback;
            $itemCallback = $list;
            $list         = null;
        }
        
        $this->list         = $list;
        $this->itemCallback = $itemCallback;
        $this->listCallback = $listCallback;
        
        return $this;
    }
    
    
    /**
     * 指定查询处理回调
     * @param null|callable($model Model):void $callback 查询处理回调
     * @return $this
     */
    public function query(callable $callback) : self
    {
        $this->queryCallback = $callback;
        
        return $this;
    }
    
    
    /**
     * 指定节点集后置处理回调
     * @param callable($list TreeFlatNode[]|TreeDeepNode[]):mixed $after
     * @return $this
     */
    public function after(callable $after) : self
    {
        $this->afterCallback = $after;
        
        return $this;
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
     * 获取id的字段
     * @return string
     */
    public function getIdField() : string
    {
        return $this->idField;
    }
    
    
    /**
     * 设置获取id的字段
     * @param string|Entity $idField
     * @return $this
     */
    public function setIdField($idField) : self
    {
        $this->idField = (string) $idField;
        
        return $this;
    }
    
    
    /**
     * 获取上级ID的字段
     * @return string
     */
    public function getParentField() : string
    {
        return $this->parentField;
    }
    
    
    /**
     * 设置获取上级ID的字段
     * @param string|Entity $parentField
     * @return $this
     */
    public function setParentField($parentField) : self
    {
        $this->parentField = (string) $parentField;
        
        return $this;
    }
    
    
    /**
     * 获取name的字段
     * @return string
     */
    public function getNameField() : string
    {
        return $this->nameField;
    }
    
    
    /**
     * 设置获取name的字段
     * @param string|Entity $nameField
     * @return $this
     */
    public function setNameField($nameField) : self
    {
        $this->nameField = (string) $nameField;
        
        return $this;
    }
    
    
    /**
     * 获取是否禁用的字段
     * @return string
     */
    public function getDisabledField() : string
    {
        return $this->disabledField;
    }
    
    
    /**
     * 设置获取是否禁用的字段
     * @param string|Entity $disabledField
     * @return $this
     */
    public function setDisabledField($disabledField) : self
    {
        $this->disabledField = (string) $disabledField;
        
        return $this;
    }
    
    
    /**
     * 获取图标的字段
     * @return string
     */
    public function getIconField() : string
    {
        return $this->iconField;
    }
    
    
    /**
     * 设置获取图标的字段
     * @param string|Entity $iconField
     * @return $this
     */
    public function setIconField($iconField) : self
    {
        $this->iconField = (string) $iconField;
        
        return $this;
    }
    
    
    /**
     * 是否异步请求节点
     * @return bool
     */
    public function isAsyncNode() : bool
    {
        return $this->asyncNode;
    }
    
    
    /**
     * 获取异步请求节点的上级ID
     * @return string
     */
    public function getAsyncParentId() : string
    {
        return $this->asyncParentId;
    }
    
    
    /**
     * 设置排序方式
     * @param string $order
     * @return $this
     */
    public function setOrder($order) : self
    {
        $this->order = $order;
        
        return $this;
    }
    
    
    /**
     * 获取排序方式
     * @return string
     */
    public function getOrder() : string
    {
        return $this->order;
    }
    
    
    /**
     * 构建JS组件数据
     * @return null|array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function build() : ?array
    {
        if ($this->handler) {
            $this->handler->prepare($this);
        }
        
        if ($this->model && is_null($this->list)) {
            // 查询处理回调
            $status = true;
            if ($this->handler) {
                $status = $this->handler->query();
            } elseif ($this->queryCallback) {
                $status = call_user_func_array($this->queryCallback, [$this->model]);
            }
            
            // 异步请求节点
            if ($this->asyncNode && $status !== false) {
                $this->model->where($this->parentField, $this->asyncParentId);
            }
            
            // 自定义排序
            if ($this->order !== '' && !$this->model->getOptions('order')) {
                $this->model->order($this->order);
            }
            
            if ($this->extend) {
                $this->list = $this->model->selectExtendList();
            } else {
                $this->list = $this->model->selectList();
            }
        }
        
        if (is_null($this->list)) {
            return null;
        }
        
        // 数据处理回调
        $list = null;
        if ($this->handler) {
            $this->handler->list($this->list);
        } elseif ($this->listCallback) {
            $list = call_user_func_array($this->listCallback, [&$this->list]);
        }
        if (is_array($list) || $list instanceof Collection) {
            $this->list = $list;
        }
        
        // 节点处理
        $index = 0;
        $data  = [];
        foreach ($this->list as $item) {
            // 异步请求节点
            if ($this->asyncNode) {
                $node = TreeDeepNode::init();
            } else {
                $node = TreeFlatNode::init();
            }
            
            // 节点处理回调
            $result = null;
            if ($this->handler) {
                $result = $this->handler->item($node, $item, $index);
            } elseif ($this->itemCallback) {
                $result = call_user_func_array($this->itemCallback, [$node, $item, $index]);
            } else {
                $node->setId($item[$this->idField] ?? '');
                $node->setDisabled($item[$this->disabledField] ?? false);
                $node->setText($item[$this->nameField] ?? '');
                $node->setIcon($item[$this->iconField] ?? '');
                if (!$this->asyncNode) {
                    $node->setParent($item[$this->parentField] ?? '');
                }
            }
            $index++;
            
            if (false === $result || $node->getText() === '' || $node->getId() === '') {
                continue;
            }
            $data[] = $node;
        }
        
        // 后置处理回调
        $result = null;
        if ($this->handler) {
            $result = $this->handler->after($data);
        } elseif ($this->afterCallback) {
            $result = call_user_func_array($this->afterCallback, [&$data]);
        } elseif ($this->model && $this->asyncNode) {
            $paths = $this
                ->model
                ->where($this->parentField, 'in', array_column($data, 'id'))
                ->column($this->parentField);
            /** @var TreeDeepNode $item */
            foreach ($data as $item) {
                $item->setChildren(in_array($item->getId(), $paths));
            }
        }
        if (is_array($result)) {
            $data = $result;
        }
        
        return [
            'data' => $data
        ];
    }
}