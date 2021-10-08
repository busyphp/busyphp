<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\plugin\tree;

use BusyPHP\model\Map;

/**
 * Tree Js 节点状态结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/23 下午上午9:43 TreeStateStruct.php $
 */
class TreeStateStruct extends Map
{
    /**
     * 是否展开
     * @var bool
     */
    public $opened = false;
    
    /**
     * 是否禁用
     * @var bool
     */
    public $disabled = false;
    
    /**
     * 是否选中
     * @var bool
     */
    public $selected = false;
    
    
    /**
     * 设置是否展开
     * @param bool $opened
     * @return $this
     */
    public function setOpened(bool $opened) : self
    {
        $this->opened = $opened;
        
        return $this;
    }
    
    
    /**
     * 设置是否禁用
     * @param bool $disabled
     * @return $this
     */
    public function setDisabled(bool $disabled) : self
    {
        $this->disabled = $disabled;
        
        return $this;
    }
    
    
    /**
     * 设置是否选中
     * @param bool $selected
     * @return $this
     */
    public function setSelected(bool $selected) : self
    {
        $this->selected = $selected;
        
        return $this;
    }
}