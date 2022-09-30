<?php

namespace BusyPHP\app\admin\plugin\linkagePicker;

use BusyPHP\model\Map;

/**
 * LinkageItem
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/3/12 5:16 PM LinkageItem.php $
 */
class LinkageItem extends Map
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
     * 设置ID
     * @param string $id
     * @return $this
     */
    public function setId(string $id) : self
    {
        $this->id = $id;
        
        return $this;
    }
    
    
    /**
     * 设置名称
     * @param string $name
     * @return $this
     */
    public function setName(string $name) : self
    {
        $this->name = $name;
        
        return $this;
    }
    
    
    /**
     * 设置是否禁用
     * @param bool $disabled
     * @return $this
     */
    public function setDisabled(bool $disabled) : self
    {
        $this->disabled = $disabled;
        
        return $this;
    }
}