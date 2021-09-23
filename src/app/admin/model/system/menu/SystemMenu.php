<?php

namespace BusyPHP\app\admin\model\system\menu;

use BusyPHP\App;
use BusyPHP\app\admin\model\system\file\SystemFileField;
use BusyPHP\app\admin\model\system\menu\provide\AdminMenuStruct;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\model;
use BusyPHP\helper\util\Arr;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
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
    const TARGET_SELF = '';
    
    /** @var string 新建窗口 */
    const TARGET_BLANK = '_blank';
    
    /** @var string Iframe窗口 */
    const TARGET_IFRAME = 'iframe';
    
    //+--------------------------------------
    //| 其它
    //+--------------------------------------
    /** 开发模式分组 */
    const DEVELOP = '#developer';
    
    /** 保留分组 */
    const RETAIN_GROUP = 'Develop,Common';
    
    /** @var bool 开发模式 */
    const DEBUG = true;
    
    protected $dataNotFoundMessage = '菜单不存在';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = SystemMenuInfo::class;
    
    
    /**
     * 添加菜单
     * @param SystemMenuField $data 添加的数据
     * @param array           $auto 自动构建的菜单
     * @param string          $autoSuffix 自动创建菜单的后缀
     * @return array 增加成功的ID集合
     * @throws Exception
     */
    public function createMenu(SystemMenuField $data, array $auto = [], string $autoSuffix = '')
    {
        $this->startTrans();
        try {
            $this->checkReplace($data->path);
            $ids   = [];
            $ids[] = $this->addData($data);
            
            // 自动创建
            if ($auto) {
                if (false !== strpos($data->path, '#') || false !== strpos($data->path, '://')) {
                    throw new VerifyException('分组和外部连接不支持自动创建');
                }
                
                $parentPath = $data->path;
                $paths      = explode('/', $parentPath);
                array_pop($paths);
                $path = implode('/', $paths) . '/';
                $map  = [
                    'add'    => '添加',
                    'edit'   => '修改',
                    'delete' => '删除',
                    'sort'   => '排序',
                    'export' => '导出',
                    'import' => '导入',
                    'detail' => '查看',
                ];
                
                foreach ($auto as $item) {
                    $data->path       = $path . $item;
                    $data->name       = $map[$item] . $autoSuffix;
                    $data->hide       = true;
                    $data->parentPath = $parentPath;
                    $data->icon       = '';
                    $ids[]            = $this->addData($data);
                }
            }
            
            $this->commit();
            
            return $ids;
        } catch (Exception $e) {
            $this->rollback();
            
            throw $e;
        }
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
        
        $this->startTrans();
        try {
            $info = $this->lock(true)->getInfo($data->id);
            
            // 更新子菜单关系
            $this->whereEntity(SystemMenuField::parentPath($info->path))
                ->setField(SystemMenuField::parentPath(), $data->path);
            
            $this->whereEntity(SystemMenuField::id($data->id))->saveData($data);
            
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            
            throw $e;
        }
    }
    
    
    /**
     * 菜单连接查重
     * @param string $path 菜单连接
     * @param int    $id 菜单ID
     * @throws VerifyException
     */
    protected function checkReplace($path, $id = 0)
    {
        $this->whereEntity(SystemMenuField::path($path));
        if ($id > 0) {
            $this->whereEntity(SystemMenuField::id('<>', $id));
        }
        
        if ($this->count() > 0) {
            throw new VerifyException('该菜单连接已存在', 'path');
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
            if ($info->system) {
                throw new VerifyException('系统菜单禁止删除');
            }
            
            // 删除子菜单
            $childIds = array_keys(Arr::listByKey($this->getChildList($info->path), SystemMenuField::id()));
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
     * @param int $path 菜单连接
     * @return SystemMenuInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getChildList($path) : array
    {
        $list = Arr::listToTree($this->selectList(), SystemMenuField::path(), SystemMenuField::parentPath(), SystemMenuInfo::child(), $path);
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
     * 获取不包含禁用和系统的菜单数据
     * @return SystemMenuInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getSafeList() : array
    {
        $list = [];
        foreach ($this->getList() as $item) {
            if ($item->system || $item->disabled) {
                continue;
            }
            
            $list[] = $item;
        }
        
        return $list;
    }
    
    
    /**
     * 获取按照path为下标的列表
     * @param bool $safe 是否获取不包含禁用和系统的菜单数据
     * @return SystemMenuInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getPathList(bool $safe = false) : array
    {
        return Arr::listByKey($safe ? $this->getSafeList() : $this->getList(), SystemMenuField::path());
    }
    
    
    /**
     * 获取按照id为下标的列表
     * @param bool $safe 是否获取不包含禁用和系统的菜单数据
     * @return SystemMenuInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getIdList(bool $safe = false) : array
    {
        return Arr::listByKey($safe ? $this->getSafeList() : $this->getList(), SystemMenuField::id());
    }
    
    
    /**
     * 获取按照hash为下标的列表
     * @param bool $safe 是否获取不包含禁用和系统的菜单数据
     * @return SystemMenuInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getHashList(bool $safe = false) : array
    {
        return Arr::listByKey($safe ? $this->getSafeList() : $this->getList(), SystemMenuInfo::hash());
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
            $list = Arr::listToTree($this->getList(), SystemMenuField::path(), SystemMenuField::parentPath(), SystemMenuInfo::child(), "");
            $this->setCache($cacheName, $list);
        }
        
        return $list;
    }
    
    
    /**
     * 获取按照ID为下标的上级ID集合
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getIdParens() : array
    {
        $arr  = [];
        $list = $this->getHashList();
        foreach ($list as $item) {
            $arr[$item->id] = [];
            $this->upwardRecursion($list, $item, SystemMenuField::id(), $arr[$item->id]);
        }
        
        return $arr;
    }
    
    
    /**
     * 获取按照hash为下标的上级hash集合
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getHashParents() : array
    {
        $arr  = [];
        $list = $this->getHashList();
        foreach ($list as $item) {
            $arr[$item->hash] = [];
            $this->upwardRecursion($list, $item, SystemMenuInfo::parentHash(), $arr[$item->hash]);
        }
        
        return $arr;
    }
    
    
    /**
     * 向上递归获取ID集合
     * @param SystemMenuInfo[] $list hash为下标的列表
     * @param SystemMenuInfo   $item 菜单数据
     * @param string           $key 取值字段
     * @param array            $gather 集合
     */
    protected function upwardRecursion(array $list, SystemMenuInfo $item, $key, array &$gather = [])
    {
        if (!is_string($key)) {
            $key = (string) $key;
        }
        
        if (isset($list[$item->parentHash])) {
            $newItem  = $list[$item->parentHash];
            $gather[] = $newItem->{$key};
            $this->upwardRecursion($list, $newItem, $key, $gather);
        }
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
            $tree = Arr::listToTree($this->getList(), SystemMenuField::path(), SystemMenuField::parentPath(), SystemMenuInfo::child(), "", function(SystemMenuInfo $item) {
                if ($item->disabled || $item->system) {
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
            if ($groupInfo->system) {
                foreach ($treeList as $item) {
                    // 禁用的
                    if ($item->disabled) {
                        continue;
                    }
                    
                    /*TODO if ($item->isDefault) {
                        $struct->defaultPath = $item->path;
                    }*/
                    
                    $item->child        = [];
                    $struct->paths[]    = $item->path;
                    $struct->menuList[] = $item;
                }
            } else {
                foreach ($treeList as $i => $item) {
                    // 禁用的，不包含在群组规则的
                    if ($item->disabled || !in_array($item->path, $groupInfo->rule)) {
                        continue;
                    }
                    
                    /*TODO if ($item->isDefault) {
                        $struct->defaultPath = $item->path;
                    }*/
                    
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
        if (!$groupInfo->system || ($groupInfo->system && !app()->isDebug())) {
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
            if ($groupInfo->system) {
                $list = Arr::listToTree($this->getList(), SystemMenuField::path(), SystemMenuField::parentPath(), SystemMenuInfo::child(), "", function(SystemMenuInfo $info) {
                    if ($info->disabled || $info->hide) {
                        return false;
                    }
                    
                    return true;
                });
            } else {
                $list = Arr::listToTree($this->getList(), SystemMenuField::path(), SystemMenuField::parentPath(), SystemMenuInfo::child(), "", function(SystemMenuInfo $info) use ($groupInfo) {
                    if ($info->disabled || !in_array($info->path, $groupInfo->rule) || $info->hide) {
                        return false;
                    }
                    
                    return true;
                });
            }
            
            $this->setCache($cacheName, $list);
        }
        
        // 不是系统管理员则不输出开发模式
        if (!$groupInfo->system || ($groupInfo->system && !app()->isDebug())) {
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
     * 不包含禁用和系统
     * @return $this
     */
    public function whereSafe() : self
    {
        $this->whereEntity(SystemMenuField::disabled(0));
        $this->whereEntity(SystemMenuField::system(0));
        
        return $this;
    }
    
    
    /**
     * 排序
     * @return $this
     */
    public function orderSort() : self
    {
        $this->order(SystemMenuField::sort(), 'asc');
        $this->order(SystemFileField::id(), 'desc');
        
        return $this;
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
        
        return $info->disabled;
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
        $request = App::getInstance()->request;
        
        return "{$request->controller()}/{$request->action()}";
    }
    
    
    /**
     * 设置是否禁用
     * @param int  $id
     * @param bool $status
     * @throws DbException
     */
    public function setDisabled($id, $status)
    {
        $this->whereEntity(SystemFileField::id(intval($id)))->setField(SystemMenuField::disabled(), $status ? 1 : 0);
    }
    
    
    /**
     * 设置是否隐藏
     * @param int  $id
     * @param bool $status
     * @throws DbException
     */
    public function setHide($id, $status)
    {
        $this->whereEntity(SystemFileField::id(intval($id)))->setField(SystemMenuField::hide(), $status ? 1 : 0);
    }
    
    
    /**
     * 获取菜单选项
     * @param string $selectedPath
     * @param array  $list
     * @param string $space
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getTreeOptions($selectedPath = '', $list = [], $space = '')
    {
        $push = '├';
        if (!$list) {
            $list = $this->getTreeList();
            $push = '';
        }
        
        $options = '';
        foreach ($list as $item) {
            if (!self::DEBUG && $item->path == self::DEVELOP) {
                continue;
            }
            
            $selected = '';
            if ($item->path == $selectedPath) {
                $selected = ' selected="selected"';
            }
            $options .= '<option value="' . $item->path . '"' . $selected . '>' . $space . $push . $item->name . ' - [' . $item->path . ']</option>';
            if ($item->child) {
                $options .= $this->getTreeOptions($selectedPath, $item->child, '┊　' . $space);
            }
        }
        
        return $options;
    }
}