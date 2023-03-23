<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\annotation\MenuGroup;
use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\controller\InsideController;
use think\Response;

/**
 * 基本元素
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/2/28 下午9:01 下午 ElementController.php $
 */
#[MenuRoute(path: 'manual_element', class: true)]
#[MenuGroup(path: 'developer_manual_element', parent: '#developer_manual', icon: 'fa fa-wpforms')]
class ElementController extends InsideController
{
    protected function initialize($checkLogin = true)
    {
        $this->releaseDisabled();
        
        parent::initialize($checkLogin);
    }
    
    
    /**
     * 布局
     */
    #[MenuNode(menu: true, icon: 'fa fa-th-large')]
    public function grid() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 基本
     */
    #[MenuNode(menu: true, icon: 'fa fa-code')]
    public function base() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 表单
     */
    #[MenuNode(menu: true, icon: 'fa fa-list-alt')]
    public function form() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 按钮/按钮组
     */
    #[MenuNode(menu: true, icon: 'fa fa-flickr')]
    public function button() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 徽章
     */
    #[MenuNode(menu: true, icon: 'fa fa-circle')]
    public function badge() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 警告框
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-dialog')]
    public function alert() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 进度条
     */
    #[MenuNode(menu: true, icon: 'fa fa-tasks')]
    public function progress() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 表格
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-table')]
    public function table() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 列表组
     */
    #[MenuNode(menu: true, icon: 'fa fa-list-ul')]
    public function group_list() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 选项卡
     */
    #[MenuNode(menu: true, icon: 'fa fa-flag-o')]
    public function tabs() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 面板
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-dialog')]
    public function panel() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 列表布局
     */
    #[MenuNode(menu: true, icon: 'fa fa-list-alt')]
    public function list_item() : Response
    {
        return $this->insideDisplay();
    }
}