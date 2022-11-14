<?php

namespace BusyPHP\app\admin\js;

use BusyPHP\model\ObjectOption;

/**
 * 节点基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/13 21:35 Node.php $
 */
abstract class Node extends ObjectOption
{
    /** @var mixed */
    public $source = [];
    
    
    /**
     * @param mixed $source 源数据
     * @return static
     */
    public static function init($source = [])
    {
        $obj = parent::init();
        $obj->setSource($source);
        
        return $obj;
    }
    
    
    /**
     * 设置源数据
     * @param mixed $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;
        
        return $this;
    }
    
    
    /**
     * 获取源数据
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }
}