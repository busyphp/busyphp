<?php

namespace BusyPHP\app\admin\js\traits;

use BusyPHP\Model;

/**
 * 查询处理特征类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/14 09:38 ModelQuery.php $
 */
trait ModelQuery
{
    /**
     * 查询处理回调
     * @var null|callable($model Model):mixed
     */
    protected $queryCallback;
    
    
    /**
     * 指定查询处理回调
     * @param null|callable($model Model):mixed $callback 查询处理回调
     * @return $this
     */
    public function query(callable $callback)
    {
        $this->queryCallback = $callback;
        
        return $this;
    }
    
    
    /**
     * 查询处理回调
     * @return false|void|null
     */
    protected function modelQuery()
    {
        $query = null;
        if ($this->handler) {
            $query = $this->handler->query();
        } elseif ($this->queryCallback) {
            $query = call_user_func_array($this->queryCallback, [$this->model]);
        }
        
        return $query;
    }
}