<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\admin\group\AdminGroup;
use BusyPHP\app\admin\model\admin\group\AdminGroupField;
use BusyPHP\app\admin\model\system\menu\SystemMenu;

/**
 * 后台用户组权限管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午12:11 下午 Group.php $
 */
class SystemGroupController extends InsideController
{
    /**
     * @var AdminGroup
     */
    private $model;
    
    
    public function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = AdminGroup::init();
    }
    
    
    /**
     * 权限管理列表
     */
    public function index()
    {
        return $this->select($this->model);
    }
    
    
    /**
     * 增加权限
     */
    public function add()
    {
        return $this->submit('post', function($data) {
            $insert = AdminGroupField::init();
            $insert->setName($data['name']);
            $insert->setRule($data['rule']);
            $insert->setDefaultGroup($data['default_group']);
            $this->model->insertData($insert);
            $this->log('增加后台管理权限', $this->model->getHandleData(), self::LOG_INSERT);
            
            return '添加成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                $info              = [];
                $info['rule']      = $this->createCheckbox(SystemMenu::init()->getSafeTree());
                $info['is_system'] = 0;
                
                return $info;
            });
            
            $this->setRedirectUrl(url('index'));
            $this->submitName = '添加';
        });
    }
    
    
    /**
     * 编辑权限
     */
    public function edit()
    {
        return $this->submit('post', function($data) {
            $insert = AdminGroupField::init();
            $insert->setId($data['id']);
            $insert->setName($data['name']);
            $insert->setRule($data['rule']);
            $insert->setDefaultGroup($data['default_group']);
            $this->model->updateData($insert);
            $this->log('修改后台管理权限', $this->model->getHandleData(), self::LOG_UPDATE);
            
            return '修改成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                $info         = AdminGroup::init()->getInfo($this->iGet('id'));
                $rule         = $this->createCheckbox(SystemMenu::init()
                    ->getSafeTree(), $info['default_group'], $info['rule_array']);
                $info['rule'] = $rule;
                
                return $info;
            });
            
            $this->setRedirectUrl();
            $this->submitName   = '修改';
            $this->templateName = 'add';
        });
    }
    
    
    /**
     * 删除权限
     */
    public function delete()
    {
        $this->bind(self::CALL_BATCH_EACH, function($id) {
            $this->model->deleteInfo($id);
        });
        
        $this->bind(self::CALL_BATCH_EACH_AFTER, function($params) {
            $this->log('删除后台管理权限', ['id' => $params], self::LOG_DELETE);
            
            return '删除成功';
        });
        
        return $this->batch();
    }
    
    
    private function createCheckbox($list, $defaultGroup = '', $data = [], $level = 0, $parent = '')
    {
        $checkbox = '';
        foreach ($list as $i => $r) {
            $checked = is_checked(in_array($r['path'], $data));
            
            
            $label = <<<HTML
<label>
    <input type="checkbox" name="rule[]" data-level="{$level}" data-parent="{$parent}" data-module="{$r['module']}" data-control="{$r['control']}" value="{$r['path']}" {$checked}/>
    {$r['name']}
</label>
HTML;
            switch ($level) {
                case 0:
                    $defChecked = is_checked($r['module'] === $defaultGroup);
                    $checkbox   .= <<<HTML
<thead>
    <tr>
        <th>{$label}</th>
        <th><label class="pull-right"><input type="radio" name="default_group" value="{$r['module']}"{$defChecked}/> 设为默认</label></th>
    </tr>
</thead>
HTML;
                break;
                case 1:
                    $checkbox .= '<tr><td class="module">' . $label . '</td><td>';
                break;
                default:
                    $checkbox .= $label;
            }
            
            if ($r['child']) {
                $checkbox .= $this->createCheckbox($r['child'], $defaultGroup, $data, $level + 1, $r['path']);
            }
            
            if ($level === 1) {
                $checkbox .= '</td></tr>';
            }
        }
        
        if ($level == 0) {
            $checkbox = '<table class="table table-bordered table-hover table-checkbox">' . $checkbox . '</table>';
        }
        
        return $checkbox;
    }
}