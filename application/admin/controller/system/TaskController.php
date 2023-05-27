<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\common\SimpleForm;
use BusyPHP\app\admin\component\js\driver\Table;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\task\SystemTask;
use BusyPHP\app\admin\model\system\task\SystemTaskField;
use BusyPHP\app\admin\task\Database;
use BusyPHP\model\ArrayOption;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;
use Throwable;

/**
 * 系统任务
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/4/10 10:56 TaskController.php $
 */
#[MenuRoute(path: 'system_task', class: true)]
class TaskController extends InsideController
{
    protected SystemTask $model;
    
    
    protected function initialize(bool $checkLogin = true)
    {
        $this->model = SystemTask::init();
        
        parent::initialize($checkLogin);
    }
    
    
    /**
     * 系统任务
     * @return Response
     */
    #[MenuNode(menu: true, parent: '#system_manager', icon: 'fa fa-server', sort: -90)]
    public function index() : Response
    {
        if ($table = Table::initIfRequest()) {
            $table->query(function(SystemTask $model, ArrayOption $option) {
                $option->deleteIfLt('status', 0);
                $option->deleteIfLt('success', 0);
                
                if ($time = $option->pull('time')) {
                    $model->whereTimeIntervalRange(SystemTaskField::planTime(), $time, ' - ', true);
                }
                
                $model->order(SystemTaskField::planTime(), 'desc');
            });
            
            return $table->model($this->model)->response();
        }
        
        $this->assign('status_options', $this->model::getStatus());
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 删除任务
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -100)]
    public function delete() : Response
    {
        SimpleForm::init()->batch($this->param('id/a', 'trim'), '请选择要删除的任务', function(string $id) {
            $this->model->remove($id);
        });
        
        $this->log()->record(self::LOG_DELETE, '删除任务');
        
        return $this->success('删除成功');
    }
    
    
    /**
     * 重置任务
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -90)]
    public function reset() : Response
    {
        $this->model->reset($this->param('id/s', 'trim'));
        
        $this->log()->record(self::LOG_UPDATE, '重置任务');
        
        return $this->success('重置成功');
    }
    
    
    /**
     * 清理任务
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -80)]
    public function clean() : Response
    {
        $res = $this->model->clean();
        
        $this->log()->record(self::LOG_DELETE, '清理任务');
        
        return $this->success(sprintf('清理完成，删除%s条，重置%s条', $res['deleted'], $res['reset']));
    }
    
    
    /**
     * 优化数据表
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -70)]
    public function optimize() : Response
    {
        return $this->task(Database::class, Database::TYPE_OPTIMIZE)->log(self::LOG_DEFAULT, '优化数据表')->create();
    }
    
    
    /**
     * 修复数据表
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index', sort: -60)]
    public function repair() : Response
    {
        return $this->task(Database::class, Database::TYPE_REPAIR)->log(self::LOG_DEFAULT, '修复数据表')->create();
    }
    
    
    /**
     * 任务状态
     * @return Response
     * @throws DbException
     */
    public function status() : Response
    {
        $message = $this->app->console->call('bp:task', ['status'])->fetch();
        $status  = false;
        if (preg_match('/with pid (\d+)/is', $message, $match)) {
            $status = $match[1];
        }
        
        return $this->success([
            'status'        => $status,
            'wait_total'    => $this
                ->model
                ->where(SystemTaskField::status('in', [
                    SystemTask::STATUS_WAIT,
                    SystemTask::STATUS_AGAIN
                ]))
                ->count(),
            'start_total'   => $this
                ->model
                ->where(SystemTaskField::status(SystemTask::STATUS_STARTED))
                ->count(),
            'success_total' => $this
                ->model
                ->where(SystemTaskField::status(SystemTask::STATUS_COMPLETE))
                ->where(SystemTaskField::success(true))
                ->count(),
            'error_total'   => $this
                ->model
                ->where(SystemTaskField::status(SystemTask::STATUS_COMPLETE))
                ->where(SystemTaskField::success(false))
                ->count()
        ]);
    }
    
    
    /**
     * 执行下载
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function download() : Response
    {
        return $this->model->downloadResult(
            $this->get('id/s', 'trim'),
            $this->get('name/s', 'trim'),
            $this->get('mimetype/s', 'trim')
        );
    }
}