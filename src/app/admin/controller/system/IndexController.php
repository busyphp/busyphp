<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\event\AdminPanelDisplayEvent;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\setting\AdminSetting;
use BusyPHP\app\admin\model\system\logs\SystemLogs;
use BusyPHP\app\admin\setting\PublicSetting;
use BusyPHP\model\Map;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 系统管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午2:21 下午 Index.php $
 */
class IndexController extends InsideController
{
    /**
     * 系统基本设置
     */
    public function index()
    {
        return $this->submit('post', function($data) {
            PublicSetting::init()->set($data['data']);
            $this->log('调整系统基本设置', $data, SystemLogs::TYPE_SET);
            
            return '设置成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                $info = PublicSetting::init()->get();
                
                $this->assign('extend_template', AdminPanelDisplayEvent::triggerEvent('System.Index/index', $info));
                
                return $info;
            });
            
            $this->setRedirectUrl(null);
        });
    }
    
    
    /**
     * 后台安全设置
     */
    public function admin()
    {
        return $this->submit('post', function($data) {
            AdminSetting::init()->set($data['data']);
            $this->log('调整后台安全设置', $data, SystemLogs::TYPE_SET);
            
            return '设置成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                return AdminSetting::init()->get();
            });
            
            $this->setRedirectUrl(null);
        });
    }
    
    
    /**
     * 操作记录
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function logs()
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
     */
    public function clear_logs()
    {
        return $this->submit('request', function() {
            $len = SystemLogs::init()->clear();
            $this->log('清空操作记录', '', self::LOG_DELETE);
            
            return '清理成功' . $len . '条';
        });
    }
    
    
    /**
     * 查看操作记录
     */
    public function view_logs()
    {
        $this->bind(self::CALL_DISPLAY, function() {
            return SystemLogs::init()->getInfo($this->iGet('id'));
        });
        
        return $this->display();
    }
}