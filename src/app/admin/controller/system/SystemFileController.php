<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\SystemFileField;
use BusyPHP\app\admin\plugin\table\TableHandler;
use BusyPHP\app\admin\plugin\TablePlugin;
use BusyPHP\helper\TransHelper;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\Model;
use BusyPHP\model\Map;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 附件管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午2:17 下午 File.php $
 */
class SystemFileController extends InsideController
{
    /**
     * @var SystemFile
     */
    private $model;
    
    
    public function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = SystemFile::init();
    }
    
    
    /**
     * 列表
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function index() : Response
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
                    if (!$type = $data->get('type', '')) {
                        $data->remove('type');
                    }
                    if (0 === strpos($type, 'type:')) {
                        $data->set('type', substr($type, 5));
                    } elseif ($type) {
                        $data->set('class_type', $type);
                        $data->remove('type');
                    }
                    
                    if (!$data->get('client', '')) {
                        $data->remove('client');
                    }
                    
                    if ($time = $data->get('time', $this->timeRange)) {
                        $model->whereTimeIntervalRange(SystemFileField::createTime(), $time, ' - ', true);
                    }
                    $data->remove('time');
                    
                    if ($plugin->sortField === 'format_size') {
                        $plugin->sortField = 'size';
                    } elseif ($plugin->sortField === 'format_create_time') {
                        $plugin->sortField = 'create_time';
                    }
                }
            });
            
            return $this->success($this->pluginTable->build($this->model->whereComplete()));
        }
        
        $this->assign('type_options', SystemFileClass::init()->getAdminOptions('', '不限'));
        $this->assign('client_options', TransHelper::toOptionHtml($this->app->getList(), null, 'name', 'dir'));
        $this->assign('time', $timeRange);
        
        return $this->display();
    }
    
    
    /**
     * 文件上传
     * @return Response
     * @throws Exception
     */
    public function upload() : Response
    {
        return $this->display();
    }
    
    
    /**
     * 删除附件
     * @return Response
     * @throws Exception
     */
    public function delete() : Response
    {
        foreach ($this->param('id/list/请选择要删除的文件', 'intval') as $id) {
            $this->model->deleteInfo($id);
        }
        
        $this->log()->record(self::LOG_DELETE, '删除文件');
        
        return $this->success('删除成功');
    }
}