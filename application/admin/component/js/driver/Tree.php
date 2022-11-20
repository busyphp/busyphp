<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\driver;

use BusyPHP\app\admin\component\js\Driver;
use BusyPHP\app\admin\component\js\driver\tree\TreeDeepNode;
use BusyPHP\app\admin\component\js\driver\tree\TreeFlatNode;
use BusyPHP\app\admin\component\js\driver\tree\TreeHandler;
use BusyPHP\app\admin\component\js\driver\tree\TreeNode;
use BusyPHP\app\admin\component\js\traits\Lists;
use BusyPHP\app\admin\component\js\traits\ModelOrder;
use BusyPHP\app\admin\component\js\traits\ModelQuery;
use BusyPHP\app\admin\component\js\traits\ModelSelect;
use BusyPHP\model\Entity;
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
    use ModelSelect;
    use ModelOrder;
    use ModelQuery;
    use Lists;
    
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
     * 节点数据集后置处理回调
     * @var callable($list TreeNode[]):mixed
     */
    protected $afterCallback;
    
    
    final protected static function defineAbstract() : string
    {
        return self::class;
    }
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->idField       = $this->request->param('id_field/s', '', 'trim');
        $this->parentField   = $this->request->param('parent_field/s', '', 'trim');
        $this->nameField     = $this->request->param('name_field/s', '', 'trim');
        $this->disabledField = $this->request->param('disabled_field/s', '', 'trim');
        $this->iconField     = $this->request->param('icon_field/s', '', 'trim');
        $this->asyncNode     = $this->request->param('async_node/b', false);
        $this->asyncParentId = $this->request->param('async_parent_id/s', '', 'trim');
        
        $this->idField       = $this->idField ?: 'id';
        $this->parentField   = $this->parentField ?: 'parent_id';
        $this->nameField     = $this->nameField ?: 'name';
        $this->disabledField = $this->disabledField ?: 'disabled';
        $this->iconField     = $this->iconField ?: 'icon';
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
     * 构建JS组件数据
     * @return null|array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function build() : ?array
    {
        $this->prepareHandler();
        
        if ($this->model && is_null($this->list)) {
            // 查询处理回调
            $query = $this->modelQuery();
            
            // 异步请求节点
            if ($this->asyncNode) {
                if ($query !== false) {
                    $this->model->where($this->parentField, $this->asyncParentId);
                }
            }
            
            $this->list = $this->modelOrder()->modelSelect();
        }
        
        // 数据处理回调
        if (!$this->handleList()) {
            return null;
        }
        
        // 节点处理
        $index = 0;
        $data  = [];
        trace($this->asyncNode ? "OK" : "NO");
        foreach ($this->list as $item) {
            // 异步请求节点
            if ($this->asyncNode) {
                $node = TreeDeepNode::init($item);
            } else {
                $node = TreeFlatNode::init($item);
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