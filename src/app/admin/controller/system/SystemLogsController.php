<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\logs\SystemLogsField;
use BusyPHP\app\admin\model\system\logs\SystemLogsInfo;
use BusyPHP\app\admin\plugin\table\TableHandler;
use BusyPHP\app\admin\plugin\TablePlugin;
use BusyPHP\helper\AppHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\app\admin\model\system\logs\SystemLogs;
use BusyPHP\Model;
use BusyPHP\model\Map;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;
use think\response\View;

/**
 * 系统日志管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/18 下午下午3:46 SystemLogsController.php $
 */
class SystemLogsController extends InsideController
{
    /**
     * 日志管理
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function index()
    {
        $timeRange = date('Y-m-d 00:00:00', strtotime('-29 days')) . ' - ' . date('Y-m-d 23:59:59');
        if ($this->pluginTable) {
            $this->pluginTable->setHandler(new class($timeRange) extends TableHandler {
                private $timeRange;
                
                
                public function __construct($timeRange)
                {
                    $this->timeRange = $timeRange;
                }
                
                
                public function query(TablePlugin $plugin, Model $model, Map $data) : void
                {
                    if ($data->get('type', -1) < 0) {
                        $data->remove('type');
                    }
                    
                    if (!$data->get('client', '')) {
                        $data->remove('client');
                    }
                    
                    if ($time = $data->get('time', $this->timeRange)) {
                        $model->whereTimeIntervalRange(SystemLogsField::createTime(), $time, ' - ', true);
                    }
                    $data->remove('time');
                    
                    if ($plugin->sortField === (string) SystemLogsInfo::formatCreateTime()) {
                        $plugin->sortField = SystemLogsInfo::createTime();
                    }
                }
            });
            
            return $this->success($this->pluginTable->build(SystemLogs::init()));
        }
        
        $this->assign('type_options', TransHelper::toOptionHtml(SystemLogs::getTypes()));
        $this->assign('client_options', TransHelper::toOptionHtml(AppHelper::getList(), null, 'name', 'dir'));
        $this->assign('time', $timeRange);
        
        return $this->display();
    }
    
    
    /**
     * 清空操作记录
     * @throws DbException
     */
    public function clear()
    {
        $len = SystemLogs::init()->clear();
        $this->log()->record(self::LOG_DELETE, '清空操作记录');
        
        return $this->success('清理成功' . $len . '条');
    }
    
    
    /**
     * 查看操作记录
     * @return View
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function detail()
    {
        $this->assign('info', $info = SystemLogs::init()->getInfo($this->get('id/d')));
        
        return $this->display();
    }
}