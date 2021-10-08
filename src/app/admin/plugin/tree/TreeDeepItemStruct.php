<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\plugin\tree;

/**
 * Tree Js 树状节点结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/23 下午上午9:43 TreeDeepItemStruct.php $
 */
class TreeDeepItemStruct extends TreeItemStruct
{
    /**
     * @var TreeDeepItemStruct[]
     */
    public $children = [];
    
    
    /**
     * 设置子节点
     * @param TreeDeepItemStruct[] $children
     * @return $this
     */
    public function setChildren(array $children) : self
    {
        $this->children = $children;
        
        return $this;
    }
    
    
    /**
     * 添加子节点
     * @param TreeDeepItemStruct $item
     * @return $this
     */
    public function addChildren(TreeDeepItemStruct $item) : self
    {
        $this->children[] = $item;
        
        return $this;
    }
}