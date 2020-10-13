<?php

namespace BusyPHP\app\admin\event;

/**
 * 后台管理面板事件基本类返回对象
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/10/13 下午1:21 下午 AdminPanelDisplayEventResult.php $
 */
class AdminPanelDisplayEventResult implements \ArrayAccess
{
    /**
     * 头
     * @var string
     */
    public $head = '';
    
    /**
     * 尾
     * @var string
     */
    public $foot = '';
    
    /**
     * 内容
     * @var string
     */
    public $content = '';
    
    
    /**
     * 设置头
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    
    /**
     * 设置尾
     * @param string $foot
     */
    public function setFoot($foot)
    {
        $this->foot = $foot;
    }
    
    
    /**
     * 设置内容
     * @param string $head
     */
    public function setHead($head)
    {
        $this->head = $head;
    }
    
    
    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }
    
    
    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }
    
    
    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }
    
    
    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->$offset = null;
    }
}