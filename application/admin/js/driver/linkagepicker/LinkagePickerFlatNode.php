<?php

namespace BusyPHP\app\admin\js\driver\linkagepicker;

/**
 * JS组件[busyAdmin.plugins.LinkagePicker] 扁平节点类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 11:02 LinkagePickerFlatNode.php $
 * @see LinkagePicker
 */
class LinkagePickerFlatNode extends LinkagePickerNode
{
    /**
     * 上级ID
     * @var string
     */
    protected $parent = '';
    
    
    /**
     * 获取上级节点ID
     * @return string
     */
    public function getParent() : string
    {
        return $this->parent;
    }
    
    
    /**
     * 设置上级节点ID
     * @param string $parent
     * @return $this
     */
    public function setParent(string $parent) : self
    {
        $this->parent = $parent ?: '';
        
        return $this;
    }
}