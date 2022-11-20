<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\traits;

use Closure;
use think\Collection;

/**
 * 数据集特征类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/14 09:51 Lists.php $
 */
trait Lists
{
    /**
     * 数据集
     * @var array|Collection|null
     */
    protected $list = null;
    
    /**
     * 数据集处理回调
     * @var callable($list array|Collection):mixed
     */
    protected $listCallback;
    
    /**
     * 数据集的Item处理回调
     * @var callable
     */
    protected $itemCallback;
    
    
    /**
     * 指定数据集
     * @param array|Collection|callable                   $list 数据集或数据集的Item处理回调
     * @param null|callable                               $itemCallback 数据集的Item处理回调
     * @param null|callable($list array|Collection):mixed $listCallback 数据集处理回调
     * @return $this
     */
    public function list($list, callable $itemCallback = null, callable $listCallback = null)
    {
        if ($list instanceof Closure) {
            $listCallback = $itemCallback;
            $itemCallback = $list;
            $list         = null;
        }
        
        $this->list         = $list;
        $this->itemCallback = $itemCallback;
        $this->listCallback = $listCallback;
        
        return $this;
    }
    
    
    /**
     * 处理数据集
     * @return bool
     */
    protected function handleList() : bool
    {
        if (is_null($this->list)) {
            return false;
        }
        
        // 数据集处理回调
        $list = null;
        if ($this->handler) {
            $list = $this->handler->list($this->list);
        } elseif ($this->listCallback) {
            $list = call_user_func_array($this->listCallback, [&$this->list]);
        }
        if (is_array($list) || $list instanceof Collection) {
            $this->list = $list;
        }
        
        return true;
    }
}