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
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = SystemLogs::init();
    }
    
    
    /**
     * 操作记录
     * @return Response
     */
    #[MenuNode(menu: true, parent: '#system_manager', icon: 'fa fa-file-text-o', sort: 2)]
    public function index() : Response
    {
        $timeRange = date('Y-m-d 00:00:00', strtotime('-6 month')) . ' - ' . date('Y-m-d 23:59:59');
        if ($table = Table::initIfRequest()) {
            return $table
                ->model($this->model)
                ->query(function(SystemLogs $model, ArrayOption $option) use ($timeRange) {
                    $option->deleteIfLt('type', 0);
                    $option->deleteIfEmpty('client');
                    
                    if ($time = $option->pull('time', $timeRange)) {
                        $model->whereTimeIntervalRange(SystemLogsField::createTime(), $time, ' - ', true);
                    }
                })
                ->response();
        }
        
        // 操作类型
        $typeList = $this->model::getTypes();
        $typeList = [-1 => '不限'] + $typeList;
        $this->assign('types', $typeList);
        
        // 客户端
        $clientList = AppHelper::getList();
        array_unshift($clientList, ['name' => '不限', 'dir' => '']);
        $clientList[] = ['name' => AppHelper::CLI_CLIENT_NAME, 'dir' => AppHelper::CLI_CLIENT_KEY];
        $clientList   = array_map(function($item) {
            return [
                'name' => $item['name'],
                'dir'  => $item['dir']
            ];
        }, $clientList);
        $this->assign('clients', $clientList);
        
        // 默认时间
        $this->assign('time', $timeRange);
        
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