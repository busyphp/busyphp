<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\menu;

use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use BusyPHP\helper\TransHelper;

/**
 * 后台菜单模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2017 busy^life <busy.life@qq.com>
 * @version $Id: 2017-06-06 下午5:27 SystemMenu.php busy^life $
 * @method static Entity id($op = null, $value = null) ID
 * @method static Entity name($op = null, $value = null) 名称
 * @method static Entity path($op = null, $value = null) 路由地址
 * @method static Entity parentPath($op = null, $value = null) 上级路由
 * @method static Entity topPath($op = null, $value = null) 顶级菜单默认访问路由地址
 * @method static Entity params($op = null, $value = null) 附加参数
 * @method static Entity icon($op = null, $value = null) 图标
 * @method static Entity target($op = null, $value = null) 打开方式
 * @method static Entity hide($op = null, $value = null) 是否隐藏
 * @method static Entity disabled($op = null, $value = null) 是否禁用
 * @method static Entity system($op = null, $value = null) 是否系统菜单
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
     * 路由地址
     * @var string
     */
    public $path;
    
    /**
     * 上级路由
     * @var string
     */
    public $parentPath;
    
    /**
     * 顶级菜单默认访问路由地址
     * @var string
     */
    public $topPath;
    
    /**
     * 附加参数
     * @var string
     */
    public $params;
    
    /**
     * 图标
     * @var string
     */
    public $icon;
    
    /**
     * 打开方式
     * @var string
     */
    public $target;
    
    /**
     * 是否隐藏
     * @var int
     */
    public $hide;
    
    /**
     * 是否禁用
     * @var int
     */
    public $disabled;
    
    /**
     * 是否系统菜单
     * @var int
     */
    public $system;
    
    /**
     * 自定义排序
     * @var int
     */
    public $sort;
    
    
    /**
     * 设置ID
     * @param int $id
     * @return $this
     * @throws ParamInvalidException
     */
    public function setId($id)
    {
        $this->id = intval($id);
        if ($this->id < 1) {
            throw new ParamInvalidException('id');
        }
        
        return $this;
    }
    
    
    /**
     * 设置名称
     * @param string $name
     * @return $this
     * @throws VerifyException
     */
    public function setName(string $name)
    {
        $this->name = trim($name);
        if (!$this->name) {
            throw new VerifyException('请输入菜单名称', 'name');
        }
        
        return $this;
    }
    
    
    /**
     * 设置路由地址
     * @param string $path
     * @return $this
     * @throws VerifyException
     */
    public function setPath(string $path)
    {
        $this->path = trim($path);
        $this->path = ltrim($path, '/');
        if (!$this->path) {
            throw new VerifyException('请输入菜单链接', 'path');
        }
        
        return $this;
    }
    
    
    /**
     * 设置上级路由
     * @param string $parentPath
     * @return $this
     */
    public function setParentPath(string $parentPath)
    {
        $this->parentPath = trim($parentPath);
        
        return $this;
    }
    
    
    /**
     * 设置顶级菜单默认访问链接
     * @param string $topPath
     */
    public function setTopPath(string $topPath) : void
    {
        $topPath = trim($topPath);
        if ($topPath) {
            if (0 === strpos($topPath, '#') || false !== strpos($topPath, '://')) {
                throw new VerifyException('顶级菜单访问链接不能是锚连接或外部连接');
            }
        }
        
        $this->topPath = $topPath;
    }
    
    
    /**
     * 设置附加参数
     * @param string $params
     * @return $this
     */
    public function setParams(string $params)
    {
        $this->params = trim($params);
        
        return $this;
    }
    
    
    /**
     * 设置图标
     * @param string $icon
     * @return $this
     */
    public function setIcon(string $icon)
    {
        $this->icon = trim($icon);
        
        return $this;
    }
    
    
    /**
     * 设置打开方式
     * @param string $target
     * @return $this
     */
    public function setTarget(string $target)
    {
        $this->target = trim($target);
        
        return $this;
    }
    
    
    /**
     * 设置是否隐藏
     * @param int $hide
     * @return $this
     */
    public function setHide($hide)
    {
        $this->hide = TransHelper::dataToBool($hide);
        
        return $this;
    }
    
    
    /**
     * 设置是否禁用
     * @param int $disabled
     * @return $this
     */
    public function setDisabled($disabled)
    {
        $this->disabled = TransHelper::dataToBool($disabled);
        
        return $this;
    }
}