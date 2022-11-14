<?php

namespace BusyPHP\app\admin\component\js\driver\autocomplete;

use BusyPHP\app\admin\component\js\driver\Autocomplete;
use BusyPHP\app\admin\component\js\Node;

/**
 * JS组件[busyAdmin.plugins.Autocomplete] 选项节点类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/14 08:52 AutocompleteNode.php $
 * @see Autocomplete
 */
class AutocompleteNode extends Node
{
    /**
     * @var string
     */
    public $text = '';
    
    
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