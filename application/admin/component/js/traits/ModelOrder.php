<?php

namespace BusyPHP\app\admin\component\js\traits;

use BusyPHP\helper\FilterHelper;
use BusyPHP\Model;
use BusyPHP\Request;

/**
 * 模型排序特征类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/14 09:23 ModelOrder.php $
 * @property Request $request
 * @property Model   $model
 */
trait ModelOrder
{
    /**
     * @var array|string
     */
    private $order;
    
    
    /**
     * 获取排序方式
     * @return array|string
     */
    public function getOrder()
    {
        if (is_null($this->order)) {
            $order = $this->request->param('order/s', '', 'trim');
            $order = FilterHelper::trimArray(explode(',', $order));
            $data  = [];
            foreach ($order as $item) {
                $item  = explode(' ', trim($item));
                $field = trim($item[0] ?? '');
                $type  = trim($item[1] ?? '');
                if (!$field) {
                    continue;
                }
                $data[$field] = $type;
            }
            
            if (!$data) {
                $this->order = 'id DESC';
            } else {
                $this->order = $data;
            }
        }
        
        return $this->order;
    }
    
    
    /**
     * 设置排序方式
     * @param array|string $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        
        return $this;
    }
    
    
    /**
     * 模型排序
     * @return $this
     */
    protected function modelOrder()
    {
        $order = $this->getOrder();
        if ($order && !$this->model->getOptions('order')) {
            $this->model->order($order);
        }
        
        return $this;
    }
}