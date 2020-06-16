<?php

namespace BusyPHP\app\admin\model\system\menu;

use BusyPHP\exception\VerifyException;
use BusyPHP\exception\SQLException;
use BusyPHP\model;
use BusyPHP\helper\util\Arr;
use BusyPHP\app\admin\model\admin\group\AdminGroup;

/**
 * 后台菜单模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午2:45 下午 SystemMenu.php $
 */
class SystemMenu extends Model
{
    //+--------------------------------------
    //| 外部链接打开方式
    //+--------------------------------------
    /** 本窗口 */
    const TARGET_SELF = '_self';
    
    /** 新建窗口 */
    const TARGET_BLANK = '_blank';
    
    /** Iframe窗口 */
    const TARGET_IFRAME = 'iframe';
    
    //+--------------------------------------
    //| 类型
    //+--------------------------------------
    /** 分组 */
    const TYPE_MODULE = 0;
    
    /** 控制器 */
    const TYPE_CONTROL = 1;
    
    /** 执行方法 */
    const TYPE_ACTION = 2;
    
    /** 执行模式 */
    const TYPE_PATTERN = 3;
    
    //+--------------------------------------
    //| 其它
    //+--------------------------------------
    /** PATH 分隔符 */
    const DIVISION = '/';
    
    /** URL pattern name */
    const PATTERN = 'pattern';
    
    /** 开发模式分组 */
    const DEVELOP = 'Develop';
    
    /** 保留分组 */
    const RETAIN_GROUP = 'Develop,Common';
    
    
    /**
     * 获取菜单信息
     * @param int $id
     * @return array
     * @throws SQLException
     */
    public function getInfo($id)
    {
        return parent::getInfo(floatval($id), '菜单不存在');
    }
    
    
    /**
     * 添加菜单
     * @param SystemMenuField $insert
     * @return int
     * @throws SQLException
     * @throws VerifyException
     */
    public function insertData($insert)
    {
        if ($insert->isDefault) {
            $this->setFieldByIsDefault(true);
        }
        
        $data = $insert->getDBData();
        
        $this->startTrans();
        try {
            if (!$result = $this->addData($data)) {
                throw new SQLException('添加菜单失败', $this);
            }
            
            $this->commit();
            
            return $result;
        } catch (SQLException $e) {
            $this->rollback();
            
            throw new SQLException($e, $this);
        }
    }
    
    
    /**
     * 修改菜单
     * @param SystemMenuField $update
     * @throws SQLException
     * @throws VerifyException
     */
    public function updateData($update)
    {
        // 默认导航面板
        if ($update->isDefault) {
            $this->setFieldByIsDefault(true);
        }
        $data = $update->getDBData();
        
        
        $this->startTrans();
        try {
            $info = $this->getInfo($update->id);
            
            // 执行修改
            if (false === $this->saveData($data)) {
                throw new SQLException('修改菜单失败', $this);
            }
            
            
            // 移动子菜单
            switch ($info['type']) {
                // 分组变更
                case self::TYPE_MODULE:
                    $where         = SystemMenuField::init();
                    $where->module = $info['module'];
                    
                    $save         = SystemMenuField::init();
                    $save->module = $update->module;
                    $save->setIsCheckData(false);
                    if (false === $this->whereof($where)->saveData($save)) {
                        throw new SQLException('所属分组变更失败', $this);
                    }
                break;
                
                
                // 控制器变更
                case self::TYPE_CONTROL:
                    $where          = SystemMenuField::init();
                    $where->module  = $info['module'];
                    $where->control = $info['control'];
                    
                    $save          = SystemMenuField::init();
                    $save->module  = $update->module;
                    $save->control = $update->control;
                    $save->setIsCheckData(false);
                    if (false === $this->whereof($where)->saveData($save)) {
                        throw new SQLException('所属分组和控制器变更失败', $this);
                    }
                break;
                
                // 执行方法变更
                case self::TYPE_ACTION:
                    $where          = SystemMenuField::init();
                    $where->module  = $info['module'];
                    $where->control = $info['control'];
                    $where->action  = $info['action'];
                    
                    $save          = SystemMenuField::init();
                    $save->module  = $update->module;
                    $save->control = $update->control;
                    $save->action  = $update->action;
                    $save->setIsCheckData(false);
                    if (false === $this->whereof($where)->saveData($save)) {
                        throw new SQLException('所属执行方法变更失败', $this);
                    }
                break;
            }
            
            $this->commit();
        } catch (SQLException $e) {
            $this->rollback();
            throw new SQLException($e, $this);
        }
    }
    
    
    /**
     * 删除菜单
     * @param int $id
     * @throws SQLException
     * @throws VerifyException
     */
    public function del($id)
    {
        $this->startTrans();
        try {
            $info = $this->lock(true)->getInfo($id);
            
            // 系统菜单不能删除
            if ($info['is_system']) {
                throw new VerifyException('系统菜单禁止删除');
            }
            
            switch ($info['type']) {
                // 删除分组下的所有子类
                case self::TYPE_MODULE:
                    $where         = SystemMenuField::init();
                    $where->module = $info['module'];
                    $where->id     = ['neq', $info['id']];
                    if (false === $this->whereof($where)->deleteData()) {
                        throw new SQLException('删除分组下的所有菜单失败', $this);
                    }
                break;
                
                // 删除控制器下的所有子类
                case self::TYPE_CONTROL:
                    $where          = SystemMenuField::init();
                    $where->module  = $info['module'];
                    $where->control = $info['control'];
                    $where->id      = ['neq', $info['id']];
                    if (false === $this->whereof($where)->deleteData()) {
                        throw new SQLException('删除控制器下的所有菜单失败', $this);
                    }
                break;
                
                // 删除方法下的所有子类
                case self::TYPE_ACTION:
                    $where          = SystemMenuField::init();
                    $where->module  = $info['module'];
                    $where->control = $info['control'];
                    $where->action  = $info['action'];
                    $where->id      = ['neq', $info['id']];
                    if (false === $this->whereof($where)->deleteData()) {
                        throw new SQLException('删除方法下的所有菜单失败', $this);
                    }
                break;
            }
            
            $result = $this->deleteData($id);
            if (false === $result) {
                throw new SQLException('删除菜单失败', $this);
            }
            
            $this->commit();
        } catch (VerifyException $e) {
            $this->rollback();
            
            throw new VerifyException($e);
        } catch (SQLException $e) {
            $this->rollback();
            
            throw new SQLException($e, $this);
        }
    }
    
    
    /**
     * 设置默认导航面板
     * @param int|true $id 如果是true则只清理
     * @throws SQLException
     */
    public function setFieldByIsDefault($id)
    {
        // 如果是true则清理已有的默认导航面板
        if ($id === true) {
            $where            = SystemMenuField::init();
            $where->isDefault = 1;
            $save             = SystemMenuField::init();
            $save->setIsCheckData(false);
            $save->isDefault = 0;
            if (false === $this->whereof($where)->saveData($save)) {
                throw new SQLException('清理默认面板失败', $this);
            }
            
            return;
        }
        
        $this->setFieldByIsDefault(true);
        $where     = SystemMenuField::init();
        $where->id = floatval($id);
        
        
        $save = SystemMenuField::init();
        $save->setIsCheckData(false);
        $save->isDefault = 1;
        $result          = $this->whereof($where)->saveData($save);
        if (false === $result) {
            throw new SQLException('设置默认面板失败', $this);
        }
    }
    
    
    /**
     * 更新缓存
     */
    public function updateCache()
    {
        $this->clearCache();
        $this->getTreeList(true);
        $this->getSafeTree(true);
        $this->getPathList(true);
    }
    
    
    /**
     * 获取模块分组列表
     * @return array
     */
    public function getModuleList()
    {
        $where          = SystemMenuField::init();
        $where->action  = '';
        $where->control = '';
        $where->module  = ['neq', ''];
        $list           = $this->whereof($where)->order('sort ASC')->selecting();
        
        return $list ? $list : [];
    }
    
    
    /**
     * 获取控制器列表
     * @param string $moduleName 分组标识
     * @return array
     */
    public function getControlList($moduleName = '')
    {
        $moduleName     = trim($moduleName);
        $where          = SystemMenuField::init();
        $where->action  = '';
        $where->control = ['neq', ''];
        
        if ($moduleName) {
            $where->module = $moduleName;
        } else {
            $where->module = ['neq', $moduleName];
        }
        
        $list = $this->whereof($where)->order('sort ASC')->selecting();
        
        return $list ? $list : [];
    }
    
    
    /**
     * 获取执行方法列表
     * @param string $moduleName 分组标识
     * @param string $controlName 控制器标识
     * @return array
     */
    public function getActionList($moduleName = '', $controlName = '')
    {
        $moduleName    = trim($moduleName);
        $controlName   = trim($controlName);
        $where         = SystemMenuField::init();
        $where->action = ['neq', ''];
        
        if ($moduleName) {
            $where->module = $moduleName;
        } else {
            $where->module = ['neq', ''];
        }
        
        if ($controlName) {
            $where->control = $controlName;
        } else {
            $where->control = ['neq', $controlName];
        }
        
        $list = $this->whereof($where)->order('sort ASC')->selecting();
        
        return $list ? $list : [];
    }
    
    
    /**
     * 获取按照path为下标的列表
     * @param bool $must 是否强制更新缓存
     * @return array
     */
    public function getPathList($must = false)
    {
        $cacheName = 'path';
        $list      = $this->getCache($cacheName);
        if (!$list || $must) {
            $list = $this->order('sort ASC')->selecting();
            $list = self::parseList($list);
            $list = Arr::listByKey($list, 'path');
            $this->setCache($cacheName, $list);
        }
        
        return $list;
    }
    
    
    /**
     * 获取菜单的树状结构
     * @param bool $must 强制更新缓存
     * @return array
     */
    public function getTreeList($must = false)
    {
        $cacheName = 'tree';
        $list      = $this->getCache($cacheName);
        if (!$list || $must) {
            $list  = $this->getModuleList();
            $total = count($list);
            foreach ($list as $one => $oneArray) {
                $oneArray          = self::parseFirstLast($oneArray, $one, $total);
                $oneArray          = self::parseInfo($oneArray);
                $oneArray['child'] = $this->getControlList($oneArray['module']);
                $twoTotal          = count($oneArray['child']);
                
                
                // 遍历二级菜单
                foreach ($oneArray['child'] as $two => $twoArray) {
                    $twoArray          = self::parseFirstLast($twoArray, $two, $twoTotal);
                    $twoArray          = self::parseInfo($twoArray);
                    $twoArray['child'] = $this->getActionList($twoArray['module'], $twoArray['control']);
                    $threeTotal        = count($twoArray['child']);
                    
                    // 遍历三级菜单
                    foreach ($twoArray['child'] as $three => $threeArray) {
                        $threeArray                = self::parseFirstLast($threeArray, $three, $threeTotal);
                        $threeArray                = self::parseInfo($threeArray);
                        $twoArray['child'][$three] = $threeArray;
                    }
                    $oneArray['child'][$two] = $twoArray;
                }
                $list[$one] = $oneArray;
            }
            
            if (!$list) {
                return [];
            }
            $this->setCache($cacheName, $list);
        }
        
        return $list;
    }
    
    
    /**
     * 获取安全的权限树
     * @param bool  $must 是否强制更新缓存
     * @param array $list
     * @return array
     */
    public function getSafeTree($must = false, $list = [])
    {
        $cacheName = 'safe_tree';
        $tree      = $this->getCache($cacheName);
        if (!$tree || $must) {
            $list = $list ?: $this->getTreeList();
            $tree = [];
            
            foreach ($list as $i => $r) {
                if (intval($r['is_disabled']) || $r['path'] == self::DEVELOP) {
                    continue;
                }
                
                if ($r['child']) {
                    $r['child'] = $this->getSafeTree(true, $r['child']);
                }
                
                $tree[$i] = $r;
            }
            $this->setCache($cacheName, $tree);
        }
        
        return $tree;
    }
    
    
    /**
     * 获取后台管理顶级菜单
     * @param int  $groupId 用户组ID
     * @param bool $must 是否强制更新缓存
     * @return array
     */
    public function getAdminMenu($groupId, $must = true)
    {
        try {
            $groupInfo = AdminGroup::init()->getInfo($groupId);
            $cacheName = "admin_menu_{$groupId}";
            $list      = $this->getCache($cacheName);
            
            if (!$list || $must) {
                $treeList    = $this->getTreeList();
                $list        = [];
                $defaultPath = '';
                $pathArray   = [];
                
                // 系统用户组则输出所有菜单
                if ($groupInfo['is_system']) {
                    foreach ($treeList as $i => $r) {
                        // 禁用的
                        if (intval($r['is_disabled'])) {
                            continue;
                        }
                        
                        if ($r['is_default']) {
                            $defaultPath = $r['path'];
                        }
                        
                        unset($r['child']);
                        $pathArray[] = $r['path'];
                        $list[]      = $r;
                    }
                } else {
                    foreach ($treeList as $i => $r) {
                        if (intval($r['is_disabled']) || !in_array($r['path'], $groupInfo['rule_array'])) {
                            continue;
                        }
                        
                        if ($r['is_default']) {
                            $defaultPath = $r['path'];
                        }
                        
                        unset($r['child']);
                        $pathArray[] = $r['path'];
                        $list[]      = $r;
                    }
                }
                
                if (!$defaultPath) {
                    $endArray    = end($list);
                    $defaultPath = $endArray['path'];
                }
                
                $list = [
                    'menu_list' => $list,
                    'keys'      => $pathArray,
                    'default'   => $defaultPath
                ];
                $this->setCache($cacheName, $list);
            }
            
            // 非调试禁止输出开发模式
            if ($groupInfo['is_system']) {
                if (!$this->app->isDebug()) {
                    $array = [];
                    foreach ($list['menu_list'] as $i => $r) {
                        if ($r['path'] == self::DEVELOP) {
                            continue;
                        }
                        $array[] = $r;
                    }
                    $list['menu_list'] = $array;
                    unset($array);
                }
            }
            
            return $list;
        } catch (SQLException $e) {
            return [];
        }
    }
    
    
    /**
     * 获取后台管理左侧菜单
     * @param int  $groupId 权限组ID
     * @param bool $must 是否强制更新缓存
     * @return array
     */
    public function getAdminNav($groupId, $must = false)
    {
        try {
            $groupInfo = AdminGroup::init()->getInfo($groupId);
            $cacheName = "admin_nav_{$groupId}";
            $list      = $this->getCache($cacheName);
            if (!$list || $must) {
                $treeList = $this->getTreeList();
                $list     = [];
                
                // 系统用户组则输出所有菜单
                if ($groupInfo['is_system']) {
                    // 一级遍历
                    foreach ($treeList as $one => $oneArray) {
                        // 禁用的
                        if (intval($oneArray['is_disabled'])) {
                            continue;
                        }
                        
                        
                        // 二级遍历
                        $oneChild = [];
                        foreach ($oneArray['child'] as $two => $twoArray) {
                            // 禁用的
                            if (intval($twoArray['is_disabled'])) {
                                continue;
                            }
                            
                            // 三级遍历
                            $twoChild = [];
                            foreach ($twoArray['child'] as $three => $threeArray) {
                                // 隐藏的，禁用的
                                if (!intval($threeArray['is_show']) || intval($threeArray['is_disabled'])) {
                                    continue;
                                }
                                $twoChild[] = $threeArray;
                            }
                            
                            $twoArray['child'] = $twoChild;
                            $oneChild[]        = $twoArray;
                        }
                        
                        $oneArray['child'] = $oneChild;
                        $list[]            = $oneArray;
                    }
                } else {
                    // 一级遍历
                    foreach ($treeList as $one => $oneArray) {
                        // 禁用的
                        if (intval($oneArray['is_disabled']) || !in_array($oneArray['path'], $groupInfo['rule_array'])) {
                            continue;
                        }
                        
                        
                        // 二级遍历
                        $oneChild = [];
                        foreach ($oneArray['child'] as $two => $twoArray) {
                            // 禁用的
                            if (intval($twoArray['is_disabled']) || !in_array($twoArray['path'], $groupInfo['rule_array'])) {
                                continue;
                            }
                            
                            // 三级遍历
                            $twoChild = [];
                            foreach ($twoArray['child'] as $three => $threeArray) {
                                // 隐藏的，禁用的
                                if (!intval($threeArray['is_show']) || intval($threeArray['is_disabled']) || !in_array($threeArray['path'], $groupInfo['rule_array'])) {
                                    continue;
                                }
                                $twoChild[] = $threeArray;
                            }
                            
                            $twoArray['child'] = $twoChild;
                            $oneChild[]        = $twoArray;
                        }
                        
                        $oneArray['child'] = $oneChild;
                        $list[]            = $oneArray;
                    }
                }
                
                
                $this->setCache($cacheName, $list);
            }
            
            // 非调试禁止输出开发模式
            if ($groupInfo['is_system']) {
                if (!$this->app->isDebug()) {
                    $array = [];
                    foreach ($list as $i => $r) {
                        if ($r['path'] == self::DEVELOP) {
                            continue;
                        }
                        $array[] = $r;
                    }
                    $list = $array;
                    unset($array);
                }
            }
            
            return $list;
        } catch (SQLException $e) {
            return [];
        }
    }
    
    
    /**
     * 获取后台分组
     * @return string
     */
    public function getAdminGroups()
    {
        $list  = $this->getTreeList();
        $array = explode(',', self::RETAIN_GROUP);
        foreach ($list as $i => $r) {
            $array[] = $r['path'];
        }
        $array = array_unique($array);
        
        return implode(',', $array);
    }
    
    
    /**
     * 依据 URL PATH 校验该菜单是否被禁用
     * @param $path
     * @return bool
     */
    public function checkIsDisabledByPath($path)
    {
        $array = $this->getPathList();
        $info  = $array[$path];
        if (!$info) {
            return true;
        }
        
        return $info['is_disabled'] == 1;
    }
    
    
    /**
     * 获取打开方式
     * @param null $var
     * @return array|mixed
     */
    public static function getTargets($var = null)
    {
        $array = [
            self::TARGET_SELF   => '当前窗口',
            self::TARGET_BLANK  => '新建窗口',
            self::TARGET_IFRAME => 'iframe 窗口',
        ];
        if (is_null($var)) {
            return $array;
        }
        
        return $array[$var];
    }
    
    
    /**
     * 获取菜单类型
     * @param null|int $var
     * @return array|mixed
     */
    public static function getTypes($var = null)
    {
        $array = [
            self::TYPE_MODULE  => '分组',
            self::TYPE_CONTROL => '控制器',
            self::TYPE_ACTION  => '执行方法',
            self::TYPE_PATTERN => '执行模式',
        ];
        if (is_null($var)) {
            return $array;
        }
        
        return $array[$var];
    }
    
    
    /**
     * 解析开始和结束标记
     * @param array $info
     * @param int   $index
     * @param int   $total
     * @return array
     */
    public static function parseFirstLast($info, $index, $total)
    {
        if ($index === 0) {
            $info['is_first'] = 1;
        } else {
            $info['is_first'] = 0;
        }
        
        if ($index == $total - 1) {
            $info['is_last'] = 1;
        } else {
            $info['is_last'] = 0;
        }
        
        return $info;
    }
    
    
    /**
     * 解析菜单数据
     * @param array $list
     * @return array
     */
    public static function parseList($list)
    {
        foreach ($list as $i => $info) {
            $module  = parse_name($info['module'], true, true);
            $control = parse_name($info['control'], true, true);
            $isUrl   = false;
            
            // 层级解析
            $info['path'] = '';
            if ($info['pattern'] && $info['action'] && $info['control'] && $info['module']) {
                $info['path']  .= $module;
                $info['path']  .= '.';
                $info['path']  .= $control;
                $info['path']  .= self::DIVISION;
                $info['path']  .= $info['action'];
                $info['path']  .= '?' . self::PATTERN;
                $info['path']  .= '=' . $info['pattern'];
                $info['level'] = 2;
                $info['type']  = self::TYPE_PATTERN;
                $info['var']   = $info['pattern'];
                $isUrl         = true;
            } elseif ($info['action'] && $info['control'] && $info['module']) {
                $info['path']  .= $module;
                $info['path']  .= '.';
                $info['path']  .= $control;
                $info['path']  .= self::DIVISION;
                $info['path']  .= $info['action'];
                $info['level'] = 2;
                $info['type']  = self::TYPE_ACTION;
                $info['var']   = $info['action'];
                $isUrl         = true;
            } elseif ($info['control'] && $info['module']) {
                $info['path'] .= $module;
                $info['path'] .= '.';
                $info['path'] .= $control;
                if (!intval($info['is_has_action'])) {
                    $info['path'] .= self::DIVISION;
                    $info['path'] .= 'index';
                    $isUrl        = true;
                }
                $info['level'] = 1;
                $info['type']  = self::TYPE_CONTROL;
                $info['var']   = $info['control'];
            } else {
                $info['path']  .= $module;
                $info['level'] = 0;
                $info['type']  = self::TYPE_MODULE;
                $info['var']   = $info['module'];
            }
            
            // 解析打开URL
            $info['url'] = '';
            if ($isUrl) {
                if ($info['link']) {
                    if ($info['target'] == self::TARGET_IFRAME) {
                        $info['url'] = (string) url($info['path'], ['iframe' => 1]);
                    } else {
                        $info['url'] = $info['link'];
                    }
                } else {
                    $info['target'] = '';
                    $info['url']    = (string) url($info['path']);
                }
            }
            
            $info['is_system'] = intval($info['is_system']) > 0;
            $list[$i]          = $info;
        }
        
        return parent::parseList($list);
    }
    
    
    /**
     * 获取当前网址的URL PATH
     * @return array|string
     */
    public static function getUrlPath()
    {
        $path = '';
        if ('' !== GROUP_NAME) {
            $path .= GROUP_NAME . '.';
        }
        $path    .= MODULE_NAME . self::DIVISION . ACTION_NAME;
        $pattern = self::getUrlPattern();
        if ($pattern) {
            $path .= '?' . self::PATTERN . '=' . $pattern;
        }
        
        return $path;
    }
    
    
    /**
     * 获取当前网址 URL Pattern
     * @return string
     */
    public static function getUrlPattern()
    {
        if (isset($_REQUEST[self::PATTERN])) {
            return rawurldecode(trim($_REQUEST[self::PATTERN]));
        }
        
        return '';
    }
    
    
    /**
     * 排序菜单
     * @param $id
     * @param $value
     * @throws VerifyException
     */
    public function setSort($id, $value)
    {
        $save = SystemMenuField::init();
        $save->setId($id);
        $save->setSort($value);
        $save->setIsCheckData(false);
        $this->saveData($save);
    }
}