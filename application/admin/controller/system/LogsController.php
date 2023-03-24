<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\js\driver\Table;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\logs\SystemLogs;
use BusyPHP\app\admin\model\system\logs\SystemLogsField;
use BusyPHP\helper\AppHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\model\ArrayOption;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 系统日志管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/18 下午下午3:46 LogsController.php $
 */
#[MenuRoute(path: 'system_logs', class: true)]
class LogsController extends InsideController
{
    /**
     * @var SystemLogs
     */
    protected $model;
    
    /**
     * 操作记录默认查询时间范围
     * @var string
     */
    protected $indexTimeRange;
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model          = SystemLogs::init();
        $this->indexTimeRange = date('Y-m-d 00:00:00', strtotime('-29 days')) . ' - ' . date('Y-m-d 23:59:59');
    }
    
    
    /**
     * 操作记录
     * @return Response
     */
    #[MenuNode(menu: true, parent: '#system_manager', icon: 'fa fa-file-text-o', sort: 2)]
    public function index() : Response
    {
        if ($table = Table::initIfRequest()) {
            if ($table->getOrderField() == SystemLogsField::formatCreateTime()) {
                $table->setOrderField(SystemLogsField::createTime());
            }
            
            return $table
                ->model($this->model)
                ->query(function(SystemLogs $model, ArrayOption $option) {
                    $option->deleteIfLt('type', 0);
                    $option->deleteIfEmpty('client');
                    
                    if ($time = $option->pull('time', $this->indexTimeRange)) {
                        $model->whereTimeIntervalRange(SystemLogsField::createTime(), $time, ' - ', true);
                    }
                    
                    $model->order('id', 'desc');
                })
                ->response();
        }
        
        $this->assign('type_options', TransHelper::toOptionHtml($this->model::getTypes()));
        $this->assign('client_options', TransHelper::toOptionHtml(AppHelper::getList(), null, 'name', 'dir'));
        $this->assign('time', $this->indexTimeRange);
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 清理操作记录
     * @throws DbException
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function clear() : Response
    {
        $len = $this->model->clear();
        $this->log()->record(self::LOG_DELETE, '清空操作记录');
        
        return $this->success('清理成功' . $len . '条');
    }
    
    
    /**
     * 查看操作记录
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function detail() : Response
    {
        $this->assign('info', $this->model->getInfo($this->get('id/d')));
        
        return $this->insideDisplay();
    }
}