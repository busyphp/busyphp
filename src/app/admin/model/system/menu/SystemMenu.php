<?php

namespace BusyPHP\app\admin\model\system\menu;

use BusyPHP\app\admin\model\system\file\SystemFileField;
use BusyPHP\app\admin\model\system\menu\provide\AdminMenuStruct;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\util\Regex;
use BusyPHP\helper\util\Str;
use BusyPHP\model;
use BusyPHP\helper\util\Arr;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\Request;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 后台菜单模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午2:45 下午 SystemMenu.php $
 * @method SystemMenuInfo findInfo($data = null, $notFoundMessage = null)
 * @method SystemMenuInfo getInfo($data, $notFoundMessage = null)
 * @method SystemMenuInfo[] selectList()
 */
class SystemMenu extends Model
{
    //+--------------------------------------
    //| 外部链接打开方式
    //+--------------------------------------
    /** @var string 当前窗口 */
    const TARGET_SELF = '_self';
    
    /** @var string 新建窗口 */
    const TARGET_BLANK = '_blank';
    
    /** @var string Iframe窗口 */
    const TARGET_IFRAME = 'iframe';
    
    //+--------------------------------------
    //| 类型
    //+--------------------------------------
    /** 分组 */
    const TYPE_GROUP = 0;
    
    /** 菜单 */
    const TYPE_NAV = 1;
    
    //+--------------------------------------
    //| 其它
    //+--------------------------------------
    /** 开发模式分组 */
    const DEVELOP = 'Develop';
    
    /** 保留分组 */
    const RETAIN_GROUP = 'Develop,Common';
    
    /** @var bool 开发模式 */
    const DEBUG = true;
    
    protected $dataNotFoundMessage = '菜单不存在';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = SystemMenuInfo::class;
    
    
    /**
     * 添加菜单
     * @param SystemMenuField $data
     * @return int
     * @throws DbException
     * @throws VerifyException
     */
    public function createMenu(SystemMenuField $data)
    {
        $this->buildData($data);
        
        return $this->addData($data);
    }
    
    
    /**
     * 修改菜单
     * @param SystemMenuField $data
     * @throws Exception
     */
    public function updateMenu(SystemMenuField $data)
    {
        if ($data->id < 1) {
            throw new ParamInvalidException('id');
        }
        
        $this->buildData($data);
        
        $this->startTrans();
        try {
            $info = $this->lock(true)->getInfo($data->id);
            
            if (!self::DEBUG) {
                if ($info->isSystem) {
                    throw new VerifyException('禁止修改系统菜单');
                }
            }
            
            // 找到该分类下的所有下级ID
            $list  = $this->getChildList($info->id);
            $inIds = array_keys(Arr::listByKey($list, SystemMenuField::id()));
            
            // 有下级菜单的无法转为菜单
            if ($data->type == self::TYPE_NAV && $inIds) {
                throw new VerifyException('该菜单包涵子菜单，无法从分组转为菜单');
            }
            
            // 不能选下级为上级
            if (in_array($data->parentId, $inIds)) {
                throw new VerifyException('不能选择下级做为上级');
            }
            
            // 分组则更新下级
            if ($data->type == self::TYPE_GROUP && $inIds) {
                // 1. 原来不是顶级，现在是顶级
                // 2. 原来是顶级，现在不是顶级
                if ($data->parentId == 0 || $info->parentId == 0) {
                    $this->whereEntity(SystemMenuField::id('in', $inIds))
                        ->setField(SystemMenuField::module(), $data->module);
                }
                
                // 1. 现在不是顶级
                // 2. 原来不是顶级
                elseif ($data->parentId > 0 || $info->parentId > 0) {
                    $save          = SystemMenuField::init();
                    $save->module  = $data->module;
                    $save->control = $data->control;
                    $this->whereEntity(SystemMenuField::id('in', $inIds))->saveData($save);
                }
            }
            
            
            $this->whereEntity(SystemMenuField::id($data->id))->saveData($data);
            
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            
            throw $e;
        }
    }
    
    
    /**
     * 过滤数据
     * @param SystemMenuField $data
     * @throws DataNotFoundException
     * @throws DbException
     * @throws VerifyException
     */
    private function buildData(SystemMenuField $data)
    {
        // 面板
        if ($data->parentId < 1) {
            $data->module  = self::verify($data->module, 'module', '面板分组');
            $data->type    = self::TYPE_GROUP;
            $data->control = '';
            $data->action  = '';
            $data->params  = '';
            $data->isHide  = false;
            
            if (!$data->icon) {
                throw new VerifyException('请选择面板分组图标', 'icon');
            }
        } else {
            $parentInfo = $this->getInfo($data->parentId);
            if ($parentInfo->type == self::TYPE_NAV) {
                throw new VerifyException('上级菜单必须是分组', 'parent_id');
            }
            
            // 菜单
            if ($data->type == self::TYPE_NAV) {
                $data->module = Str::studly($parentInfo->module);
                
                if ($parentInfo->control) {
                    $data->control = Str::studly($parentInfo->control);
                } else {
                    $data->control = self::verify($data->control, 'control', '控制器');
                }
                
                $data->action = trim($data->action);
                self::verify($data->action, 'action', '执行方法');
                
                if (!$data->isHide && !$data->icon) {
                    throw new VerifyException('请选择菜单图标', 'icon');
                }
            }
            
            //
            // 分组
            else {
                $data->module  = Str::studly($parentInfo->module);
                $data->control = self::verify($data->control, 'control', '控制器');
                $data->action  = '';
                
                if (!$data->icon) {
                    throw new VerifyException('请选择分组图标', 'icon');
                }
            }
        }
        
        // 新增校验保留面板
        if ($data->id < 1 && $data->parentId == 0) {
            if (in_array($data->module, array_map(function($item) {
                return Str::studly($item);
            }, explode(',', self::RETAIN_GROUP)))) {
                throw new VerifyException("{$data->module}为系统保留面板，请勿使用", 'module');
            }
        }
        
        // 查重
        $this->whereEntity(SystemMenuField::module($data->module), SystemMenuField::control($data->control), SystemMenuField::action($data->action));
        if ($data->id > 0) {
            $this->whereEntity(SystemMenuField::id('<>', $data->id));
        }
        if ($this->findInfo()) {
            throw new VerifyException('该菜单已存在，请勿重复添加');
        }
    }
    
    
    /**
     * 删除菜单
     * @param int $data 菜单ID
     * @return int
     * @throws Exception
     */
    public function deleteInfo($data) : int
    {
        $this->startTrans();
        try {
            $info = $this->lock(true)->getInfo($data);
            
            // 系统菜单不能删除
            if ($info->isSystem) {
                throw new VerifyException('系统菜单禁止删除');
            }
            
            // 删除子菜单
            $childIds = array_keys(Arr::listByKey($this->getChildList($info->id), SystemMenuField::id()));
            if ($childIds) {
                $this->whereEntity(SystemMenuField::id('in', $childIds))->delete();
            }
            
            $result = parent::deleteInfo($info->id);
            $this->commit();
            
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            
            throw $e;
        }
    }
    
    
    /**
     * 获取某菜单的所有子菜单
     * @param int $id 菜单ID
     * @return SystemMenuInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getChildList($id) : array
    {
        $list = Arr::listToTree($this->selectList(), SystemMenuField::id(), SystemMenuField::parentId(), SystemMenuInfo::child(), $id);
        $list = Arr::treeToList($list, SystemMenuInfo::child());
        
        return $list;
    }
    
    
    /**
     * 更新缓存
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function updateCache()
    {
        $this->clearCache();
        $this->getList(true);
        $this->getTreeList(true);
        $this->getSafeTree(true);
        $this->getPathList(true);
    }
    
    
    /**
     * 获取所有菜单数据
     * @param bool $must
     * @return SystemMenuInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getList(bool $must = false) : array
    {
        $cacheName = 'list';
        $list      = $this->getCache($cacheName);
        if (!$list || $must) {
            $list = $this->order(SystemMenuField::sort(), 'asc')->order(SystemMenuField::id(), 'desc')->selectList();
            $this->setCache($cacheName, $list);
        }
        
        return $list;
    }
    
    
    /**
     * 获取按照path为下标的列表
     * @param bool $must 是否强制更新缓存
     * @return SystemMenuInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getPathList(bool $must = false) : array
    {
        $cacheName = 'path';
        $list      = $this->getCache($cacheName);
        if (!$list || $must) {
            $list = Arr::listByKey($this->getList(), SystemMenuInfo::path());
            $this->setCache($cacheName, $list);
        }
        
        return $list;
    }
    
    
    /**
     * 获取菜单的树状结构
     * @param bool $must 强制更新缓存
     * @return SystemMenuInfo[]
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function getTreeList(bool $must = false) : array
    {
        $cacheName = 'tree';
        $list      = $this->getCache($cacheName);
        if (!$list || $must) {
            $list = Arr::listToTree($this->getList(), SystemMenuField::id(), SystemMenuField::parentId(), SystemMenuInfo::child());
            $this->setCache($cacheName, $list);
        }
        
        return $list;
    }
    
    
    /**
     * 获取安全的权限树
     * @param bool $must 是否强制更新缓存
     * @return SystemMenuInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getSafeTree(bool $must = false) : array
    {
        $cacheName = 'safe_tree';
        $tree      = $this->getCache($cacheName);
        if (!$tree || $must) {
            $tree = Arr::listToTree($this->getList(), SystemMenuField::id(), SystemMenuField::parentId(), SystemMenuInfo::child(), 0, function(SystemMenuInfo $item) {
                if ($item->isDisabled || $item->path == self::DEVELOP) {
                    return false;
                }
                
                return true;
            });
            $this->setCache($cacheName, $tree);
        }
        
        return $tree;
    }
    
    
    /**
     * 获取后台管理顶级菜单
     * @param int  $groupId 用户组ID
     * @param bool $must 是否强制更新缓存
     * @return AdminMenuStruct
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getAdminMenu($groupId, bool $must = true) : AdminMenuStruct
    {
        $groupInfo = AdminGroup::init()->getInfo($groupId);
        $cacheName = "admin_menu_{$groupId}";
        $struct    = $this->getCache($cacheName);
        
        if (!$struct || $must) {
            $treeList = $this->getTreeList();
            $struct   = AdminMenuStruct::init();
            
            // 系统用户组则输出所有菜单
            if ($groupInfo->isSystem) {
                foreach ($treeList as $item) {
                    // 禁用的
                    if ($item->isDisabled) {
                        continue;
                    }
                    
                    if ($item->isDefault) {
                        $struct->defaultPath = $item->path;
                    }
                    
                    $item->child        = [];
                    $struct->paths[]    = $item->path;
                    $struct->menuList[] = $item;
                }
            } else {
                foreach ($treeList as $i => $item) {
                    // 禁用的，不包含在群组规则的
                    if ($item->isDisabled || !in_array($item->path, $groupInfo->ruleArray)) {
                        continue;
                    }
                    
                    if ($item->isDefault) {
                        $struct->defaultPath = $item->path;
                    }
                    
                    $item->child        = [];
                    $struct->paths[]    = $item->path;
                    $struct->menuList[] = $item;
                }
            }
            
            if (!$struct->defaultPath) {
                $struct->defaultPath = end($struct->paths);
            }
            
            $this->setCache($cacheName, $struct);
        }
        
        // 不是系统管理员则不输出开发模式
        if (!$groupInfo->isSystem || ($groupInfo->isSystem && !app()->isDebug())) {
            $list = [];
            foreach ($struct->menuList as $item) {
                if ($item->path == self::DEVELOP) {
                    continue;
                }
                $list[] = $item;
            }
            $struct->menuList = $list;
        }
        
        return $struct;
    }
    
    
    /**
     * 获取后台管理左侧菜单
     * @param int  $groupId 权限组ID
     * @param bool $must 是否强制更新缓存
     * @return SystemMenuInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getAdminNav($groupId, bool $must = false) : array
    {
        $groupInfo = AdminGroup::init()->getInfo($groupId);
        $cacheName = "admin_nav_{$groupId}";
        
        /** @var SystemMenuInfo[] $list */
        $list = $this->getCache($cacheName);
        if (!$list || $must) {
            // 系统用户组则输出所有菜单
            if ($groupInfo->isSystem) {
                $list = Arr::listToTree($this->getList(), SystemMenuField::id(), SystemMenuField::parentId(), SystemMenuInfo::child(), 0, function(SystemMenuInfo $info) {
                    if ($info->isDisabled || $info->isHide) {
                        return false;
                    }
                    
                    return true;
                });
            } else {
                $list = Arr::listToTree($this->getList(), SystemMenuField::id(), SystemMenuField::parentId(), SystemMenuInfo::child(), 0, function(SystemMenuInfo $info) use ($groupInfo) {
                    if ($info->isDisabled || !in_array($info->path, $groupInfo->ruleArray) || $info->isHide) {
                        return false;
                    }
                    
                    return true;
                });
            }
            
            $this->setCache($cacheName, $list);
        }
        
        // 不是系统管理员则不输出开发模式
        if (!$groupInfo->isSystem || ($groupInfo->isSystem && !app()->isDebug())) {
            $temp = [];
            foreach ($list as $item) {
                if ($item->path == self::DEVELOP) {
                    continue;
                }
                $temp[] = $item;
            }
            
            $list = $temp;
        }
        
        return $list;
    }
    
    
    /**
     * 获取后台分组
     * @return string[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getGroups() : array
    {
        $list   = $this->getTreeList();
        $groups = explode(',', self::RETAIN_GROUP);
        foreach ($list as $item) {
            $groups[] = $item->path;
        }
        
        return array_values(array_unique($groups));
    }
    
    
    /**
     * 依据 URL PATH 校验该菜单是否被禁用
     * @param $path
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function checkDisabledByPath($path) : bool
    {
        $list = $this->getPathList();
        $info = $list[$path] ?? false;
        if (!$info) {
            return true;
        }
        
        return $info->isDisabled;
    }
    
    
    /**
     * 获取打开方式
     * @param string $var
     * @return array|string
     */
    public static function getTargets($var = null)
    {
        return self::parseVars(self::parseConst(self::class, 'TARGET_', [], function($item) {
            return $item['name'];
        }), $var);
    }
    
    
    /**
     * 获取当前网址的URL PATH
     * @return string
     */
    public static function getUrlPath()
    {
        /** @var Request $request */
        $request = request();
        
        return "{$request->controller()}/{$request->action()}";
    }
    
    
    /**
     * 生成URL PATH
     * @param SystemMenuInfo $info
     * @return string
     */
    public static function createUrlPath(SystemMenuInfo $info)
    {
        if ($info->module && $info->control && $info->action) {
            return "{$info->module}.{$info->control}/{$info->action}";
        } elseif ($info->module && $info->control) {
            return "{$info->module}.{$info->control}";
        } else {
            return $info->module;
        }
    }
    
    
    /**
     * 校验输入值是否正确
     * @param $value
     * @param $field
     * @param $name
     * @return string
     * @throws VerifyException
     */
    public static function verify($value, $field, $name)
    {
        $value = Str::studly(trim($value));
        if (!$value) {
            throw new VerifyException("请输入{$name}", $field);
        }
        
        if (!Regex::account($value)) {
            throw new VerifyException("{$name}只能包含字母、数字及下划线", $field);
        }
        
        if (!Regex::english(substr($value, 0, 1))) {
            throw new VerifyException("{$name}开始只能是英文", $field);
        }
        
        return $value;
    }
    
    
    /**
     * 排序菜单
     * @param int $id
     * @param int $value
     * @throws DbException
     */
    public function setSort($id, $value)
    {
        $this->whereEntity(SystemMenuField::id(intval($id)))->setField(SystemMenuField::sort(), intval($value));
    }
    
    
    /**
     * 设置是否禁用
     * @param int  $id
     * @param bool $status
     * @throws DbException
     */
    public function setDisabled($id, $status)
    {
        $this->whereEntity(SystemFileField::id(intval($id)))->setField(SystemMenuField::isDisabled(), $status ? 1 : 0);
    }
    
    
    /**
     * 设置是否隐藏
     * @param int  $id
     * @param bool $status
     * @throws DbException
     */
    public function setHide($id, $status)
    {
        $this->whereEntity(SystemFileField::id(intval($id)))->setField(SystemMenuField::isHide(), $status ? 1 : 0);
    }
    
    
    /**
     * 获取菜单选项
     * @param string $selectedValue
     * @param array  $list
     * @param string $space
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getTreeOptions($selectedValue = '', $list = [], $space = '')
    {
        $push = '├';
        if (!$list) {
            $list = $this->getTreeList();
            $push = '';
        }
        
        $options = '';
        foreach ($list as $item) {
            if ($item->type == self::TYPE_NAV || (!self::DEBUG && $item->module == self::DEVELOP)) {
                continue;
            }
            
            $selected = '';
            if ($item->id == $selectedValue) {
                $selected = ' selected="selected"';
            }
            $options .= '<option value="' . $item['id'] . '"' . $selected . '>' . $space . $push . $item->name . ' - [' . $item->path . ']</option>';
            if ($item->child) {
                $options .= $this->getTreeOptions($selectedValue, $item->child, '┊　' . $space);
            }
        }
        
        return $options;
    }
    
    
    /**
     * @param string $method
     * @param mixed  $id
     * @param array  $options
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function onChanged(string $method, $id, array $options)
    {
        $this->updateCache();
    }
}