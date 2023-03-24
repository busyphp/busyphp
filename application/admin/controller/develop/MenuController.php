<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\develop;

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
use BusyPHP\model\ArrayOption;
use RangeException;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;
use Throwable;

/**
 * 开发模式-后台菜单管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:35 下午 MenuController.php $
 */
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
    const DEVELOP = true;
    
    
    protected function initialize($checkLogin = true)
    {
        $this->releaseDisabled('linkage_picker_data');
        
        parent::initialize($checkLogin);
        
        $this->model = SystemMenu::init();
    }
    
    
    /**
     * 菜单管理
     * @return Response
     */
    #[MenuNode(menu: true, parent: '#developer', icon: 'bicon bicon-menu', sort: 1)]
    public function index() : Response
    {
        if ($table = Table::initIfRequest()) {
            return $table
                ->model($this->model)
                ->query(function(SystemMenu $model, ArrayOption $option) {
                    if (!MenuController::DEVELOP) {
                        $model->where(SystemMenuField::path('<>', SystemMenu::class()::DEVELOPER_PATH));
                    }
                    
                    $model->order(SystemMenuField::sort(), 'asc');
                    $model->order(SystemMenuField::id(), 'asc');
                })
                ->response();
        }
        
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
            $this->model->create(SystemMenuField::parse($this->parseData()));
            $this->updateCache();
            $this->log()->record(self::LOG_INSERT, '添加系统菜单');
            
            return $this->success('添加菜单成功');
        }
        
        $parentPath = '';
        if ($id = $this->get('id/d')) {
            $info       = $this->model->getInfo($id);
            $parentPath = $this->parseParentPath($info, true);
        }
        
        $this->assign('parent_path', $parentPath);
        $this->assign('target_list', SystemMenu::class()::getTargets());
        $this->assign('info', [
            'target'   => '',
            'hide'     => 0,
            'disabled' => 0,
            'system'   => 0
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
            $this->model->modify(SystemMenuField::parse($this->parseData()));
            $this->updateCache();
            $this->log()->record(self::LOG_UPDATE, '修改系统菜单');
            
            return $this->success('修改菜单成功');
        } else {
            $info = $this->model->getInfo($this->get('id/d'));
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
        SimpleForm::init($this->model)->sort('sort', SystemMenuField::sort());
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
        foreach ($this->param('id/list/请选择要删除的菜单') as $id) {
            $this->model->remove($id);
        }
        
        $this->log()->record(self::LOG_DELETE, '删除系统菜单');
        $this->updateCache();
        
        return $this->success('删除成功');
    }
    
    
    /**
     * LinkagePicker
     * @return Response
     */
    public function linkage_picker_data() : Response
    {
        $hash = $this->get('hash/s', 'trim');
        $type = $this->get('type/s', 'trim');
        if ($type === 'db') {
            return LinkagePicker::init()
                ->model($this->model)
                ->query(function(SystemMenu $model) use ($hash) {
                    if (!MenuController::DEVELOP) {
                        $model->where(SystemMenuField::path('<>', SystemMenu::class()::DEVELOPER_PATH));
                    }
                    
                    if ($hash) {
                        $info = $this->model->getHashMap()[$hash];
                        if ($info) {
                            $model->where(SystemMenuField::path('<>', $info->path));
                        }
                    }
                    
                    $model->order(SystemMenuField::sort(), 'asc');
                    $model->order(SystemMenuField::id(), 'asc');
                })
                ->list(function(LinkagePickerFlatNode $node, SystemMenuField $item, int $index) {
                    $node->setId($item->hash);
                    $node->setName($item->name);
                    $node->setParent($item->parentHash);
                })
                ->response();
        } else {
            $developerPath = $this->model::DEVELOPER_PATH;
            
            return LinkagePicker::init()
                ->list($this->model->getList(), function(LinkagePickerFlatNode $node, SystemMenuField $item, int $index) use ($developerPath, $hash) {
                    if (!MenuController::DEVELOP && $item->path == $developerPath || $item->hash == $hash) {
                        return false;
                    }
                    
                    $node->setId($item->hash);
                    $node->setName($item->name);
                    $node->setParent($item->parentHash);
                    
                    return true;
                })
                ->response();
        }
    }
    
    
    /**
     * Autocomplete Data
     * @return Response
     */
    public function autocomplete_data() : Response
    {
        $autocomplete = Autocomplete::init();
        
        return $autocomplete->list($this->model->getList(), null, function($list) use ($autocomplete) {
            $list = Collection::make($list);
            
            return $list->whereLike(SystemMenuField::path()->name(), $autocomplete->getWord());
        })->response();
    }
}