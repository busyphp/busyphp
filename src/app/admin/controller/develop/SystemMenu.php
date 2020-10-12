<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\system\menu\SystemMenu as Model;
use BusyPHP\app\admin\model\system\menu\SystemMenuField;

/**
 * 开发模式-后台菜单管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:35 下午 SystemMenu.php $
 */
class SystemMenu extends InsideController
{
    /**
     * @var Model
     */
    private $model;
    
    
    public function initialize($checkLogin = true)
    {
        if (!$this->app->isDebug()) {
            abort(404);
        }
        
        parent::initialize($checkLogin);
        
        $this->model = Model::init();
    }
    
    
    /**
     * 栏目列表
     */
    public function index()
    {
        $this->assign('list', $this->model->getTreeList());
        
        return $this->display();
    }
    
    
    /**
     * 增加
     */
    public function add()
    {
        return $this->submit('post', function($data) {
            $type   = intval($data['type']);
            $var    = trim($data['var']);
            $insert = SystemMenuField::init();
            $insert->setName($data['name']);
            $insert->setVar($var);
            $insert->setType($type);
            $insert->setIcon($data['icon']);
            $insert->setIsDefault($data['is_default']);
            $insert->setIsDisabled($data['is_disabled']);
            $insert->setIsHasAction($data['is_has_action']);
            $insert->setIsShow($data['is_show']);
            $insert->setIsSystem($data['is_system']);
            
            // 按类型添加
            switch ($type) {
                // 控制器
                case Model::TYPE_CONTROL:
                    $insert->setControl($var);
                    $insert->setModule($data['module']);
                break;
                
                // 执行方法
                case Model::TYPE_ACTION:
                    $insert->setAction($var);
                    $insert->setIcon($data['icon']);
                    $insert->setModule($data['module']);
                    $insert->setControl($data['control']);
                    $insert->setHigher($data['higher']);
                break;
                
                // 执行类型
                case Model::TYPE_PATTERN:
                    $insert->setPattern($var);
                    $insert->setIcon($data['icon']);
                    $insert->setModule($data['module']);
                    $insert->setControl($data['control']);
                    $insert->setAction($data['action']);
                break;
                
                // 分组
                case Model::TYPE_MODULE:
                default:
                    $insert->setModule($var);
                    $insert->setIcon($data['icon']);
            }
            
            $insert->setParams($data['params']);
            $insert->setLink($data['link']);
            $insert->setTarget($data['target']);
            $insert->setSort($data['sort']);
            
            $this->model->insertData($insert);
            $this->log('增加系统菜单', $this->model->getHandleData(), self::LOG_INSERT);
            $this->updateCache();
            
            return '添加成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                $array                   = [];
                $array['target_options'] = Transform::arrayToOption(Model::getTargets());
                $array['tree']           = json_encode($this->model->getTreeList(), JSON_UNESCAPED_UNICODE);
                $array['is_show']        = 1;
                $array['is_has_action']  = 1;
                $array['type']           = isset($_GET['type']) ? intval($_GET['type']) : Model::TYPE_CONTROL;
                $array['sort']           = 50;
                $array['type_list']      = Model::getTypes();
                
                return $array;
            });
            
            $this->setRedirectUrl(url('index'));
            $this->submitName   = '添加';
            $this->templateName = 'add';
        });
    }
    
    
    /**
     * 修改
     */
    public function edit()
    {
        return $this->submit('post', function($data) {
            $info   = $this->model->getInfo($data['id']);
            $type   = intval($info['type']);
            $var    = trim($data['var']);
            $update = SystemMenuField::init();
            $update->setId($data['id']);
            $update->setName($data['name']);
            $update->setVar($var);
            $update->setType($type);
            $update->setIcon($data['icon']);
            $update->setIsDefault($data['is_default']);
            $update->setIsDisabled($data['is_disabled']);
            $update->setIsHasAction($data['is_has_action']);
            $update->setIsShow($data['is_show']);
            $update->setIsSystem($data['is_system']);
            
            // 按类型添加
            switch ($type) {
                // 控制器
                case Model::TYPE_CONTROL:
                    $update->setControl($var);
                    $update->setModule($data['module']);
                break;
                
                // 执行方法
                case Model::TYPE_ACTION:
                    $update->setAction($var);
                    $update->setIcon($data['icon']);
                    $update->setModule($data['module']);
                    $update->setControl($data['control']);
                    $update->setHigher($data['higher']);
                break;
                
                // 执行类型
                case Model::TYPE_PATTERN:
                    $update->setPattern($var);
                    $update->setIcon($data['icon']);
                    $update->setModule($data['module']);
                    $update->setControl($data['control']);
                    $update->setAction($data['action']);
                break;
                
                // 分组
                case Model::TYPE_MODULE:
                default:
                    $update->setModule($var);
                    $update->setIcon($data['icon']);
            }
            
            $update->setParams($data['params']);
            $update->setLink($data['link']);
            $update->setTarget($data['target']);
            $update->setSort($data['sort']);
            
            $this->model->updateData($update);
            $this->log('修改系统菜单', $this->model->getHandleData(), self::LOG_UPDATE);
            $this->updateCache();
            
            return '修改成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                $array                   = $this->model->getInfo($this->iGet('id'));
                $array['target_options'] = Transform::arrayToOption(Model::getTargets(), '', '', $array['target']);
                $array['tree']           = json_encode($this->model->getTreeList());
                $array['type_list']      = Model::getTypes();
                
                return $array;
            });
            
            
            $this->setRedirectUrl();
            $this->submitName   = '修改';
            $this->templateName = 'add';
        });
    }
    
    
    /**
     * 排序
     */
    public function set_sort()
    {
        $this->bind(self::CALL_BATCH_EACH, function($value, $id) {
            $this->model->setSort($id, $value);
        });
        $this->bind(self::CALL_BATCH_EACH_AFTER, function($params) {
            $this->log('排序系统菜单', $params, self::LOG_DELETE);
            $this->updateCache();
            
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
            $this->model->del($id);
        });
        $this->bind(self::CALL_BATCH_EACH_AFTER, function($params) {
            $this->log('删除系统菜单', ['id' => $params], self::LOG_DELETE);
            $this->updateCache();
            
            return '删除成功';
        });
        
        return $this->batch();
    }
}