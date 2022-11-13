<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\plugin\tree;

/**
 * Tree Js 扁平节点结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/23 下午上午9:43 TreeItemStruct.php $
 * @deprecated
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
        $parent       = (string) $parent;
        $this->parent = $parent ?: $this->parent;
        
        return $this;
    }
}