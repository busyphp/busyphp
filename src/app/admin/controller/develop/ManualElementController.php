<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;

/**
 * 基本元素
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/2/28 下午9:01 下午 ManualController.php $
 */
class ManualElementController extends InsideController
{
    /**
     * 按钮/按钮组
     */
    public function button()
    {
        return $this->display();
    }
    
    
    /**
     * 徽章
     */
    public function badge()
    {
        return $this->display();
    }
    
    
    /**
     * 警告框
     */
    public function alert()
    {
        return $this->display();
    }
    
    
    /**
     * 进度条
     */
    public function progress()
    {
        return $this->display();
    }
    
    
    /**
     * 表单
     */
    public function form()
    {
        return $this->display();
    }
}