<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\common\SimpleForm;
use BusyPHP\app\admin\component\js\driver\Table;
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
#[MenuRoute(path: 'system_config', class: true)]
class ConfigController extends InsideController
{
    /**
     * @var SystemConfig
     */
    protected $model;
    
    
    protected function initialize($checkLogin = true)
    {
        $this->releaseDisabled();
        
        parent::initialize($checkLogin);
        
        $this->model = SystemConfig::init();
    }
    
    
    /**
     * 配置管理
     * @return Response
     */
    #[MenuNode(menu: true, parent: '#developer', icon: 'fa fa-cube', sort: 20, canDisable: false)]
    public function index() : Response
    {
        if ($table = Table::initIfRequest()) {
            return $table->model($this->model)->response();
        }
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 添加配置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function add() : Response
    {
        if ($this->isPost()) {
            $this->model->create(SystemConfigField::init($this->post()));
            $this->log()->record(self::LOG_INSERT, '增加系统配置');
            
            return $this->success('添加成功');
        }
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 修改配置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function edit() : Response
    {
        if ($this->isPost()) {
            $this->model->modify(SystemConfigField::init($this->post()));
            $this->log()->record(self::LOG_UPDATE, '修改系统配置');
            
            return $this->success('修改成功');
        }
        
        $this->assign('info', $this->model->getInfo($this->get('id/d')));
        
        return $this->insideDisplay('add');
    }
    
    
    /**
     * 删除配置
     * @throws Throwable
     */
    #[MenuNode(menu: false, parent: '/index')]
    public function delete() : Response
    {
        SimpleForm::init()->batch($this->param('id/a', 'intval'), '请选择要删除的配置', function(int $id) {
            $this->model->remove($id);
        });
        
        $this->log()->record(self::LOG_DELETE, '删除系统配置');
        
        return $this->success('删除成功');
    }
} 