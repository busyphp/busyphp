<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\config\SystemConfig;
use BusyPHP\app\admin\model\system\config\SystemConfigField;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;
use Throwable;

/**
 * 开发模式-系统键值对配置管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:35 下午 ConfigController.php $
 */
class ConfigController extends InsideController
{
    /**
     * @var SystemConfig
     */
    private $model;
    
    
    public function initialize($checkLogin = true)
    {
        if (!$this->app->isDebug()) {
            abort(404);
        }
        
        parent::initialize($checkLogin);
        
        $this->model = SystemConfig::init();
    }
    
    
    /**
     * 配置列表
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function index() : Response
    {
        if ($this->pluginTable) {
            return $this->success($this->pluginTable->build($this->model));
        }
        
        return $this->display();
    }
    
    
    /**
     * 添加配置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function add() : Response
    {
        if ($this->isPost()) {
            $this->model->createInfo(SystemConfigField::parse($this->post()));
            $this->log()->record(self::LOG_INSERT, '增加系统配置');
            
            return $this->success('添加成功');
        }
        
        return $this->display();
    }
    
    
    /**
     * 修改配置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    public function edit() : Response
    {
        if ($this->isPost()) {
            $this->model->updateInfo(SystemConfigField::parse($this->post()));
            $this->log()->record(self::LOG_UPDATE, '修改系统配置');
            
            return $this->success('修改成功');
        }
        
        $this->assign('info', $this->model->getInfo($this->get('id/d')));
        
        return $this->display('add');
    }
    
    
    /**
     * 删除
     * @throws Throwable
     */
    public function delete() : Response
    {
        foreach ($this->param('id/list/请选择要删除的配置', 'intval') as $id) {
            $this->model->deleteInfo($id);
        }
        
        $this->log()->record(self::LOG_DELETE, '删除系统配置');
        
        return $this->success('删除成功');
    }
} 