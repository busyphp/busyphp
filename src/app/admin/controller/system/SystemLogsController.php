<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\system\logs\SystemLogs;
use BusyPHP\model\Map;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 系统日志管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
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
        if ($this->pluginTable) {
            $this->pluginTable->setQueryHandler(function(SystemLogs $model, Map $data) {
                if ($data->get('type', -1) < 0) {
                    $data->remove('type');
                }
                
                switch ($data->get('admin_type', 0)) {
                    case 1:
                        $data->set('is_admin', 1);
                    break;
                    case 2:
                        $data->set('is_admin', 0);
                    break;
                }
                
                $data->remove('admin_type');
            });
            
            return $this->success($this->pluginTable->build(SystemLogs::init()));
        }
        
        $this->assign('type_options', Transform::arrayToOption(SystemLogs::getTypes()));
        
        return $this->display();
    }
    
    
    /**
     * 清空操作记录
     * @throws DbException
     */
    public function clear()
    {
        $len = SystemLogs::init()->clear();
        $this->log('清空操作记录', '', self::LOG_DELETE);
        
        return $this->success('清理成功' . $len . '条');
    }
    
    
    /**
     * 查看操作记录
     */
    public function detail()
    {
        $this->bind(self::CALL_DISPLAY, function() {
            return SystemLogs::init()->getInfo($this->iGet('id'));
        });
        
        return $this->display();
    }
}