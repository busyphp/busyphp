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
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\model\system\menu\SystemMenuField;
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
#[MenuGroup(path: '#developer', name: "开发模式", icon: 'fa fa-folder-open-o', sort: 0, default: true, canDisable: false)]
#[MenuGroup(path: '#developer_manual', name: "开发手册", parent: "#developer", icon: 'fa fa-book', sort: 50, canDisable: false)]

// 系统
#[MenuGroup(path: '#system', name: "系统", icon: 'glyphicon glyphicon-cog', sort: 1, canDisable: false)]
#[MenuGroup(path: '#system_manager', name: "系统管理", parent: "#system", icon: 'fa fa-anchor', sort: 0)]
#[MenuGroup(path: '#system_user', name: "系统用户", parent: "#system", icon: 'fa fa-user-circle', sort: 1)]

// 路由转发为class
#[MenuRoute(path: 'system_menu', class: true)]
class MenuController extends InsideController
{
    /**
     * @var SystemMenu
     */
    protected $model;
    
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
    #[MenuNode(menu: true, icon: 'bicon bicon-menu', sort: 1, canDisable: false)]
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
    #[MenuNode(menu: false, parent: '/index')]
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
        $this->assign('target_list', SystemMenu::class()::getTargets());
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
    #[MenuNode(menu: false, parent: '/index')]
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
            $this->assign('target_list', SystemMenu::class()::getTargets());
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
     * 快速设置属性
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
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
     * 排序菜单
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
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
     * 删除菜单
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
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
     * 数据接口
     * @return Response
     */
    public function data() : Response
    {
        if ($linkage = LinkagePicker::initIfRequest()) {
            $hash = $this->param('hash/s', 'trim');
            $all  = $this->param('all/b');
            
            return $linkage
                ->list($this->model->getList(), function(LinkagePickerFlatNode $node, SystemMenuField $item, int $index) use ($hash, $all) {
                    if ((!self::DEVELOP && $item->path == $this->model::DEVELOPER_PATH) || $item->hash == $hash || (!$all && $item->hide)) {
                        return false;
                    }
                    
                    $node->setId($item->hash);
                    $node->setName($item->name);
                    $node->setParent($item->parentHash);
                    
                    return true;
                })
                ->response();
        } elseif ($autocomplete = Autocomplete::initIfRequest()) {
            return $autocomplete->list($this->model->getList(), null, function($list) use ($autocomplete) {
                $list = Collection::make($list);
                
                return $list->whereLike(SystemMenuField::path()->name(), $autocomplete->getWord());
            })->response();
        }
        
        throw new HttpException(404);
    }
}