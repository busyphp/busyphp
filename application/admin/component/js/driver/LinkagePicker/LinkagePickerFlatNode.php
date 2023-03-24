<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\driver\LinkagePicker;

use BusyPHP\app\admin\component\js\driver\LinkagePicker;

/**
 * JS组件[busyAdmin.plugins.LinkagePicker] 扁平节点类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 11:02 LinkagePickerFlatNode.php $
 * @see LinkagePicker
 */
class LinkagePickerFlatNode extends LinkagePickerNode
{
    /**
     * 上级ID
     * @var string
     */
    public $parent = '';
    
    
    /**
     * 获取上级节点ID
     * @return string
     */
    public function getParent() : string
    {
        return $this->parent;
    }
    
    
    /**
     * 设置上级节点ID
     * @param mixed $parent
     * @return static
     */
    public function setParent($parent) : static
    {
        $this->parent = (string) ($parent ?: '');
        
        return $this;
    }
}