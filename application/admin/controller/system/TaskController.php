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
    /**
     * 任务模型
     * @var SystemTask
     */
    protected SystemTask $model;
    
    /**
     * 任务模型字段类
     * @var string|SystemTaskField
     */
    protected mixed $field;
    
    
    protected function initialize(bool $checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = SystemTask::init();
        $this->field = $this->model->getFieldClass();
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
                    $model->whereTimeIntervalRange($this->field::planTime(), $time, ' - ', true);
                }
                
                $model->order($this->field::planTime(), 'desc');
            });
            
            return $table->model($this->model)->response();
        }
        
        $this->assign('status_options', $this->model::getStatusMap());
        
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
        $this->ignoreOperate();
        $server = $this->model::getRunningServer(3);
        $status = false !== $server;
        $server = $server ?: [];
        
        return $this->success([
            'status'        => $status,
            'server_pid'    => (int) ($server['pid'] ?? 0),
            'server_name'   => ($server['name'] ?? ''),
            'wait_total'    => $this
                ->model
                ->where($this->field::status('in', [
                    $this->model::STATUS_WAIT,
                    $this->model::STATUS_AGAIN
                ]))
                ->count(),
            'start_total'   => $this
                ->model
                ->where($this->field::status($this->model::STATUS_STARTED))
                ->count(),
            'success_total' => $this
                ->model
                ->where($this->field::status($this->model::STATUS_COMPLETE))
                ->where($this->field::success(true))
                ->count(),
            'error_total'   => $this
                ->model
                ->where($this->field::status($this->model::STATUS_COMPLETE))
                ->where($this->field::success(false))
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