<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassField;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\model\Map;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 开发模式-系统附件分类管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:34 下午 SystemFileClass.php $
 */
class SystemFileClassController extends InsideController
{
    /**
     * @var SystemFileClass
     */
    private $model;
    
    
    public function initialize($checkLogin = true)
    {
        if (!$this->app->isDebug()) {
            abort(404);
        }
        
        parent::initialize($checkLogin);
        
        $this->model = SystemFileClass::init();
    }
    
    
    /**
     * 文件分类列表
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function index()
    {
        if ($this->pluginTable) {
            $this->pluginTable->setQueryHandler(function(SystemFileClass $model, Map $data) {
                if (!$data->get('type')) {
                    $data->remove('type');
                }
                
                $model->order(SystemFileClassField::sort(), 'asc');
                $model->order(SystemFileClassField::id(), 'desc');
            });
            
            return $this->success($this->pluginTable->build($this->model));
        }
        
        
        $this->assign('type_options', Transform::arrayToOption(SystemFile::getTypes()));
        
        return $this->display();
    }
    
    
    /**
     * 添加文件分类
     * @return Response
     * @throws DbException
     */
    public function add()
    {
        if ($this->isPost()) {
            $insert = SystemFileClassField::init();
            $insert->setName($this->post('name/s'));
            $insert->setVar($this->post('var/s'));
            $insert->setType($this->post('type/s'));
            $insert->setSystem($this->post('system/b'));
            $this->model->insertData($insert);
            $this->log()->record(self::LOG_INSERT, '增加文件分类');
            
            return $this->success('添加成功');
        }
        
        $this->assign('info', [
            'type_options' => Transform::arrayToOption(SystemFile::getTypes()),
            'system'       => 0,
        ]);
        
        return $this->display();
    }
    
    
    /**
     * 修改文件分类
     * @return Response
     * @throws DbException
     * @throws DataNotFoundException
     * @throws Exception
     */
    public function edit()
    {
        if ($this->isPost()) {
            $update = SystemFileClassField::init();
            $update->setId($this->post('id/d'));
            $update->setName($this->post('name/s'));
            $update->setVar($this->post('var/s'));
            $update->setType($this->post('type/s'));
            $update->setSystem($this->post('system/b'));
            $this->model->updateData($update);
            $this->log()->record(self::LOG_UPDATE, '修改文件分类');
            
            return $this->success('修改成功');
        }
        
        $info                 = $this->model->getInfo($this->get('id'));
        $info['type_options'] = Transform::arrayToOption(SystemFile::getTypes(), '', '', $info->type);
        $this->assign('info', $info);
        
        return $this->display('add');
    }
    
    
    /**
     * 定义排序
     */
    public function sort()
    {
        $this->bind(self::CALL_BATCH_EACH_AFTER, function($params) {
            foreach ($params as $id => $value) {
                $this->model->setSort($id, $value);
            }
            
            $this->log()->record(self::LOG_UPDATE, '排序文件分类');
            
            return '排序成功';
        });
        
        return $this->batch('sort');
    }
    
    
    /**
     * 删除
     */
    public function delete()
    {
        $this->bind(self::CALL_BATCH_EACH, function($id) {
            $this->model->deleteInfo($id);
        });
        $this->bind(self::CALL_BATCH_EACH_AFTER, function($params) {
            $this->log()->record(self::LOG_DELETE, '删除文件分类');
            
            return '删除成功';
        });
        
        return $this->batch();
    }
} 