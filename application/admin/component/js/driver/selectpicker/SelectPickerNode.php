<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\driver\selectpicker;

use BusyPHP\app\admin\component\js\driver\selectpicker;
use BusyPHP\app\admin\component\js\Node;

/**
 * JS组件[busyAdmin.plugins.SelectPicker] 选项节点类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 21:13 SelectPickerNode.php $
 * @see SelectPicker
 */
class SelectPickerNode extends Node
{
    /**
     * @var string
     */
    public $id = '';
    
    /**
     * @var string
     */
    public $text = '';
    
    
    /**
     * 获取选项ID
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }
    
    
    /**
     * 设置选项ID
     * @param mixed $id
     * @return $this
     */
    public function setId($id) : self
    {
        $this->id = (string) $id;
        
        return $this;
    }
    
    
    /**
     * 获取选项文本
     * @return string
     */
    public function getText() : string
    {
        return $this->text;
    }
    
    
    /**
     * 设置选项文本
     * @param mixed $text
     * @return $this
     */
    public function setText($text) : self
    {
        $this->text = (string) $text;
        
        return $this;
    }
}