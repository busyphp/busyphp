<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\driver\SelectPicker;

use BusyPHP\app\admin\component\js\driver\SelectPicker;
use BusyPHP\app\admin\component\js\Handler;
use BusyPHP\model\Field;
use think\Collection;

/**
 * JS组件[busyAdmin.plugins.SelectPicker] 处理回调类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 20:33 SelectPickerHandler.php $
 * @see SelectPicker
 * @property SelectPicker $driver
 */
class SelectPickerHandler extends Handler
{
    /**
     * 查询处理回调
     * @return void|null|false 返回false代表阻止系统处理关键词搜索的相关请求参数：
     * ({@see SelectPicker::isValue()}，{@see SelectPicker::getTextField()}, {@see SelectPicker::getIdField()})
     */
    public function query()
    {
        return null;
    }
    
    
    /**
     * 选项处理回调
     * @param SelectPickerNode $node 节点对象
     * @param array|Field      $item 数据集Item
     * @param bool             $group 是否请求分组，暂无意义
     * @param int              $index 数据集Item下标
     * @return null|false|void  如果返回false则删除该节点
     */
    public function item(SelectPickerNode $node, $item, bool $group, int $index)
    {
        $node->setId($item[$this->driver->getIdField()] ?? '');
        $node->setText($item[$this->driver->getTextField()] ?? '');
        
        return null;
    }
    
    
    /**
     * 数据集处理回调
     * @param array|Collection $list 要处理的数据集
     * @return null|void|array|Collection 返回处理后的数据(array)或数据集({@see Collection})，返回空则使用引用的$list
     */
    public function list(&$list)
    {
        return null;
    }
}