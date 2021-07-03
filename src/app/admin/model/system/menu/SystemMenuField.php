<?php

namespace BusyPHP\app\admin\model\system\menu;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Filter;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use BusyPHP\helper\util\Transform;


/**
 * 后台菜单模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2017 busy^life <busy.life@qq.com>
 * @version $Id: 2017-06-06 下午5:27 SystemMenu.php busy^life $
 * @method static Entity id($op = null, $value = null) ID
 * @method static Entity name($op = null, $value = null) 名称
 * @method static Entity type($op = null, $value = null) 菜单类型
 * @method static Entity parentId($op = null, $value = null) 上级菜单ID
 * @method static Entity module($op = null, $value = null) 分组模块
 * @method static Entity control($op = null, $value = null) 控制器
 * @method static Entity action($op = null, $value = null) 执行方法
 * @method static Entity params($op = null, $value = null) 附加参数
 * @method static Entity higher($op = null, $value = null) 定义高亮上级
 * @method static Entity icon($op = null, $value = null) 图标
 * @method static Entity link($op = null, $value = null) 外部链接
 * @method static Entity target($op = null, $value = null) 打开方式
 * @method static Entity isDefault($op = null, $value = null) 默认导航面板
 * @method static Entity isHide($op = null, $value = null) 是否显示
 * @method static Entity isDisabled($op = null, $value = null) 是否禁用
 * @method static Entity isSystem($op = null, $value = null) 是否系统菜单
 * @method static Entity sort($op = null, $value = null) 自定义排序
 */
class SystemMenuField extends Field
{
    /**
     * ID
     * @var int
     */
    public $id;
    
    /**
     * 名称
     * @var string
     */
    public $name;
    
    /**
     * 菜单类型
     * @var int
     */
    public $type;
    
    /**
     * 上级菜单ID
     * @var int
     */
    public $parentId;
    
    /**
     * 分组模块
     * @var string
     */
    public $module;
    
    /**
     * 控制器
     * @var string
     */
    public $control;
    
    /**
     * 执行方法
     * @var string
     */
    public $action;
    
    /**
     * 附加参数
     * @var string
     */
    public $params;
    
    /**
     * 定义高亮上级
     * @var string
     */
    public $higher;
    
    /**
     * 图标
     * @var string
     */
    public $icon;
    
    /**
     * 外部链接
     * @var string
     */
    public $link;
    
    /**
     * 打开方式
     * @var string
     */
    public $target;
    
    /**
     * 默认导航面板
     * @var bool
     */
    public $isDefault;
    
    /**
     * 是否显示
     * @var bool
     */
    public $isHide;
    
    /**
     * 是否禁用
     * @var bool
     */
    public $isDisabled;
    
    /**
     * 是否系统菜单
     * @var bool
     */
    public $isSystem;
    
    /**
     * 自定义排序
     * @var int
     */
    public $sort;
    
    
    /**
     * 设置
     * @param int $id
     * @return $this
     * @throws ParamInvalidException
     */
    public function setId($id)
    {
        $this->id = floatval($id);
        if ($this->id < 1) {
            throw new ParamInvalidException('id');
        }
        
        return $this;
    }
    
    
    /**
     * 设置上级ID
     * @param int $parentId
     * @return $this
     */
    public function setParentId($parentId)
    {
        $this->parentId = intval($parentId);
        
        return $this;
    }
    
    
    /**
     * 设置名称
     * @param string $name
     * @return $this
     * @throws VerifyException
     */
    public function setName($name)
    {
        $this->name = trim($name);
        if (!$this->name) {
            throw new VerifyException('请输入菜单名称', 'name');
        }
        
        return $this;
    }
    
    
    /**
     * 设置执行方法
     * @param string $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = trim($action);
        
        return $this;
    }
    
    
    /**
     * 设置控制器
     * @param string $control
     * @return $this
     */
    public function setControl($control)
    {
        $this->control = trim($control);
        
        return $this;
    }
    
    
    /**
     * 设置分组模块
     * @param string $module
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = trim($module);
        
        return $this;
    }
    
    
    /**
     * 设置附加参数
     * @param string $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = trim($params);
        $this->params = explode(',', $this->params);
        $this->params = Filter::trimArray($this->params);
        $this->params = implode(',', $this->params);
        
        return $this;
    }
    
    
    /**
     * 设置定义高亮上级
     * @param string $higher
     * @return $this
     */
    public function setHigher($higher)
    {
        $this->higher = trim($higher);
        
        return $this;
    }
    
    
    /**
     * 设置图标
     * @param string $icon
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = trim($icon);
        
        return $this;
    }
    
    
    /**
     * 设置外部链接
     * @param string $link
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = trim($link);
        
        return $this;
    }
    
    
    /**
     * 设置打开方式
     * @param string $target
     * @return $this
     */
    public function setTarget($target)
    {
        $this->target = trim($target);
        
        return $this;
    }
    
    
    /**
     * 设置默认导航面板
     * @param int $isDefault
     * @return $this
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = Transform::boolToNumber($isDefault);
        
        return $this;
    }
    
    
    /**
     * 设置是否显示
     * @param int $isHide
     * @return $this
     */
    public function setIsHide($isHide)
    {
        $this->isHide = Transform::boolToNumber($isHide);
        
        return $this;
    }
    
    
    /**
     * 设置是否禁用
     * @param int $isDisabled
     * @return $this
     */
    public function setIsDisabled($isDisabled)
    {
        $this->isDisabled = Transform::boolToNumber($isDisabled);
        
        return $this;
    }
    
    
    /**
     * 设置是否系统菜单
     * @param int $isSystem
     * @return $this
     */
    public function setIsSystem($isSystem)
    {
        $this->isSystem = Transform::boolToNumber($isSystem);
        
        return $this;
    }
    
    
    /**
     * 设置菜单类型
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = intval($type);
        
        return $this;
    }
    
    
    /**
     * 设置自定义排序
     * @param int $sort
     * @return $this
     */
    public function setSort($sort)
    {
        $this->sort = floatval($sort);
        
        return $this;
    }
}