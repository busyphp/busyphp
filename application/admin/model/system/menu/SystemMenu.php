<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\menu;

use BusyPHP\App;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\app\admin\model\system\file\SystemFileField;
use BusyPHP\helper\ClassHelper;
use BusyPHP\model;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\model\Entity;
use RuntimeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use Throwable;

/**
 * 后台菜单模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午2:45 下午 SystemMenu.php $
 * @method SystemMenuInfo getInfo(int $id, string $notFoundMessage = null)
 * @method SystemMenuInfo|null findInfo(int $id = null, string $notFoundMessage = null)
 * @method SystemMenuInfo[] selectList()
 * @method SystemMenuInfo[] buildListWithField(array $values, string|Entity $key = null, string|Entity $field = null)
 * @method static string|SystemMenu getClass()
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
    
    /** @var string 操作场景-自动创建子菜单 */
    public const SCENE_AUTO_CREATE = 'auto_create';
    
    /** @var bool 开发模式 */
    const DEBUG = false;
    
    protected $dataNotFoundMessage = '菜单不存在';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = SystemMenuInfo::class;
    
    /**
     * @var string[]
     */
    protected $autoCreateMap = [
        'add'    => '添加',
        'edit'   => '修改',
        'delete' => '删除',
        'sort'   => '排序',
        'export' => '导出',
        'import' => '导入',
        'detail' => '查看',
    ];
    
    
    /**
     * @inheritDoc
     */
    final protected static function defineClass() : string
    {
        return self::class;
    }
    
    
    /**
     * 获取打开方式
     * @param string|null $var
     * @return array|string
     */
    public static function getTargets(string $var = null)
    {
        return ArrayHelper::getValueOrSelf(ClassHelper::getConstAttrs(self::class, 'TARGET_', ClassHelper::ATTR_NAME), $var);
    }
    
    
    /**
     * 快速添加菜单
     * @param string $path 路径
     * @param string $name 名称
     * @param string $parentPath 上级路径
     * @param string $icon 图标
     * @param bool   $hide 是否隐藏
     * @param int    $sort 排序
     * @param string $params GET参数
     * @return int[]
     * @throws Throwable
     * @deprecated 未来某个版本会删除
     * @see SystemMenu::createInfo()
     */
    public function addMenu(string $path, string $name, string $parentPath = '', string $icon = '', bool $hide = false, int $sort = 50, string $params = '') : array
    {
        $data = SystemMenuField::init();
        $data->setParentPath($parentPath);
        $data->setName($name);
        $data->setIcon($icon);
        $data->setPath($path);
        $data->setParams($params);
        $data->setHide($hide);
        $data->setSort($sort);
        
        return $this->createInfo($data, [], '', true);
    }
    
    
    /**
     * 添加菜单
     * @param SystemMenuField $data 添加的数据
     * @param array           $auto 自动构建的菜单
     * @param string          $autoSuffix 自动创建菜单的后缀
     * @param bool            $disabledTrans 是否禁用事物
     * @return int[] 增加成功的ID集合
     * @throws Throwable
     */
    public function createInfo(SystemMenuField $data, array $auto = [], string $autoSuffix = '', $disabledTrans = false) : array
    {
        $autoSuffix = trim($autoSuffix);
        
        return $this->transaction(function() use ($data, $auto, $autoSuffix) {
            $ids   = [];
            $ids[] = (int) $this->validate($data, self::SCENE_CREATE)->addData();
            
            // 自动创建
            if ($auto) {
                if (false !== strpos($data->path, '#') || false !== strpos($data->path, '://')) {
                    throw new RuntimeException('分组和外部连接不支持自动创建');
                }
                
                $parentPath = $data->path;
                $paths      = explode('/', $parentPath);
                array_pop($paths);
                $path = implode('/', $paths) . '/';
                
                foreach ($auto as $item) {
                    if (!isset($this->autoCreateMap[$item])) {
                        continue;
                    }
                    
                    $data->setPath($path . $item);
                    $data->setName($this->autoCreateMap[$item] . $autoSuffix);
                    $data->setHide(true);
                    $data->setParentPath($parentPath);
                    $ids[] = (int) $this->validate($data, self::SCENE_AUTO_CREATE)->addData($data);
                }
            }
            
            return $ids;
        }, $disabledTrans);
    }
    
    
    /**
     * 修改菜单
     * @param SystemMenuField $data
     * @throws Throwable
     */
    public function updateInfo(SystemMenuField $data)
    {
        $this->transaction(function() use ($data) {
            $info = $this->lock(true)->getInfo($data->id);
            
            $this->validate($data, self::SCENE_UPDATE, $info)->data([]);
            
            // 更新子菜单关系
            $this->whereEntity(SystemMenuField::parentPath($info->path))
                ->setField(SystemMenuField::parentPath(), $data->path);
            
            $this->saveData($data);
        });
    }
    
    
    /**
     * 删除菜单
     * @param int  $data 菜单ID
     * @param bool $disabledTrans 是否禁用事物
     * @return int
     * @throws Throwable
     */
    public function deleteInfo($data, bool $disabledTrans = false) : int
    {
        $id = (int) $data;
        
        return $this->transaction(function() use ($id) {
            $info = $this->lock(true)->getInfo($id);
            
            if ($info->system) {
                throw new RuntimeException('系统菜单禁止删除');
            }
            
            // 删除子菜单
            $childIds   = array_column($this->getAllChildList($info->path), SystemFileField::id()->name());
            $childIds[] = $info->id;
            
            return $this->whereEntity(SystemMenuField::id('in', $childIds))->delete();
        }, $disabledTrans);
    }
    
    
    /**
     * 通过路径删除菜单
     * @param string $path 菜单路径
     * @param bool   $disabledTrans 是否禁用事物
     * @return int
     * @throws Throwable
     */
    public function deleteByPath(string $path, bool $disabledTrans = false) : int
    {
        $info = $this->whereEntity(SystemFileField::path($path))->findInfo();
        if (!$info) {
            return 0;
        }
        
        return $this->deleteInfo($info->id, $disabledTrans);
    }
    
    
    /**
     * 设置排序
     * @param array $data
     * @throws DbException
     */
    public function setSort(array $data)
    {
        $saveAll = [];
        foreach ($data as $id => $value) {
            $item       = SystemMenuField::init();
            $item->id   = $id;
            $item->sort = $value;
            $saveAll[]  = $item;
        }
        
        if ($saveAll) {
            $this->saveAll($saveAll);
        }
    }
    
    
    /**
     * 获取某菜单下的所有子节点菜单
     * @param string $path 菜单连接
     * @return SystemMenuInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getAllChildList(string $path) : array
    {
        $list = ArrayHelper::listToTree(
            $this->selectList(),
            SystemMenuField::path()->name(),
            SystemMenuField::parentPath()->name(),
            SystemMenuInfo::child()->name(),
            $path
        );
        
        return ArrayHelper::treeToList($list, SystemMenuInfo::child()->name());
    }
    
    
    /**
     * 获取某菜单下的子菜单
     * @param string $path 菜单路径
     * @param bool   $self 是否含自己
     * @param bool   $hide 是否只查询隐藏的菜单
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getChildList(string $path, bool $self = false, bool $hide = false) : array
    {
        return $this
            ->where(function(SystemMenu $model) use ($path, $self, $hide) {
                $model->whereOr(function(SystemMenu $model) use ($path, $hide) {
                    $model->whereEntity(SystemMenuField::parentPath($path));
                    $model->whereEntity(SystemMenuField::hide($hide));
                });
                
                if ($self) {
                    $model->whereOr(SystemFileField::path(), $path);
                }
            })
            ->order(SystemMenuField::sort(), 'asc')
            ->order(SystemMenuField::id(), 'asc')
            ->orderRaw(sprintf('field(`%s`, "%s") asc', SystemMenuField::path(), $path))
            ->selectList();
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
            $list = $this->order(SystemMenuField::sort(), 'asc')->order(SystemMenuField::id(), 'asc')->selectList();
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
        return ArrayHelper::listByKey($safe ? $this->getSafeList() : $this->getList(), SystemMenuField::path()->name());
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
        return ArrayHelper::listByKey($safe ? $this->getSafeList() : $this->getList(), SystemMenuField::id()->name());
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
        return ArrayHelper::listByKey($safe ? $this->getSafeList() : $this->getList(), SystemMenuInfo::hash()->name());
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
            $list = ArrayHelper::listToTree(
                $this->getList(),
                SystemMenuField::path()->name(),
                SystemMenuField::parentPath()->name(),
                SystemMenuInfo::child()->name(),
                ""
            );
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
            $tree = ArrayHelper::listToTree(
                $this->getList(),
                SystemMenuField::path()->name(),
                SystemMenuField::parentPath()->name(),
                SystemMenuInfo::child()->name(),
                "",
                function(SystemMenuInfo $item) {
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
     * 获取后台菜单
     * @param AdminUserInfo $adminUserInfo 用户信息
     * @return SystemMenuInfo[]
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getNav(AdminUserInfo $adminUserInfo) : array
    {
        $parentsIdsList = $this->getIdParens();
        $list           = $this->getIdList();
        
        return ArrayHelper::listToTree(
            $list,
            SystemMenuField::path()->name(),
            SystemMenuField::parentPath()->name(),
            SystemMenuInfo::child()->name(),
            "",
            function(SystemMenuInfo $info) use ($adminUserInfo, $parentsIdsList, $list) {
                if ($info->hide && isset($parentsIdsList[$info->id])) {
                    $parentId = array_shift($parentsIdsList[$info->id]);
                    if (isset($list[$parentId])) {
                        $list[$parentId]->hides[] = $info;
                    }
                }
                
                // 禁用和隐藏的菜单不输出
                if ($info->disabled || $info->hide) {
                    return false;
                }
                
                // 系统管理员
                if ($adminUserInfo->groupHasSystem) {
                    // 系统菜单在非开发模式下不输出
                    if ($info->system && !App::getInstance()->isDebug()) {
                        return false;
                    }
                } else {
                    // 不在规则内
                    // 不是系统菜单
                    if (!in_array($info->id, $adminUserInfo->groupRuleIds) || $info->system) {
                        return false;
                    }
                }
                
                $list[$info->id] = $info;
                
                return true;
            });
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
        $this->order(SystemFileField::id(), 'asc');
        
        return $this;
    }
    
    
    /**
     * 设置是否禁用
     * @param int  $id
     * @param bool $status
     * @throws DbException
     */
    public function setDisabled(int $id, bool $status)
    {
        $this->whereEntity(SystemFileField::id(intval($id)))->setField(SystemMenuField::disabled(), $status ? 1 : 0);
    }
    
    
    /**
     * 设置是否隐藏
     * @param int  $id
     * @param bool $status
     * @throws DbException
     */
    public function setHide(int $id, bool $status)
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
    public function getTreeOptions($selectedPath = '', $list = [], $space = '') : string
    {
        $push = '├';
        if (!$list) {
            $list = $this->getTreeList();
            $push = '';
        }
        
        $options = '';
        foreach ($list as $item) {
            if (!self::DEBUG && $item->system) {
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