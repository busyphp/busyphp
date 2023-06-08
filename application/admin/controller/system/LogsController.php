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
     * 日志模型
     * @var SystemLogs
     */
    protected SystemLogs $model;
    
    /**
     * 日志模型字段
     * @var SystemLogsField|string
     */
    protected mixed $field;
    
    /**
     * 查询时间范围
     * @var string
     */
    protected string $timeRange;
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model     = SystemLogs::init();
        $this->field     = $this->model->getFieldClass();
        $this->timeRange = date('Y-m-d 00:00:00', strtotime('-6 month')) . ' - ' . date('Y-m-d 23:59:59');
    }
    
    
    /**
     * 操作记录
     * @return Response
     */
    #[MenuNode(menu: true, parent: '#system_manager', icon: 'fa fa-file-text-o', sort: -80)]
    public function index() : Response
    {
        if ($table = Table::initIfRequest()) {
            if (!$this->adminUser->groupHasSystem) {
                $this->model->where($this->field::client(), 'admin');
                $this->model->where($this->field::userId(), $this->adminUserId);
            }
            
            return $table
                ->model($this->model)
                ->query(function(SystemLogs $model, ArrayOption $option) {
                    $option->deleteIfLt('type', 0);
                    $option->deleteIfEmpty('client');
                    
                    if ($time = $option->pull('time', $this->timeRange)) {
                        $model->whereTimeIntervalRange($this->field::createTime(), $time, ' - ', true);
                    }
                    
                    $this->indexTableQuery($model, $option);
                })
                ->response();
        }
        
        $this->assignIndexData();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 自定义列表查询条件
     * @param SystemLogs  $model
     * @param ArrayOption $option
     */
    protected function indexTableQuery(SystemLogs $model, ArrayOption $option)
    {
    }
    
    
    /**
     * 赋值列表模版数据
     */
    protected function assignIndexData()
    {
        // 操作类型
        $typeList = $this->model::getTypeMap();
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
        $this->assign('time', $this->timeRange);
    }
    
    
    /**
     * 清理操作记录
     * @throws DbException
     */
    #[MenuNode(menu: false, parent: '/index', sort: -100)]
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
    #[MenuNode(menu: false, parent: '/index', sort: -90)]
    public function detail() : Response
    {
        $this->assignDetailData();
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 赋值查看操作记录模版数据
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function assignDetailData()
    {
        $this->assign('info', $this->model->getInfo($this->get('id/d')));
    }
}