<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\common\SimpleForm;
use BusyPHP\app\admin\component\js\driver\Table;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassField;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\helper\TransHelper;
use BusyPHP\model\ArrayOption;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;
use Throwable;

/**
 * 开发模式-系统附件分类管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:34 下午 FileClassController.php $
 */
#[MenuRoute(path: 'system_file_class', class: true)]
class FileClassController extends InsideController
{
    /**
     * @var SystemFileClass
     */
    protected $model;
    
    
    protected function initialize($checkLogin = true)
    {
        $this->releaseDisabled();
        
        parent::initialize($checkLogin);
        
        $this->model = SystemFileClass::init();
    }
    
    
    /**
     * 文件分类
     * @return Response
     */
    #[MenuNode(menu: true, parent: '#developer', icon: 'fa fa-file-text-o', sort: 10)]
    public function index() : Response
    {
        if ($table = Table::initIfRequest()) {
            return $table
                ->model($this->model)
                ->query(function(SystemFileClass $model, ArrayOption $option) {
                    $option->deleteIfEmpty('type');
                    
                    $model->order(SystemFileClassField::sort(), 'asc');
                    $model->order(SystemFileClassField::id(), 'desc');
                })
                ->response();
        }
        
        $this->assign('type_options', TransHelper::toOptionHtml(SystemFile::class()::getTypes()));
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 添加文件分类
     * @return Response
     * @throws DbException
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function add() : Response
    {
        if ($this->isPost()) {
            $this->model->create(SystemFileClassField::parse($this->post()));
            $this->log()->record(self::LOG_INSERT, '增加文件分类');
            
            return $this->success('添加成功');
        }
        
        $this->assign('info', [
            'type_options' => TransHelper::toOptionHtml(SystemFile::class()::getTypes()),
            'system'       => 0,
        ]);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 修改文件分类
     * @return Response
     * @throws DbException
     * @throws DataNotFoundException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function edit() : Response
    {
        if ($this->isPost()) {
            $this->model->modify(SystemFileClassField::parse($this->post()));
            $this->log()->record(self::LOG_UPDATE, '修改文件分类');
            
            return $this->success('修改成功');
        }
        
        $info                 = $this->model->getInfo($this->get('id'));
        $info['type_options'] = TransHelper::toOptionHtml(SystemFile::class()::getTypes(), $info->type);
        $this->assign('info', $info);
        
        return $this->insideDisplay('add');
    }
    
    
    /**
     * 排序文件分类
     * @throws DbException
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function sort() : Response
    {
        SimpleForm::init($this->model)->sort('sort', SystemFileClassField::sort());
        $this->log()->record(self::LOG_DELETE, '排序文件分类');
        
        return $this->success('排序成功');
    }
    
    
    /**
     * 删除文件分类
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function delete() : Response
    {
        SimpleForm::init()->batch($this->param('id/a', 'intval'), '请选择要删除的文件分类', function(int $id) {
            $this->model->remove($id);
        });
        
        $this->log()->record(self::LOG_DELETE, '删除文件分类');
        
        return $this->success('删除成功');
    }
} 