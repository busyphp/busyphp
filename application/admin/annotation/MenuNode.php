<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\annotation;

use Attribute;

/**
 * 菜单/权限节点定义注解类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/10 13:54 MenuNode.php $
 */
#[Attribute(Attribute::TARGET_METHOD)]
class MenuNode
{
    private bool      $menu;
    
    private string    $icon;
    
    private int|false $sort;
    
    private string    $params;
    
    private string    $name;
    
    private mixed     $parent;
    
    
    /**
     * 构造函数
     * @param bool            $menu true是菜单节点，false是权限节点，默认为true
     * @param string          $name 菜单名称
     * @param string|callable $parent 定义该方法的上级菜单节点，如果不设置则使用当前类定义的{@see MenuGroup}注解，支持 / 变量前缀代表当前控制器名称(不含后缀Controller)，如：/index ，则被转为 goods/index
     * @param string          $icon 定义菜单图标，必须是图标的完整css类名称，如：fa fa-list-ol
     * @param int|false       $sort 定义菜单所属分组排序，数字越大排序越靠后
     * @param string          $params 如果是菜单节点，定义该菜单URL支持的参数名，多个用英文逗号分割，便于系统自动获取参数
     */
    public function __construct(bool $menu = true, string $name = '', string|callable $parent = '', string $icon = '', int|false $sort = false, string $params = '')
    {
        $this->menu   = $menu;
        $this->icon   = $icon;
        $this->sort   = $sort;
        $this->params = $params;
        $this->name   = $name;
        $this->parent = $parent;
    }
    
    
    /**
     * @return bool
     */
    public function isMenu() : bool
    {
        return $this->menu;
    }
    
    
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    
    /**
     * @return string
     */
    public function getIcon() : string
    {
        return $this->icon;
    }
    
    
    /**
     * @return int|false
     */
    public function getSort() : int|false
    {
        return $this->sort;
    }
    
    
    /**
     * @return string
     */
    public function getParams() : string
    {
        return $this->params;
    }
    
    
    /**
     * @return string
     */
    public function getParent() : string
    {
        if (is_array($this->parent) && is_callable($this->parent)) {
            return call_user_func($this->parent);
        }
        
        return $this->parent;
    }
}