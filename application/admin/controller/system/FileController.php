<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\common\SimpleForm;
use BusyPHP\app\admin\component\js\driver\Table;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassField;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\SystemFileField;
use BusyPHP\helper\AppHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\model\ArrayOption;
use think\db\exception\DbException;
use think\Response;
use Throwable;

/**
 * 文件管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午2:17 下午 FileController.php $
 */
#[MenuRoute(path: 'system_file', class: true)]
class FileController extends InsideController
{
    /**
     * @var SystemFile
     */
    protected $model;
    
    /**
     * 文件管理默认时间范围
     * @var string
     */
    protected $indexTimeRange;
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model          = SystemFile::init();
        $this->indexTimeRange = date('Y-m-d 00:00:00', strtotime('-29 days')) . ' - ' . date('Y-m-d 23:59:59');
    }
    
    
    /**
     * 文件管理
     * @return Response
     */
    #[MenuNode(menu: true, parent: '#system_manager', icon: 'fa fa-file-text', sort: 3)]
    public function index() : Response
    {
        $type = $this->param('type/s', 'trim');
        if ($table = Table::initIfRequest()) {
            switch ($table->getOrderField()) {
                case SystemFileField::formatSize():
                    $table->setOrderField(SystemFileField::size());
                break;
                case SystemFileField::formatCreateTime():
                    $table->setOrderField(SystemFileField::createTime());
                break;
            }
            
            return $table
                ->model($this->model)
                ->query(function(SystemFile $model, ArrayOption $option) use ($type) {
                    $option->deleteIfEmpty('client');
                    $option->deleteIfEmpty('disk');
                    $option->deleteIfEmpty('class_type');
    
                    if ($type) {
                        $option->set('type', $type);
                    }
                    
                    // 时间
                    if ($time = $option->pull('time', $this->indexTimeRange)) {
                        $model->whereTimeIntervalRange(SystemFileField::createTime(), $time, ' - ', true);
                    }
                    
                    if ($uploadType = $option->pull('upload_type', 0, 'intval')) {
                        switch ($uploadType) {
                            case 1:
                                $model->whereComplete()->where(SystemFileField::fast(0));
                            break;
                            case 2:
                                $model->whereComplete()->where(SystemFileField::fast(1));
                            break;
                            case 3:
                                $model->whereComplete(false);
                            break;
                        }
                    }
                })
                ->response();
        }
        
        $this->assign('cate_options', TransHelper::toOptionHtml(SystemFileClass::instance()->getList(), '', SystemFileClassField::name(), SystemFileClassField::var()));
        $this->assign('client_options', TransHelper::toOptionHtml(AppHelper::getList(), null, 'name', 'dir'));
        $this->assign('time', $this->indexTimeRange);
        $this->assign('types', SystemFile::class()::getTypes());
        $this->assign('type', $type);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 上传文件
     * @return Response
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function upload() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 删除文件
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function delete() : Response
    {
        SimpleForm::init()->batch($this->param('id/a', 'intval'), '请选择要删除的文件', function(int $id) {
            $this->model->remove($id);
        });
        
        $this->log()->record(self::LOG_DELETE, '删除文件');
        
        return $this->success('删除成功');
    }
    
    
    /**
     * 清理重复文件
     * @return Response
     * @throws DbException
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function clear_repeat() : Response
    {
        $total = $this->model->clearRepeat();
        $this->log()->record(self::LOG_DELETE, '清理重复文件');
        
        return $this->success("成功清理{$total}个文件");
    }
    
    
    /**
     * 清理无效文件
     * @return Response
     * @throws DbException
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function clear_invalid() : Response
    {
        $total = $this->model->where(SystemFileField::createTime('<', time() - 86400 * 2))->clearInvalid();
        $this->log()->record(self::LOG_DELETE, '清理无效文件');
        
        return $this->success("成功清理{$total}个文件");
    }
}