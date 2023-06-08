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
use BusyPHP\model\ArrayOption;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\exception\HttpException;
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
     * 角色模型
     * @var AdminGroup
     */
    protected AdminGroup $model;
    
    /**
     * 角色模型字段
     * @var AdminGroupField|string
     */
    protected mixed $field;
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = AdminGroup::init();
        $this->field = $this->model->getFieldClass();
    }
    
    
    /**
     * 系统角色管理
     * @return Response
     */
    #[MenuNode(menu: true, parent: '#system_user', icon: 'bicon bicon-user-lock', sort: -90)]
    public function index() : Response
    {
        // 系统角色列表数据
        if ($table = Table::initIfRequest()) {
            return $table
                ->defaultOrder([
                    (string) $this->field::sort() => 'asc',
                    (string) $this->field::id()   => 'asc',
                ])
                ->model($this->model)->query(function(AdminGroup $model, ArrayOption $option) {
                    $this->indexTableQuery($model, $option);
                })
                ->response();
        }
        
        $this->assignIndexData();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 自定义列表查询条件
     * @param AdminGroup  $model
     * @param ArrayOption $option
     */
    protected function indexTableQuery(AdminGroup $model, ArrayOption $option)
    {
    }
    
    
    /**
     * 赋值列表模版数据
     */
    protected function assignIndexData()
    {
    }
    
    
    /**
     * 添加角色
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -100)]
    public function add() : Response
    {
        // 添加
        if ($this->isPost()) {
            $this->model->create($this->field::init($this->parseData()));
            $this->log()->record(self::LOG_INSERT, '添加系统角色');
            
            return $this->success('添加成功');
        }
        
        $this->assignSaveData();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 修改角色
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -90)]
    public function edit() : Response
    {
        // 修改
        if ($this->isPost()) {
            $this->model->modify($this->field::init($this->parseData()));
            $this->log()->record(self::LOG_UPDATE, '修改系统角色');
            
            return $this->success('修改成功');
        }
        
        $this->assignSaveData(true);
        
        return $this->insideDisplay('add');
    }
    
    
    /**
     * 赋值新增/修改模版数据
     * @param bool  $edit 是否修改
     * @param array $defaultInfo 新增默认信息
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function assignSaveData(bool $edit = false, array $defaultInfo = [])
    {
        if ($edit) {
            $info           = $this->model->getInfo($this->get('id/d'));
            $info->parentId = $this->parseParentId($info);
            $this->assign([
                'info' => $info,
            ]);
        } else {
            $id = $this->get('id/d');
            $this->assign([
                'info' => $defaultInfo + [
                        'status'    => true,
                        'system'    => false,
                        'parent_id' => $id > 0 ? $this->parseParentId($this->model->getInfo($id), true) : ''
                    ],
            ]);
        }
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
     * 解析上级系统角色组
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
     * 删除角色
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -80)]
    public function delete() : Response
    {
        foreach ($this->param('id/a', 'intval') as $id) {
            $this->model->remove($id);
        }
        
        $this->log()->record(self::LOG_DELETE, '删除系统角色');
        
        return $this->success('删除成功');
    }
    
    
    /**
     * 启用/禁用角色
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -70)]
    public function change_status() : Response
    {
        $status = $this->get('status/b');
        $this->model->changeStatus($this->get('id/d'), $status);
        $this->log()->record(self::LOG_UPDATE, '启用/禁用系统角色');
        
        return $this->success($status ? '启用成功' : '禁用成功');
    }
    
    
    /**
     * 排序角色
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -60)]
    public function sort() : Response
    {
        SimpleForm::init($this->model)->sort('sort', $this->field::sort());
        $this->log()->record(self::LOG_UPDATE, '排序系统角色');
        $this->updateCache();
        
        return $this->success('排序成功');
    }
    
    
    /**
     * 数据接口
     * @return Response
     */
    public function data() : Response
    {
        // tree
        // data
        if ($tree = Tree::initIfRequest()) {
            $groupIds = $this->param('id/s', 'trim');
            $groupIds = array_map('intval', explode(',', $groupIds));
            
            return $tree->model($this->model)
                ->defaultOrder([
                    (string) $this->field::sort() => 'asc',
                    (string) $this->field::id()   => 'asc',
                ])
                ->list(function(TreeFlatNode $node, AdminGroupField $item, int $index) use ($groupIds) {
                    $node->setText($item->name);
                    $node->setParent($item->parentId);
                    $node->setId($item->id);
                    $node->setOpened(true);
                    
                    if (in_array($item->id, $groupIds)) {
                        $node->setSelected(true);
                    }
                })
                ->response();
        }
        
        // linkagePicker
        // data
        elseif ($linkage = LinkagePicker::initIfRequest()) {
            $id = $this->param('id/d');
            
            return $linkage->model($this->model)
                ->defaultOrder([
                    (string) $this->field::sort() => 'asc',
                    (string) $this->field::id()   => 'asc',
                ])
                ->query(function(AdminGroup $model) use ($id) {
                    if ($id > 0) {
                        $model->where($this->field::id('<>', $id));
                    }
                })
                ->response();
        }
        
        throw new HttpException(404);
    }
}