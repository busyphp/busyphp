<?php

namespace BusyPHP\app\admin\js\driver\tree;

use BusyPHP\model\ObjectOption;
use stdClass;

/**
 * JS组件[busyAdmin.plugins.Tree] 节点基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 13:06 TreeNode.php $
 * @see Tree
 */
abstract class TreeNode extends ObjectOption
{
    /**
     * 节点ID
     * @var string
     */
    public $id = '';
    
    /**
     * 节点名称
     * @var string
     */
    public $text = '';
    
    /**
     * 节点图标
     * @var string
     */
    public $icon = '';
    
    /**
     * 节点状态
     * @var array
     */
    public $state = [
        'opened'   => false,
        'disabled' => false,
        'selected' => false
    ];
    
    /**
     * 自定义LI节点属性
     * @var array
     */
    public $li_attr;
    
    /**
     * 自定义A节点属性
     * @var array
     */
    public $a_attr;
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->li_attr = new stdClass();
        $this->a_attr  = new stdClass();
    }
    
    
    /**
     * 获取节点ID
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }
    
    
    /**
     * 设置节点ID
     * @param string $id
     * @return $this
     */
    public function setId(string $id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    
    /**
     * 获取节点名称
     * @return string
     */
    public function getText() : string
    {
        return $this->text;
    }
    
    
    /**
     * 设置节点名称
     * @param string $text
     * @return $this
     */
    public function setText(string $text)
    {
        $this->text = $text;
        
        return $this;
    }
    
    
    /**
     * 获取节点图标
     * @return string
     */
    public function getIcon() : string
    {
        return $this->icon;
    }
    
    
    /**
     * 设置节点图标
     * @param string $icon
     * @return $this
     */
    public function setIcon(string $icon)
    {
        $this->icon = $icon;
        
        return $this;
    }
    
    
    /**
     * 是否展开
     * @return bool
     */
    public function isOpened() : bool
    {
        return $this->state['opened'];
    }
    
    
    /**
     * 设置是否展开
     * @param bool $opened
     * @return $this
     */
    public function setOpened(bool $opened)
    {
        $this->state['opened'] = $opened;
        
        return $this;
    }
    
    
    /**
     * 是否禁用
     * @return bool
     */
    public function isDisabled() : bool
    {
        return $this->state['disabled'];
    }
    
    
    /**
     * 设置是否禁用
     * @param bool $disabled
     * @return $this
     */
    public function setDisabled(bool $disabled)
    {
        $this->state['disabled'] = $disabled;
        
        return $this;
    }
    
    
    /**
     * 是否选中
     * @return bool
     */
    public function isSelected() : bool
    {
        return $this->state['selected'];
    }
    
    
    /**
     * 设置是否选中
     * @param bool $selected
     * @return $this
     */
    public function setSelected(bool $selected)
    {
        $this->state['selected'] = $selected;
        
        return $this;
    }
    
    
    /**
     * 获取LI标签的自定义属性
     * @return array
     */
    public function getLiAttr() : array
    {
        if ($this->li_attr instanceof stdClass) {
            return [];
        }
        
        return $this->li_attr;
    }
    
    
    /**
     * 添加LI标签的自定义属性
     * @param string $key 属性名称
     * @param mixed  $value 属性值
     * @return $this
     */
    public function addLiAttr(string $key, $value)
    {
        if ($this->li_attr instanceof stdClass) {
            $this->li_attr = [];
        }
        
        $this->li_attr[$key] = $value;
        
        return $this;
    }
    
    
    /**
     * 获取A标签的自定义属性
     * @return array
     */
    public function getAAttr() : array
    {
        if ($this->a_attr instanceof stdClass) {
            return [];
        }
        
        return $this->a_attr;
    }
    
    
    /**
     * 添加A标签的自定义属性
     * @param string $key 属性名称
     * @param mixed  $value 属性值
     * @return $this
     */
    public function addAAttr(string $key, $value)
    {
        if ($this->a_attr instanceof stdClass) {
            $this->a_attr = [];
        }
        
        $this->a_attr[$key] = $value;
        
        return $this;
    }
}