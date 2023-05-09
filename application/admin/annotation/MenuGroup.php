<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\annotation;

use Attribute;

/**
 * 定义控制器类为分组节点注解
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/3/9 16:58 MenuGroup.php $
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class MenuGroup
{
    private string    $path;
    
    private string    $icon;
    
    private int|false $sort;
    
    private string    $name;
    
    private mixed     $parent;
    
    private bool      $default;
    
    
    /**
     * 构造函数
     * @param string          $path 定义该控制器类的分组节点标识，默认使用当前控制器名称(不含后缀Controller)，支持自定义分组标识
     * @param string          $name 分组名称
     * @param string|callable $parent 定义该类上级菜单节点，该节点必须是已定义的节点。
     * @param string          $icon 定义该分组图标，必须是图标的完整css类名称，如：fa fa-list-ol
     * @param int|false       $sort 定义该分组排序，数字越大排序越靠后
     * @param bool            $default 在一个控制器中声明多个时，用来定义该控制器的默认parent属于哪个，如果都是false则取最后一个
     */
    public function __construct(string $path = '', string $name = '', string|callable $parent = '', string $icon = '', int|false $sort = false, bool $default = false)
    {
        $this->path    = $path;
        $this->icon    = $icon;
        $this->sort    = $sort;
        $this->name    = $name;
        $this->parent  = $parent;
        $this->default = $default;
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
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }
    
    
    /**
     * @return int|false
     */
    public function getSort() : int|false
    {
        return $this->sort;
    }
    
    
    /**
     * @return bool
     */
    public function isDefault() : bool
    {
        return $this->default;
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