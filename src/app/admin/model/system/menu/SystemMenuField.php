<?php

namespace BusyPHP\app\admin\model\system\menu;

use BusyPHP\exception\VerifyException;
use BusyPHP\model\Field;
use BusyPHP\helper\util\Regex;
use BusyPHP\helper\util\Transform;


/**
 * 后台菜单模型字段
 * @author busy^life <busy.life@qq.com>
 * @copyright 2015 - 2017 busy^life <busy.life@qq.com>
 * @version $Id: 2017-06-06 下午5:27 SystemMenu.php busy^life $
 */
class SystemMenuField extends Field
{
    /** @var int */
    public $id = null;
    
    /** @var string 名称 */
    public $name = null;
    
    /** @var string 执行方法 */
    public $action = null;
    
    /** @var string 控制器 */
    public $control = null;
    
    /** @var string 分组模块 */
    public $module = null;
    
    /** @var string 追加方法 */
    public $pattern = null;
    
    /** @var string 附加参数 */
    public $params = null;
    
    /** @var string 定义高亮上级 */
    public $higher = null;
    
    /** @var string 图标 */
    public $icon = null;
    
    /** @var string 外部链接 */
    public $link = null;
    
    /** @var string 打开方式 */
    public $target = null;
    
    /** @var int 默认导航面板 */
    public $isDefault = null;
    
    /** @var int 是否显示 */
    public $isShow = null;
    
    /** @var int 是否禁用 */
    public $isDisabled = null;
    
    /** @var int 是否有执行方法 */
    public $isHasAction = null;
    
    /** @var int 是否系统菜单 */
    public $isSystem = null;
    
    /** @var int 自定义排序 */
    public $sort = null;
    
    /** @var null 菜单标识 */
    private $var = null;
    
    /** @var null 菜单类型 */
    private $type = null;
    
    /** @var bool 是否校验数据 */
    private $isCheckData = true;
    
    
    /**
     * 设置
     * @param int $id
     * @return $this
     * @throws VerifyException
     */
    public function setId($id)
    {
        $this->id = floatval($id);
        if ($this->id < 1) {
            throw new VerifyException('缺少参数', 'id');
        }
        
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
     * @throws VerifyException
     */
    public function setAction($action)
    {
        $this->action = trim($action);
        if (!$this->action) {
            throw new VerifyException('请选择所属执行方法', 'action');
        }
        
        return $this;
    }
    
    
    /**
     * 设置控制器
     * @param string $control
     * @return $this
     * @throws VerifyException
     */
    public function setControl($control)
    {
        $this->control = trim($control);
        if (!$this->control) {
            throw new VerifyException('请选择所属控制器', 'control');
        }
        
        return $this;
    }
    
    
    /**
     * 设置分组模块
     * @param string $module
     * @return $this
     * @throws VerifyException
     */
    public function setModule($module)
    {
        $this->module = trim($module);
        if (!$this->module) {
            throw new VerifyException('请选择所属分组', 'module');
        }
        
        $groups = explode(',', SystemMenu::RETAIN_GROUP);
        $groups = array_map('parse_name', $groups);
        if (in_array($this->module, $groups)) {
            throw new VerifyException($module . '为系统保留值，请勿使用', 'module');
        }
        
        return $this;
    }
    
    
    /**
     * 设置追加方法
     * @param string $pattern
     * @return $this
     */
    public function setPattern($pattern)
    {
        $this->pattern = trim($pattern);
        
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
     * @param int $isShow
     * @return $this
     */
    public function setIsShow($isShow)
    {
        $this->isShow = Transform::boolToNumber($isShow);
        
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
     * 设置是否有执行方法
     * @param int $isHasAction
     * @return $this
     */
    public function setIsHasAction($isHasAction)
    {
        $this->isHasAction = Transform::boolToNumber($isHasAction);
        
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
     * 设置菜单标识
     * @param string $var 标识
     * @return $this
     * @throws VerifyException
     */
    public function setVar($var)
    {
        $var = parse_name(trim($var), 0, false);
        if (!$var) {
            throw new VerifyException('请输入菜单标识', 'var');
        }
        
        if (!Regex::account($var)) {
            throw new VerifyException('菜单标识只能包含字母、数字及下划线', 'var');
        }
        
        if (!Regex::english(substr($var, 0, 1))) {
            throw new VerifyException('菜单标识开始只能是英文', 'var');
        }
        
        $this->var = $var;
        
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
    
    
    /**
     * @return array
     * @throws VerifyException
     */
    public function getDBData()
    {
        if ($this->isCheckData) {
            switch ($this->type) {
                // 执行模式
                case SystemMenu::TYPE_PATTERN:
                    if (!$this->module) {
                        throw new VerifyException('请选择所属分组', 'module');
                    }
                    if (!$this->control) {
                        throw new VerifyException('请选择所属控制器', 'control');
                    }
                    if (!$this->action) {
                        throw new VerifyException('请选择所属执行方法', 'action');
                    }
                    
                    $this->higher = null;
                    $this->checkRepeat();
                break;
                
                // 执行方法
                case SystemMenu::TYPE_ACTION:
                    if (!$this->module) {
                        throw new VerifyException('请选择所属分组', 'module');
                    }
                    if (!$this->control) {
                        throw new VerifyException('请选择所属控制器', 'control');
                    }
                    
                    $this->pattern = null;
                    $this->checkRepeat();
                    
                    // 显示不能有高亮
                    // 不显示不能有外部链接
                    if ($this->isShow) {
                        $this->higher = null;
                    } else {
                        $this->link   = null;
                        $this->target = null;
                    }
                break;
                
                
                // 控制器
                case SystemMenu::TYPE_CONTROL:
                    if (!$this->module) {
                        throw new VerifyException('请选择所属分组', 'module');
                    }
                    
                    
                    $this->action  = null;
                    $this->pattern = null;
                    $this->higher  = null;
                    $this->checkRepeat();
                    
                    // 包含执行方法不能有外部链接
                    if ($this->isHasAction) {
                        $this->link   = null;
                        $this->target = null;
                    }
                break;
                
                
                // 模块
                case SystemMenu::TYPE_MODULE:
                default:
                    $this->control = null;
                    $this->action  = null;
                    $this->pattern = null;
                    $this->higher  = null;
                    $this->checkRepeat();
            }
        }
        
        return parent::getDBData();
    }
    
    
    /**
     * 查重
     * @throws VerifyException
     */
    private function checkRepeat()
    {
        $where          = new self();
        $where->module  = trim($this->module);
        $where->control = trim($this->control);
        $where->action  = trim($this->action);
        $where->pattern = trim($this->pattern);
        if ($this->id > 0) {
            $where->id = array('neq', $this->id);
        }
        
        if (SystemMenu::init()->whereof($where)->findData()) {
            throw new VerifyException('该菜单已存在，请勿重复添加', 'repeat');
        }
    }
    
    
    /**
     * 设置是否校验数据
     * @param bool $isCheckData
     * @return $this
     */
    public function setIsCheckData($isCheckData)
    {
        $this->isCheckData = $isCheckData;
        
        return $this;
    }
}