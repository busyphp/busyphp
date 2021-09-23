<?php

namespace BusyPHP\app\admin\js\struct;

/**
 * Tree Js 扁平节点结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/23 下午上午9:43 TreeItemStruct.php $
 */
class TreeFlatItemStruct extends TreeItemStruct
{
    /**
     * 父节点ID
     * @var string
     */
    public $parent = '#';
    
    
    /**
     * 设置父节点ID
     * @param string $parent
     * @return $this
     */
    public function setParent($parent) : self
    {
        $this->parent = trim($parent);
        
        return $this;
    }
}