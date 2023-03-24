<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\driver\LinkagePicker;

use BusyPHP\app\admin\component\js\driver\LinkagePicker;
use BusyPHP\app\admin\component\js\Node;

/**
 * JS组件[busyAdmin.plugins.LinkagePicker] 节点基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 10:59 LinkagePickerNode.php $
 * @see LinkagePicker
 */
abstract class LinkagePickerNode extends Node
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
    public $name = '';
    
    /**
     * 是否禁用
     * @var bool
     */
    public $disabled = false;
    
    
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
     * @param mixed $id
     * @return static
     */
    public function setId($id) : static
    {
        $this->id = (string) $id;
        
        return $this;
    }
    
    
    /**
     * 获取节点名称
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    
    /**
     * 设置节点名称
     * @param mixed $name
     * @return static
     */
    public function setName($name) : static
    {
        $this->name = (string) $name;
        
        return $this;
    }
    
    
    /**
     * 是否禁用
     * @return bool
     */
    public function isDisabled() : bool
    {
        return $this->disabled;
    }
    
    
    /**
     * 设置是否禁用
     * @param mixed $disabled
     * @return static
     */
    public function setDisabled($disabled) : static
    {
        $this->disabled = (bool) $disabled;
        
        return $this;
    }
}