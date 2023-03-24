<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js;

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
    public static function init($source = []) : static
    {
        $obj = parent::init();
        $obj->setSource($source);
        
        return $obj;
    }
    
    
    /**
     * 设置源数据
     * @param mixed $source
     * @return static
     */
    public function setSource($source) : static
    {
        $this->source = $source;
        
        return $this;
    }
    
    
    /**
     * 获取源数据
     * @return mixed
     */
    public function getSource() : mixed
    {
        return $this->source;
    }
}