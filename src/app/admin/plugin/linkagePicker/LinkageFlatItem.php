<?php

namespace BusyPHP\app\admin\plugin\linkagePicker;

/**
 * 扁平结构节点
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/3/12 5:27 PM LinkageFlatItem.php $
 */
class LinkageFlatItem extends LinkageItem
{
    /**
     * 上级ID
     * @var string
     */
    public $parent = '';
    
    
    /**
     * 设置父级ID
     * @param string $parent
     * @return $this
     */
    public function setParent(string $parent) : self
    {
        $this->parent = $parent;
        
        return $this;
    }
}