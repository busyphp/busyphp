<?php

namespace BusyPHP\app\admin\js\driver\autocomplete;

use BusyPHP\app\admin\js\driver\Autocomplete;
use BusyPHP\app\admin\js\Handler;
use think\Collection;

/**
 * JS组件[busyAdmin.plugins.Autocomplete] 处理回调
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 00:01 AutocompleteHandler.php $
 * @see Autocomplete
 * @property Autocomplete $driver
 */
abstract class AutocompleteHandler extends Handler
{
    /**
     * 查询处理回调
     * @return null|false|void 返回false代表阻止系统处理相关参数：
     * ({@see Autocomplete::getTextField()}、{@see Autocomplete::getWord()})
     */
    public function query()
    {
        return null;
    }
    
    
    /**
     * 数据集处理回调
     * @param array|Collection $list
     * @return array|Collection|null|void 返回处理后的数据(array)或数据集({@see Collection})，返回空则使用引用的$list
     */
    public function list(&$list)
    {
        return null;
    }
    
    
    /**
     * 数据集单个Item处理回调
     * @param AutocompleteNode $node 选项节点对象
     * @param mixed            $item 数据集Item
     * @param int              $index 数据下标
     * @return false|null|void 如果返回false则删除该节点
     */
    public function item(AutocompleteNode $node, $item, int $index)
    {
        $node->setText($item[$this->driver->getTextField()] ?? '');
        
        return null;
    }
}