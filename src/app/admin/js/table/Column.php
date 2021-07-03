<?php

namespace BusyPHP\app\admin\js\table;

use BusyPHP\model\Entity;
use BusyPHP\model\Field;

/**
 * 表格列实体
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/6/28 下午上午11:15 Column.php $
 */
class Column extends Field
{
    /**
     * 列名称
     * @var string
     */
    private $title;
    
    /**
     * 列字段
     * @var Entity|string
     */
    private $field;
    
    /**
     * 列是否checkbox复选框
     * @var bool
     */
    private $checkbox;
    
    /**
     * 是否启用checkbox复选框
     * @var bool
     */
    private $checkboxEnabled;
    
    /**
     * 列宽度
     * @var int
     */
    private $width;
    
    /**
     * 单元格式化JS事件名
     * @var string
     */
    private $formatter;
}