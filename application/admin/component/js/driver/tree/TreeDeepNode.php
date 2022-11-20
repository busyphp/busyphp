<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\driver\tree;

use BusyPHP\app\admin\component\js\driver\Tree;

/**
 * JS组件[busyAdmin.plugins.Tree] 树结构节点类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 13:52 TreeDeepNode.php $
 * @see Tree
 */
class TreeDeepNode extends TreeNode
{
    /**
     * @var TreeDeepNode[]|bool
     */
    public $children = false;
    
    
    /**
     * 获取下级节点数据集或是否含有下级节点
     * @return TreeDeepNode[]|bool
     */
    public function getChildren() : array
    {
        return $this->children;
    }
    
    
    /**
     * 设置获取下级节点数据集或是否含有下级节点
     * @param TreeDeepNode[]|bool $children
     * @return $this
     */
    public function setChildren($children) : self
    {
        if (!is_array($children)) {
            $children = (bool) $children;
        }
        
        $this->children = $children;
        
        return $this;
    }
    
    
    /**
     * 添加下级节点
     * @param TreeDeepNode $node
     * @return $this
     */
    public function addChildren(TreeDeepNode $node) : self
    {
        if (!is_array($this->children)) {
            $this->children = [];
        }
        
        $this->children[] = $node;
        
        return $this;
    }
}