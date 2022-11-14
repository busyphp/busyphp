<?php

namespace BusyPHP\app\admin\component\js\driver\tree;

use BusyPHP\app\admin\component\js\driver\Tree;
use BusyPHP\app\admin\component\js\Handler;
use BusyPHP\model\Field;
use think\Collection;

/**
 * JS组件[busyAdmin.plugins.Tree] 处理回调
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 13:32 TreeHandler.php $
 * @see Tree
 * @property Tree $driver
 */
class TreeHandler extends Handler
{
    /**
     * 查询处理回调
     * @return void|false|null 返回false代表阻止系统处理异步节点的请求参数：
     * ({@see Tree::isAsyncNode()}，{@see Tree::getAsyncParentId()})
     */
    public function query()
    {
        return null;
    }
    
    
    /**
     * 数据集处理回调
     * @param array|Collection $list 要处理的数据集
     * @return void|array|Collection|null 返回处理后的数据(array)或数据集({@see Collection})，返回空则使用引用的$list
     */
    public function list(&$list)
    {
        return null;
    }
    
    
    /**
     * 节点处理回调
     * @param TreeNode    $node 节点对象，可能是 {@see TreeFlatNode}，也可能是 {@see TreeDeepNode}，取决于是否异步请求节点
     * @param array|Field $item 数据集的Item
     * @param int         $index 下标
     * @return void|null|false 如果返回false则删除该节点
     */
    public function item(TreeNode $node, $item, int $index)
    {
        $node->setId($item[$this->driver->getIdField()] ?? '');
        $node->setDisabled($item[$this->driver->getDisabledField()] ?? false);
        $node->setText($item[$this->driver->getNameField()] ?? '');
        $node->setIcon($item[$this->driver->getIconField()] ?? '');
        
        if ($node instanceof TreeFlatNode) {
            $node->setParent($item[$this->driver->getParentField()] ?? '');
        }
        
        return null;
    }
    
    
    /**
     * 节点数据集后置处理回调
     * @param TreeFlatNode[]|TreeDeepNode[] $list 节点数据集
     * @return void|null|TreeFlatNode[]|TreeDeepNode[] 返回处理后的节点数据集，返回空则使用引用的$list
     */
    public function after(array &$list)
    {
        return null;
    }
}