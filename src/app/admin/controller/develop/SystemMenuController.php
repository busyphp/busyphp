<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\helper\util\Arr;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\system\menu\SystemMenu as Model;
use BusyPHP\app\admin\model\system\menu\SystemMenuField;

/**
 * 开发模式-后台菜单管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/1 下午3:35 下午 SystemMenu.php $
 */
class SystemMenuController extends InsideController
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
        
        if ($this->request->header('Busy-Admin-Table')) {
            $selectList = $this->model->order('sort', 'asc')->selectList();
            foreach ($selectList as $i => $item) {
                $selectList[$i] = $item;
            }
            
            return $this->success('', '', [
                'options' => [
                    'columns'         => [
                        [
                            [
                                'field'     => 'sort',
                                'title'     => '排序',
                                'formatter' => 'tableSortFormatter',
                                'width'     => 60,
                                'align'     => 'center',
                                'falign'    => 'left'
                            ],
                            [
                                'field'     => 'icon',
                                'formatter' => 'tableIconFormatter',
                                'title'     => '图标',
                                'width'     => 50,
                                'align'     => 'center',
                            ],
                            [
                                'field'      => 'name',
                                'formatter' => 'tableNameFormatter',
                                'title'      => '菜单名称',
                                'halign'     => 'center',
                            ],
                            [
                                'field' => 'is_default',
                                'title' => '默认',
                                'align' => 'center',
                                'width' => 60,
                            ],
                            [
                                'field' => 'is_disabled',
                                'title' => '禁用',
                                'align' => 'center',
                                'width' => 60,
                            ],
                            [
                                'field' => 'is_show',
                                'title' => '隐藏',
                                'align' => 'center',
                                'width' => 60,
                            ],
                            [
                                'field'     => 'operate',
                                'title'     => '操作',
                                'align'     => 'center',
                                'formatter' => 'tableOperateFormatter',
                                'width'     => 80,
                            ]
                        ],
                    ],
                    'paginationParts' => [],
                ],
                'list'    => [
                    'total'            => count($selectList),
                    'totalNotFiltered' => count($selectList),
                    'rows'             => $selectList,
                    /*'footer'           => [
                        'check'       => '你',
                        'id'          => '',
                        '_id_colspan' => 3,
                        'view_number' => '',
                        'desc'        => '',
                    ]*/
                ]
            ]);
        }
        
        return $this->display();
    }
    
    
    /**
     * 增加
     */
    public function add()
    {
        return $this->submit('post', function($data) {
            $insert = SystemMenuField::init();
            
            if (SystemMenu::DEBUG) {
                $insert->setIsSystem($data['is_system']);
            }
            
            $insert->setParentId($data['parent_id']);
            $insert->setType($data['type']);
            $insert->setName($data['name']);
            $insert->setModule($data['module']);
            $insert->setControl($data['control']);
            $insert->setAction($data['action']);
            $insert->setIcon($data['icon']);
            $insert->setIsDefault($data['is_default']);
            $insert->setIsDisabled($data['is_disabled']);
            $insert->setIsHide($data['is_hide']);
            $insert->setHigher($data['higher']);
            $insert->setParams($data['params']);
            $insert->setTarget($data['target']);
            $insert->setLink($data['link']);
            $this->model->createMenu($insert);
            $this->log('增加系统菜单', $this->model->getHandleData(), self::LOG_INSERT);
            $this->updateCache();
            
            return '添加成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                $array                   = [];
                $array['list']           = json_encode(Arr::listByKey($this->model->getList(), SystemMenuField::id()), JSON_UNESCAPED_UNICODE);
                $array['tree']           = json_encode($this->model->getTreeList(), JSON_UNESCAPED_UNICODE);
                $array['target_options'] = Transform::arrayToOption(Model::getTargets());
                $array['tree_options']   = $this->model->getTreeOptions();
                $array['debug']          = SystemMenu::DEBUG;
                
                return $array;
            });
            
            $this->setRedirectUrl(url('index'));
            $this->submitName = '添加';
        });
    }
    
    
    /**
     * 修改
     */
    public function edit()
    {
        return $this->submit('post', function($data) {
            $update = SystemMenuField::init();
            
            if (SystemMenu::DEBUG) {
                $update->setIsSystem($data['is_system']);
            }
            
            $update->setId($data['id']);
            $update->setParentId($data['parent_id']);
            $update->setType($data['type']);
            $update->setName($data['name']);
            $update->setModule($data['module']);
            $update->setControl($data['control']);
            $update->setAction($data['action']);
            $update->setIcon($data['icon']);
            $update->setIsDefault($data['is_default']);
            $update->setIsDisabled($data['is_disabled']);
            $update->setIsHide($data['is_hide']);
            $update->setHigher($data['higher']);
            $update->setParams($data['params']);
            $update->setTarget($data['target']);
            $update->setLink($data['link']);
            $this->model->updateMenu($update);
            $this->log('修改系统菜单', $this->model->getHandleData(), self::LOG_UPDATE);
            $this->updateCache();
            
            return '修改成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                $info                   = $this->model->getInfo($this->iGet('id'));
                $info['list']           = json_encode(Arr::listByKey($this->model->getList(), SystemMenuField::id()), JSON_UNESCAPED_UNICODE);
                $info['tree']           = json_encode($this->model->getTreeList(), JSON_UNESCAPED_UNICODE);
                $info['target_options'] = Transform::arrayToOption(Model::getTargets(), '', '', $info->target);
                $info['tree_options']   = $this->model->getTreeOptions($info->parentId);
                $info['debug']          = SystemMenu::DEBUG;
                
                return $info;
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
            $this->log('排序系统菜单', $params, self::LOG_BATCH);
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
            $this->model->deleteInfo($id);
        });
        
        $this->bind(self::CALL_BATCH_EACH_AFTER, function($params) {
            $this->log('删除系统菜单', ['id' => $params], self::LOG_DELETE);
            $this->updateCache();
            
            return '删除成功';
        });
        
        return $this->batch();
    }
}