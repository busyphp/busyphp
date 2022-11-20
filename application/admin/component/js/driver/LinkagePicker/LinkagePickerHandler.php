<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\driver\LinkagePicker;

use BusyPHP\app\admin\component\js\driver\LinkagePicker;
use BusyPHP\app\admin\component\js\Handler;
use BusyPHP\model\Field;
use think\Collection;

/**
 * JS组件[busyAdmin.plugins.LinkagePicker] 处理回调
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 11:15 LinkagePickerHandler.php $
 * @see LinkagePicker
 * @property LinkagePicker $driver
 */
class LinkagePickerHandler extends Handler
{
    /**
     * 查询处理回调
     */
    public function query()
    {
    }
    
    
    /**
     * 数据集处理回调
     * @param array|Collection $list
     * @return null|void|array|Collection 返回处理后的数据(array)或数据集({@see Collection})，返回空则使用引用的$list
     */
    public function list(&$list)
    {
        return null;
    }
    
    
    /**
     * 节点处理回调
     * @param LinkagePickerFlatNode $node 扁平节点对象
     * @param array|Field           $item 数据集的Item
     * @param int                   $index 下标
     * @return null|void|false 如果返回false则删除该节点
     */
    public function item(LinkagePickerFlatNode $node, $item, int $index)
    {
        $node->setId($item[$this->driver->getIdField()] ?? '');
        $node->setParent($item[$this->driver->getParentField()] ?? '');
        $node->setName($item[$this->driver->getNameField()] ?? '');
        $node->setDisabled($item[$this->driver->getDisabledField()] ?? false);
        
        return null;
    }
}