<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\message\todo;

/**
 * TodoReadParameter
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/19 19:54 TodoReadParameter.php $
 */
class TodoReadParameter extends TodoParameter
{
    /** @var string */
    private $id;
    
    
    /**
     * 获取ID
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }
    
    
    /**
     * 设置ID
     * @param string $id
     * @return static
     */
    public function setId(string $id) : static
    {
        $this->id = $id;
        
        return $this;
    }
}