<?php

namespace BusyPHP\app\admin\js\struct;

use BusyPHP\model\Map;
use stdClass;

/**
 * Tree Js 节点结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/23 下午上午9:43 TreeItemStruct.php $
 */
abstract class TreeItemStruct extends Map
{
    /**
     * 节点ID
     * @var string
     */
    public $id = '';
    
    /**
     * 节点名称
     * @var string
     */
    public $text = '';
    
    /**
     * 节点图标
     * @var string
     */
    public $icon = '';
    
    /**
     * 节点状态
     * @var TreeStateStruct
     */
    public $state;
    
    /**
     * 自定义LI节点属性
     */
    public $liAttr;
    
    /**
     * 自定义A节点属性
     */
    public $aAttr;
    
    
    public function __construct()
    {
        $this->state  = new TreeStateStruct();
        $this->liAttr = new stdClass();
        $this->aAttr  = new stdClass();
    }
    
    
    /**
     * 设置节点ID
     * @param string $id
     * @return $this
     */
    public function setId($id) : self
    {
        $this->id = trim((string) $id);
        
        return $this;
    }
    
    
    /**
     * 设置节点名称
     * @param string $text
     * @return $this
     */
    public function setText($text) : self
    {
        $this->text = trim((string) $text);
        
        return $this;
    }
    
    
    /**
     * 设置节点图标
     * @param string $icon
     * @return $this
     */
    public function setIcon($icon) : self
    {
        $this->icon = trim((string) $icon);
        
        return $this;
    }
    
    
    /**
     * 设置节点状态
     * @param TreeStateStruct $state
     * @return $this
     */
    public function setState(TreeStateStruct $state) : self
    {
        $this->state = $state;
        
        return $this;
    }
    
    
    /**
     * 设置li节点自定义属性
     * @param $key
     * @param $value
     * @return $this
     */
    public function setLiAttr(string $key, $value) : self
    {
        if ($this->liAttr instanceof stdClass) {
            $this->liAttr = [];
        }
        $this->liAttr[$key] = $value;
        
        return $this;
    }
    
    
    /**
     * 设置a节点自定义属性
     * @param $key
     * @param $value
     * @return $this
     */
    public function setAAttr(string $key, $value) : self
    {
        if ($this->aAttr instanceof stdClass) {
            $this->aAttr = [];
        }
        $this->aAttr[$key] = $value;
        
        return $this;
    }
}