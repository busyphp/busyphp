<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\annotation\MenuGroup;
use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\common\SimpleForm;
use BusyPHP\app\admin\component\js\driver\Autocomplete;
use BusyPHP\app\admin\component\js\driver\LinkagePicker;
use BusyPHP\app\admin\component\js\driver\LinkagePicker\LinkagePickerFlatNode;
use BusyPHP\app\admin\component\js\driver\Table;
use BusyPHP\app\admin\component\js\driver\Tree;
use BusyPHP\app\admin\component\js\driver\tree\TreeFlatNode;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\model\system\menu\SystemMenuField;
use BusyPHP\helper\FilterHelper;
use LogicException;
use RangeException;
use RuntimeException;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\exception\HttpException;
use think\Response;
use Throwable;

/**
 * 开发模式-后台菜单管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:35 下午 MenuController.php $
 */
// 开发模式
#[MenuGroup(path: '#developer', name: "开发模式", icon: 'fa fa-folder-open-o', sort: -200, default: true, canDisable: false)]
#[MenuGroup(path: '#developer_manual', name: "开发手册", parent: "#developer", icon: 'fa fa-book', sort: -70, canDisable: false)]

// 系统
#[MenuGroup(path: '#system', name: "系统", icon: 'glyphicon glyphicon-cog', sort: -100, canDisable: false)]
#[MenuGroup(path: '#system_manager', name: "系统管理", parent: "#system", icon: 'fa fa-anchor', sort: -100)]
#[MenuGroup(path: '#system_user', name: "系统用户", parent: "#system", icon: 'fa fa-user-circle', sort: -90)]

// 路由转发为class
#[MenuRoute(path: 'system_menu', class: true)]
class MenuController extends InsideController
{
    protected SystemMenu $model;
    
    /**
     * @var bool 开发模式
     */
    const DEVELOP = false;
    
    
    protected function initialize($checkLogin = true)
    {
        $this->releaseDisabled('data');
        
        parent::initialize($checkLogin);
        
        $this->model = SystemMenu::init();
    }
    
    
    /**
     * 菜单管理
     * @return Response
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-menu', sort: -100, canDisable: false)]
    public function index() : Response
    {
        if ($table = Table::initIfRequest()) {
            $list = $this->model->getList();
            if (!static::DEVELOP) {
                $data = [];
                foreach ($list as $item) {
                    if ($item->path == $this->model::DEVELOPER_PATH) {
                        continue;
                    }
                    $data[] = $item;
                }
                $list = $data;
            }
            
            return $table->list($list)->response();
        }
        
        $this->assign('parent_field', SystemMenuField::parentPath());
        $this->assign('id_field', SystemMenuField::path());
        $this->assign('parent_root', '');
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 增加菜单
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -100)]
    public function add() : Response
    {
        if ($this->isPost()) {
            $this->model->create(SystemMenuField::init($this->parseData()));
            $this->updateCache();
            $this->log()->record(self::LOG_INSERT, '添加系统菜单');
            
            return $this->success('添加菜单成功');
        }
        
        $parentPath = '';
        if ($id = $this->get('id/d')) {
            $info       = $this->model->getInfo($id);
            $parentPath = $this->parseParentPath($info, true);
        } elseif ($hash = $this->get('hash/s', 'trim')) {
            $info       = $this->model->getAnnotationMenu($hash);
            $parentPath = $this->parseParentPath($info, true);
        }
        
        $this->assign('parent_path', $parentPath);
        $this->assign('target_list', SystemMenu::class()::getTargetMap());
        $this->assign('info', [
            'target'          => '',
            'hide'            => 0,
            'disabled'        => 0,
            'annotation'      => false,
            'system'          => false,
            'operate_disable' => true
        ]);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 修改菜单
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -90)]
    public function edit() : Response
    {
        if ($this->isPost()) {
            $this->model->modify(SystemMenuField::init($this->parseData()));
            $this->updateCache();
            $this->log()->record(self::LOG_UPDATE, '修改系统菜单');
            
            return $this->success('修改菜单成功');
        } else {
            if ($hash = $this->get('hash/s', 'trim')) {
                $info = $this->model->getAnnotationMenu($hash);
            } else {
                $info = $this->model->getInfo($this->get('id/d'));
            }
            
            $this->assign('parent_path', $this->parseParentPath($info));
            $this->assign('target_list', SystemMenu::class()::getTargetMap());
            $this->assign('info', $info);
            
            return $this->insideDisplay('add');
        }
    }
    
    
    /**
     * 解析提交的数据
     * @return array
     */
    protected function parseData() : array
    {
        $data                = $this->post();
        $parentPaths         = explode(',', $data['parent_path'] ?? '');
        $data['parent_path'] = SystemMenu::instance()->getHashMap()[end($parentPaths) ?: '']->path ?? '';
        
        return $data;
    }
    
    
    /**
     * 解析上级节点路径
     * @param SystemMenuField $info
     * @param bool            $hasSelf
     * @return string
     */
    protected function parseParentPath(SystemMenuField $info, bool $hasSelf = false) : string
    {
        $hashMap   = $this->model->getHashMap();
        $parentMap = $this->model->getHashParentMap()[$info->hash] ?? [];
        $parentMap = array_reverse($parentMap);
        $pathList  = [];
        foreach ($parentMap as $hash) {
            $path = $hashMap[$hash]->hash ?? '';
            if ($path) {
                $pathList[] = $path;
            }
        }
        if ($hasSelf) {
            $pathList[] = $info->hash;
        }
        
        return implode(',', $pathList);
    }
    
    
    /**
     * 删除菜单
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -80)]
    public function delete() : Response
    {
        $deleteIds = [];
        foreach ($this->param('id/a', 'intval') as $id) {
            if ($id <= 0) {
                continue;
            }
            $deleteIds[] = $id;
        }
        
        SimpleForm::init()->batch($deleteIds, '请选择要删除的菜单', function(int $id) {
            $this->model->remove($id);
        });
        
        $this->log()->record(self::LOG_DELETE, '删除系统菜单');
        $this->updateCache();
        
        return $this->success('删除成功');
    }
    
    
    /**
     * 排序菜单
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -70)]
    public function sort() : Response
    {
        $data = [];
        foreach ($this->request->param("sort/a", [], 'intval') as $id => $item) {
            if ($id <= 0) {
                continue;
            }
            $data[$id] = $item;
        }
        
        SimpleForm::init($this->model)->sort($data, SystemMenuField::sort());
        $this->log()->record(self::LOG_UPDATE, '排序系统菜单');
        $this->updateCache();
        
        return $this->success('排序成功');
    }
    
    
    /**
     * 设置属性
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -60)]
    public function set_attr() : Response
    {
        $type   = $this->get('type/s', 'trim');
        $id     = $this->get('id/d');
        $status = $this->get('status/b');
        if ($id < 0) {
            throw new RuntimeException('系统菜单不允许操作');
        }
        
        switch ($type) {
            case 'disabled':
                $this->model->setDisabled($id, !$status);
            break;
            case 'hide':
                $this->model->setHide($id, !$status);
            break;
            default:
                throw new RangeException('未知类型');
        }
        
        $this->updateCache();
        $this->log()->record(self::LOG_UPDATE, '修改系统菜单属性');
        
        return $this->success('设置成功');
    }
    
    
    /**
     * 数据接口
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function data() : Response
    {
        $all      = $this->param('all/b'); // 是否显示权限节点菜单
        $disabled = $this->param('disabled/b');// 是否显示禁用菜单
        $develop  = $this->param('develop/b'); // 是否显示开发模式菜单
        
        // linkagePicker
        // data
        if ($linkage = LinkagePicker::initIfRequest()) {
            $hash = $this->param('hash/s', 'trim'); // 排除的菜单节点hash
            
            return $linkage
                ->list($this->model->getList(), function(LinkagePickerFlatNode $node, SystemMenuField $item, int $index) use ($develop, $hash, $all, $disabled) {
                    if (!$this->filterItem($item, $all, $disabled, $develop)) {
                        return false;
                    }
                    
                    if ($item->hash == $hash) {
                        return false;
                    }
                    
                    $node->setId($item->hash);
                    $node->setName($item->name);
                    $node->setParent($item->parentHash);
                    
                    return true;
                })
                ->response();
        }
        
        // autocomplete
        // data
        elseif ($autocomplete = Autocomplete::initIfRequest()) {
            return $autocomplete->list($this->model->getList(), null, function($list) use ($autocomplete, $all, $disabled, $develop) {
                $list = Collection::make(array_filter($list, function(SystemMenuField $item) use ($develop, $all, $disabled) {
                    return $this->filterItem($item, $all, $disabled, $develop);
                }));
                
                return $list->whereLike(SystemMenuField::path()->name(), $autocomplete->getWord());
            })->response();
        }
        
        // tree
        // data
        elseif ($tree = Tree::initIfRequest()) {
            $openedHashList   = FilterHelper::trimArray(explode(',', $this->param('opened_hash/s', 'trim')));   // 打开的菜单节点hash集合
            $selectedHashList = FilterHelper::trimArray(explode(',', $this->param('selected_hash/s', 'trim'))); // 选中的菜单节点hash集合
            $filterHashList   = FilterHelper::trimArray(explode(',', $this->param('filter_hash/s', 'trim')));   // 过滤的菜单节点hash集合
            
            // 角色组
            $groupId       = $this->param('group_id/d');
            $parentGroupId = explode(',', $this->param('parent_group_id/s', 'trim'));
            $parentGroupId = (int) end($parentGroupId);
            if ($groupId) {
                $groupInfo = AdminGroup::init()->getInfo($groupId);
                if ($groupInfo->id === $parentGroupId) {
                    throw new LogicException('父角色不能是自己');
                }
                $openedHashList   = $groupInfo->ruleIndeterminate;
                $selectedHashList = $groupInfo->rule;
            }
            
            if ($parentGroupId) {
                $parentGroupInfo = AdminGroup::init()->getInfo($parentGroupId);
                $filterHashList  = $parentGroupInfo->ruleIds;
            }
            
            return $tree->list(
                $this->model->getList(),
                function(TreeFlatNode $node, SystemMenuField $item, int $index) use ($openedHashList, $selectedHashList) {
                    $node->setParent($item->parentHash);
                    $node->setText($item->name);
                    $node->setId($item->hash);
                    $node->setIcon($item->icon);
                    
                    // 展开选中项的父节点
                    if ($openedHashList && in_array($item->hash, $openedHashList)) {
                        $node->setOpened(true);
                    }
                    
                    // 设为选中
                    if ($selectedHashList && in_array($item->hash, $selectedHashList)) {
                        $node->setSelected(true);
                    }
                },
                function(array $list) use ($filterHashList, $all, $disabled, $develop) {
                    return array_filter($list, function(SystemMenuField $item) use ($filterHashList, $all, $disabled, $develop) {
                        if (!$this->filterItem($item, $all, $disabled, $develop)) {
                            return false;
                        }
                        
                        if ($filterHashList) {
                            return in_array($item->hash, $filterHashList);
                        }
                        
                        return true;
                    });
                })
                ->response();
        }
        
        throw new HttpException(404);
    }
    
    
    /**
     * 通用过滤菜单
     * @param SystemMenuField $item 菜单节点
     * @param bool            $all 是否显示权限节点
     * @param bool            $disabled 是否显示禁用节点
     * @param bool            $develop 是否显示开发模式节点
     * @return bool
     */
    protected function filterItem(SystemMenuField $item, bool $all, bool $disabled, bool $develop = false) : bool
    {
        // 不显示禁用菜单节点
        if (!$disabled && $item->disabled) {
            return false;
        }
        
        // 不显示权限节点菜单
        if (!$all && $item->hide) {
            return false;
        }
        
        // 超级管理员在DEVELOP开启的情况下显示开发模式菜单
        if ($this->adminUser->groupHasSystem && $develop) {
            if (!self::DEVELOP && $this->model->isDeveloper($item->hash)) {
                return false;
            }
        } else {
            if ($this->model->isDeveloper($item->hash)) {
                return false;
            }
        }
        
        return true;
    }
}