<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\file\SystemFileField;
use BusyPHP\helper\TransHelper;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
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
    public function index()
    {
        $timeRange = date('Y-m-d 00:00:00', strtotime('-29 days')) . ' - ' . date('Y-m-d 23:59:59');
        if ($this->pluginTable) {
            $this->pluginTable->setQueryHandler(function(SystemFile $model, Map $data) use ($timeRange) {
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
                
                if ($time = $data->get('time', $timeRange)) {
                    $model->whereTimeIntervalRange(SystemFileField::createTime(), $time, ' - ', true);
                }
                $data->remove('time');
                
                if ($this->pluginTable->sortField === 'format_size') {
                    $this->pluginTable->sortField = 'size';
                } elseif ($this->pluginTable->sortField === 'format_create_time') {
                    $this->pluginTable->sortField = 'create_time';
                }
            });
            
            return $this->success($this->pluginTable->build($this->model));
        }
        
        $this->assign('type_options', SystemFileClass::init()->getAdminOptions('', '不限'));
        $this->assign('client_options', TransHelper::toOptionHtml($this->app->getList(), null, 'dir', 'name'));
        $this->assign('time', $timeRange);
        
        return $this->display();
    }
    
    
    /**
     * 文件上传
     * @return Response
     * @throws Exception
     */
    public function upload()
    {
        return $this->display();
    }
    
    
    /**
     * 删除附件
     * @return Response
     * @throws Exception
     */
    public function delete()
    {
        foreach ($this->param('id/list/请选择要删除的文件', 'intval') as $id) {
            $this->model->deleteInfo($id);
        }
        
        $this->log()->record(self::LOG_DELETE, '删除文件');
        
        return $this->success('删除成功');
    }
}