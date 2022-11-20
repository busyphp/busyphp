<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\driver;

use BusyPHP\app\admin\component\js\Driver;
use BusyPHP\app\admin\component\js\driver\LinkagePicker\LinkagePickerFlatNode;
use BusyPHP\app\admin\component\js\driver\LinkagePicker\LinkagePickerHandler;
use BusyPHP\app\admin\component\js\traits\Lists;
use BusyPHP\app\admin\component\js\traits\ModelOrder;
use BusyPHP\app\admin\component\js\traits\ModelQuery;
use BusyPHP\app\admin\component\js\traits\ModelSelect;
use BusyPHP\model\Entity;
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
        
        $this->idField       = $this->idField ?: 'id';
        $this->parentField   = $this->parentField ?: 'parent_id';
        $this->nameField     = $this->nameField ?: 'name';
        $this->disabledField = $this->disabledField ?: 'disabled';
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
        $this->prepareHandler();
        
        if ($this->model && is_null($this->list)) {
            // 查询处理回调
            $this->modelQuery();
            
            $this->list = $this->modelOrder()->modelSelect();
        }
        
        // 数据集处理
        if (!$this->handleList()) {
            return null;
        }
        
        // 节点处理
        $index = 0;
        $data  = [];
        foreach ($this->list as $item) {
            $node = LinkagePickerFlatNode::init($item);
            
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