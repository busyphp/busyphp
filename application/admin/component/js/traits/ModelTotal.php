<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\component\js\traits;

use BusyPHP\Model;

/**
 * 模型统计特征类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/11/14 09:34 ModelTotal.php $
 * @property Model $model
 */
trait ModelTotal
{
    /**
     * 统计
     * @return int
     */
    protected function modelTotal() : int
    {
        $totalModel = clone $this->model;
        $totalModel->removeOption('order');
        $totalModel->removeOption('limit');
        $totalModel->removeOption('page');
        $totalModel->removeOption('field');
        
        return $totalModel->count();
    }
}