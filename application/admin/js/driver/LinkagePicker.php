<?php

namespace BusyPHP\app\admin\js\driver;

use BusyPHP\app\admin\js\Driver;
use BusyPHP\app\admin\js\driver\linkagepicker\LinkagePickerFlatNode;
use BusyPHP\app\admin\js\driver\linkagepicker\LinkagePickerHandler;
use BusyPHP\Model;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use Closure;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * JS组件[busyAdmin.plugins.LinkagePicker]
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 10:47 LinkagePicker.php $
 * @property LinkagePickerHandler $handler
 * @method LinkagePicker handler(LinkagePickerHandler $handler)
 */
class LinkagePicker extends Driver
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
     * @var callable($node LinkagePickerFlatNode, $item array|Field, $index int):mixed
     */
    protected $itemCallback;
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->idField       = $this->request->param('id_field/s', '', 'trim');       // TODO JS组件暂不支持
        $this->parentField   = $this->request->param('parent_field/s', '', 'trim');   // TODO JS组件暂不支持
        $this->nameField     = $this->request->param('name_field/s', '', 'trim');     // TODO JS组件暂不支持
        $this->disabledField = $this->request->param('disabled_field/s', '', 'trim'); // TODO JS组件暂不支持
        $this->extend        = $this->request->param('extend/b', false);
        
        $this->idField       = $this->idField ?: 'id';
        $this->parentField   = $this->parentField ?: 'parent_id';
        $this->nameField     = $this->nameField ?: 'name';
        $this->disabledField = $this->disabledField ?: 'disabled';
    }
    
    
    /**
     * 指定数据集
     * @param array|Collection|callable($node LinkagePickerFlatNode, $item array|Field, $index int):mixed                            $list 数据集或数据集的Item处理回调
     * @param null|callable($node LinkagePickerFlatNode, $item array|Field, $index int):mixed|callable($list array|Collection):mixed $itemCallback 数据集的Item处理回调
     * @param null|callable($list array|Collection):mixed                                                                            $listCallback 数据集处理回调
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
     * @return LinkagePicker
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
            if ($this->handler) {
                $this->handler->query();
            } elseif ($this->queryCallback) {
                call_user_func_array($this->queryCallback, [$this->model]);
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
            $node = LinkagePickerFlatNode::init();
            
            // 节点处理回调
            $result = null;
            if ($this->handler) {
                $result = $this->handler->item($node, $item, $index);
            } elseif ($this->itemCallback) {
                $result = call_user_func_array($this->itemCallback, [$node, $item, $index]);
            } else {
                $node->setId($item[$this->idField] ?? '');
                $node->setParent($item[$this->parentField] ?? '');
                $node->setDisabled($item[$this->disabledField] ?? false);
                $node->setName($item[$this->nameField] ?? '');
            }
            $index++;
            
            if (false === $result || $node->getName() === '' || $node->getId() === '') {
                continue;
            }
            $data[] = $node;
        }
        
        return [
            'data' => $data
        ];
    }
}