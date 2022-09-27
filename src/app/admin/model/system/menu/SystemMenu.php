<?php
declare (strict_types = 1);

namespace BusyPHP\app\admin\model\system\menu;

use BusyPHP\App;
use BusyPHP\app\admin\model\admin\user\AdminUserInfo;
use BusyPHP\app\admin\model\system\file\SystemFileField;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\exception\VerifyException;
use BusyPHP\model;
use BusyPHP\helper\ArrayHelper;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

/**
 * 后台菜单模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/5/28 下午2:45 下午 SystemMenu.php $
 * @method SystemMenuInfo findInfo($data = null, $notFoundMessage = null)
 * @method SystemMenuInfo getInfo($data, $notFoundMessage = null)
 * @method SystemMenuInfo[] selectList()
 * @method SystemMenuInfo[] buildListWithField(array $values, $key = null, $field = null) : array
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
    
    /** @var bool 开发模式 */
    const DEBUG = false;
    
    protected $dataNotFoundMessage = '菜单不存在';
    
    protected $findInfoFilter      = 'intval';
    
    protected $bindParseClass      = SystemMenuInfo::class;
    
    
    /**
     * 快速添加菜单
     * @param string $path 路径
     * @param string $name 名称
     * @param string $parentPath 上级路径
     * @param string $icon 图标
     * @param bool   $hide 是否隐藏
     * @param int    $sort 排序
     * @param string $params GET参数
     * @return array
     * @throws Exception
     */
    public function addMenu(string $path, string $name, string $parentPath = '', string $icon = '', bool $hide = false, int $sort = 50, string $params = '')
    {
        $data = SystemMenuField::init();
        $data->setParentPath($parentPath);
        $data->setName($name);
        $data->setIcon($icon);
        $data->setPath($path);
        $data->setParams($params);
        $data->setHide($hide);
        $data->sort = $sort;
        
        return $this->createMenu($data, [], '', true);
    }
    
    
    /**
     * 添加菜单
     * @param SystemMenuField $data 添加的数据
     * @param array           $auto 自动构建的菜单
     * @param string          $autoSuffix 自动创建菜单的后缀
     * @param bool            $disabledTrans 是否禁用事物
     * @return array 增加成功的ID集合
     * @throws Exception
     */
    public function createMenu(SystemMenuField $data, array $auto = [], string $autoSuffix = '', $disabledTrans = false)
    {
        $autoSuffix = trim($autoSuffix);
        $this->startTrans($disabledTrans);
        try {
            $this->checkData($data);
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
            
            $this->commit($disabledTrans);
            
            return $ids;
        } catch (Exception $e) {
            $this->rollback($disabledTrans);
            
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
            if ($info->system) {
                $data->system = true;
            }
            
            $this->checkData($data, $data->id);
            
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
     * 菜单数据校验
     * @param SystemMenuField $data 菜单数据
     * @param int             $id 菜单ID
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function checkData(SystemMenuField $data, $id = 0)
    {
        $this->whereEntity(SystemMenuField::path($data->path));
        if ($id > 0) {
            $this->whereEntity(SystemMenuField::id('<>', $id));
        }
        
        if ($this->findInfo()) {
            throw new VerifyException('该菜单连接已存在', 'path');
        }
        
        if ($data->topPath) {
            if (!$this->whereEntity(SystemMenuField::path($data->topPath))->findInfo()) {
                throw new VerifyException('顶级菜单访问链接不存在', 'top_path');
            }
        }
    }
    
    
    /**
     * 删除菜单
     * @param int  $data 菜单ID
     * @param bool $disabledTrans 是否禁用事物
     * @return int
     * @throws Exception
     */
    public function deleteInfo($data, bool $disabledTrans = false) : int
    {
        $this->startTrans($disabledTrans);
        try {
            $info = $this->lock(true)->findInfo($data);
            if (!$info) {
                $result = 0;
                goto commit;
            }
            
            // 系统菜单不能删除
            if ($info->system) {
                throw new VerifyException('系统菜单禁止删除');
            }
            
            // 删除子菜单
            $childIds = array_keys(ArrayHelper::listByKey($this->getAllChildList($info->path), SystemMenuField::id()));
            if ($childIds) {
                $this->whereEntity(SystemMenuField::id('in', $childIds))->delete();
            }
            
            $result = parent::deleteInfo($info->id);
            
            commit:
            $this->commit($disabledTrans);
            
            return $result;
        } catch (Exception $e) {
            $this->rollback($disabledTrans);
            
            throw $e;
        }
    }
    
    
    /**
     * 通过路径删除菜单
     * @param string $path 菜单路径
     * @param bool   $disabledTrans 是否禁用事物
     * @return int
     * @throws Exception
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
        $list = ArrayHelper::listToTree($this->selectList(), SystemMenuField::path(), SystemMenuField::parentPath(), SystemMenuInfo::child(), $path);
        
        return ArrayHelper::treeToList($list, SystemMenuInfo::child());
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
        return ArrayHelper::listByKey($safe ? $this->getSafeList() : $this->getList(), SystemMenuField::path());
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
        return ArrayHelper::listByKey($safe ? $this->getSafeList() : $this->getList(), SystemMenuField::id());
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
        return ArrayHelper::listByKey($safe ? $this->getSafeList() : $this->getList(), SystemMenuInfo::hash());
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
            $list = ArrayHelper::listToTree($this->getList(), SystemMenuField::path(), SystemMenuField::parentPath(), SystemMenuInfo::child(), "");
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
            $tree = ArrayHelper::listToTree($this->getList(), SystemMenuField::path(), SystemMenuField::parentPath(), SystemMenuInfo::child(), "", function(SystemMenuInfo $item) {
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
        
        return ArrayHelper::listToTree($list, SystemMenuField::path(), SystemMenuField::parentPath(), SystemMenuInfo::child(), "", function(SystemMenuInfo $info) use ($adminUserInfo, $parentsIdsList, $list) {
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
     * 获取打开方式
     * @param string $var
     * @return array|string
     */
    public static function getTargets($var = null)
    {
        return self::parseVars(
            self::parseConst(self::class, 'TARGET_', [], function($item) {
                return $item['name'];
            }), $var
        );
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