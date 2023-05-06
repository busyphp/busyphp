<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\driver\table;

use BusyPHP\app\admin\component\js\driver\Table;
use BusyPHP\app\admin\component\js\Handler;
use BusyPHP\model\ArrayOption;
use BusyPHP\model\Entity;
use think\Collection;

/**
 * JS组件[busyAdmin.plugins.Table] 处理回调
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/12 22:57 TableHandler.php $
 * @see Table
 * @property Table $driver
 */
abstract class TableHandler extends Handler
{
    /**
     * 查询字段处理回调
     * @param string $field 查询的字段
     * @param string $op 查询条件 = 或 like
     * @param string $word 查询的关键词，如果查询条件是like，系统已自动加上 % 号
     * @return mixed 返回真实字段名称或字段实体({@see Entity})，返回空则不查询该字段
     */
    public function fieldQuery(string $field, string $op, string $word) : mixed
    {
        return $field;
    }
    
    
    /**
     * 查询处理回调
     * @param ArrayOption $option
     */
    public function query(ArrayOption $option)
    {
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
    
    
    /**
     * 查询根节点处理回调
     * @param string $field
     * @param string $value
     * @return mixed 返回真实字段名称或字段实体({@see Entity})，返回空则不查询该字段
     */
    public function treeLazyRootQuery(string $field, string $value) : mixed
    {
        return $field;
    }
}