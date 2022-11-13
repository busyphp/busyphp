<?php

namespace BusyPHP\app\admin\js\driver\tree;

use BusyPHP\app\admin\js\driver\Tree;

/**
 * JS组件[busyAdmin.plugins.Tree] 扁平节点类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 13:31 TreeFlatNode.php $
 * @see Tree
 */
class TreeFlatNode extends TreeNode
{
    /**
     * 父节点ID
     * @var string
     */
    public $parent = '#';
    
    
    /**
     * 获取父节点ID
     * @return string
     */
    public function getParent() : string
    {
        return $this->parent;
    }
    
    
    /**
     * 设置父节点ID
     * @param string $parent
     * @return $this
     */
    public function setParent(string $parent) : self
    {
        $this->parent = $parent ?: '#';
        
        return $this;
    }
}