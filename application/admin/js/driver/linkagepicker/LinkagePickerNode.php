<?php

namespace BusyPHP\app\admin\js\driver\linkagepicker;

use BusyPHP\app\admin\js\driver\LinkagePicker;
use BusyPHP\model\ObjectOption;

/**
 * JS组件[busyAdmin.plugins.LinkagePicker] 节点基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 10:59 LinkagePickerNode.php $
 * @see LinkagePicker
 */
abstract class LinkagePickerNode extends ObjectOption
{
    /**
     * 节点ID
     * @var string
     */
    protected $id = '';
    
    /**
     * 节点名称
     * @var string
     */
    protected $name = '';
    
    /**
     * 是否禁用
     * @var bool
     */
    protected $disabled = false;
    
    
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
    public function getName() : string
    {
        return $this->name;
    }
    
    
    /**
     * 设置节点名称
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        
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
     * @param bool $disabled
     * @return $this
     */
    public function setDisabled(bool $disabled)
    {
        $this->disabled = $disabled;
        
        return $this;
    }
}