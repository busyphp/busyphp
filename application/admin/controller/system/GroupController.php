<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\common\SimpleForm;
use BusyPHP\app\admin\component\js\driver\LinkagePicker;
use BusyPHP\app\admin\component\js\driver\Table;
use BusyPHP\app\admin\component\js\driver\Tree;
use BusyPHP\app\admin\component\js\driver\tree\TreeFlatNode;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\group\AdminGroupField;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\model\system\menu\SystemMenuField;
use BusyPHP\exception\VerifyException;
use BusyPHP\model\ArrayOption;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;
use Throwable;

/**
 * 后台用户组权限管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午12:11 下午 GroupController.php $
 */
#[MenuRoute(path: 'system_group', class: true)]
class GroupController extends InsideController
{
    /**
     * @var AdminGroup
     */
    protected $model;
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = AdminGroup::init();
    }
    
    
    /**
     * 管理员角色
     * @return Response
     */
    #[MenuNode(menu: true, parent: '#system_user', icon: 'bicon bicon-user-lock', sort: 2)]
    public function index() : Response
    {
        // 角色列表数据
        if ($table = Table::initIfRequest()) {
            $table->model($this->model);
            $table->query(function(AdminGroup $model, ArrayOption $option) {
                $model->order(AdminGroupField::sort(), 'asc');
                $model->order(AdminGroupField::id(), 'asc');
            });
            
            return $table->response();
        }
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 添加角色
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function add() : Response
    {
        // 添加
        if ($this->isPost()) {
            $this->model->create(AdminGroupField::init($this->parseData()));
            $this->log()->record(self::LOG_INSERT, '添加管理角色');
            
            return $this->success('添加成功');
        }
        
        $id = $this->get('id/d');
        $this->assign([
            'info' => [
                'status'    => true,
                'system'    => false,
                'parent_id' => $id > 0 ? $this->parseParentId($this->model->getInfo($id), true) : ''
            ],
        ]);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 修改角色
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function edit() : Response
    {
        // 修改
        if ($this->isPost()) {
            $this->model->modify(AdminGroupField::init($this->parseData()));
            $this->log()->record(self::LOG_UPDATE, '修改管理角色');
            
            return $this->success('修改成功');
        }
        
        $info           = $this->model->getInfo($this->get('id/d'));
        $info->parentId = $this->parseParentId($info);
        $this->assign([
            'info' => $info,
        ]);
        
        return $this->insideDisplay('add');
    }
    
    
    /**
     * 解析提交的数据
     * @return array
     */
    protected function parseData() : array
    {
        $data              = $this->post();
        $parentIds         = explode(',', $data['parent_id'] ?? '');
        $data['parent_id'] = end($parentIds) ?: 0;
        
        return $data;
    }
    
    
    /**
     * 解析上级角色组
     * @param AdminGroupField $info
     * @param bool            $hasSelf
     * @return string
     */
    protected function parseParentId(AdminGroupField $info, bool $hasSelf = false) : string
    {
        $idMap     = $this->model->getIdMap();
        $parentMap = $this->model->getIdParentMap()[$info->id] ?? [];
        $parentMap = array_reverse($parentMap);
        $pathList  = [];
        foreach ($parentMap as $id) {
            $path = $idMap[$id]->id ?? '';
            if ($path) {
                $pathList[] = $path;
            }
        }
        if ($hasSelf) {
            $pathList[] = $info->id;
        }
        
        return implode(',', $pathList);
    }
    
    
    /**
     * 获取树权限节点
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function rule_tree_data() : Response
    {
        $id         = $this->get('group_id/d');
        $parentId   = explode(',', $this->get('parent_group_id/s', 'trim'));
        $parentId   = (int) end($parentId);
        $info       = null;
        $parentInfo = null;
        if ($id > 0) {
            $info = $this->model->getInfo($id);
            if ($info->id == $parentId) {
                throw new VerifyException('父角色不能是自己');
            }
        }
        if ($parentId > 0) {
            $parentInfo = $this->model->getInfo($parentId);
            if (!$parentInfo->ruleIds) {
                throw new VerifyException('该角色权限信息异常');
            }
        }
        
        return Tree::init()
            ->list(
                SystemMenu::instance()->getList(),
                function(TreeFlatNode $node, SystemMenuField $item, int $index) use ($info) {
                    $node->setParent($item->parentHash);
                    $node->setText($item->name);
                    $node->setId($item->hash);
                    $node->setIcon($item->icon);
                    
                    if ($info) {
                        // 展开选中项的父节点
                        if (in_array($item->hash, $info->ruleIndeterminate)) {
                            $node->setOpened(true);
                        }
                        
                        // 设为选中
                        if (in_array($item->hash, $info->rule)) {
                            $node->setSelected(true);
                        }
                    }
                },
                function(array $list) use ($parentInfo) {
                    return array_filter($list, function(SystemMenuField $item) use ($parentInfo) {
                        if ($item->path === SystemMenu::class()::DEVELOPER_PATH || $item->disabled) {
                            return false;
                        }
                        
                        if ($parentInfo) {
                            return in_array($item->hash, $parentInfo->ruleIds);
                        }
                        
                        return true;
                    });
                })
            ->response();
    }
    
    
    /**
     * 获取权限选择数据
     * @return Response
     */
    public function linkage_picker_data() : Response
    {
        $id = $this->param('id/d');
        
        return LinkagePicker::init()
            ->model($this->model)
            ->query(function(AdminGroup $model) use ($id) {
                if ($id > 0) {
                    $model->where(AdminGroupField::id('<>', $id));
                }
                
                $model->order(AdminGroupField::sort(), 'asc');
                $model->order(AdminGroupField::id(), 'asc');
            })
            ->response();
    }
    
    
    /**
     * 删除角色
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function delete() : Response
    {
        foreach ($this->param('id/a', 'intval') as $id) {
            $this->model->remove($id);
        }
        
        $this->log()->record(self::LOG_DELETE, '删除管理角色');
        
        return $this->success('删除成功');
    }
    
    
    /**
     * 启用/禁用角色
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function change_status() : Response
    {
        $status = $this->get('status/b');
        $this->model->changeStatus($this->get('id/d'), $status);
        $this->log()->record(self::LOG_UPDATE, '启用/禁用角色');
        
        return $this->success($status ? '启用成功' : '禁用成功');
    }
    
    
    /**
     * 排序角色
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function sort() : Response
    {
        SimpleForm::init($this->model)->sort('sort', AdminGroupField::sort());
        $this->log()->record(self::LOG_UPDATE, '排序管理角色');
        $this->updateCache();
        
        return $this->success('排序成功');
    }
}