<?php

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\controller\InsideController;

/**
 * 组件开发手册
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/2/28 下午9:01 下午 ManualController.php $
 */
class ManualComponentController extends InsideController
{
    /**
     * 日期/时间
     */
    public function date()
    {
        return $this->display();
    }
    
    
    /**
     * 目录
     */
    public function catalog()
    {
        return $this->display();
    }
    
    
    /**
     * 复制/剪切
     */
    public function copy()
    {
        return $this->display();
    }
    
    
    /**
     * 代码高亮
     */
    public function high_code()
    {
        return $this->display();
    }
    
    
    /**
     * 单页对话框
     */
    public function page_dialog()
    {
        if ($this->request->header('Busy-Admin-Page-Dialog')) {
            return $this->display('page_dialog_' . $this->request->request('action'));
        }
        
        return $this->display();
    }
    
    
    /**
     * 表格
     */
    public function table()
    {
        if ($this->request->header('Busy-Admin-Table')) {
            $limit = $this->request->param('limit', 10, 'intval');
            $page  = $this->request->param('page', 1, 'intval');
            
            $data = [];
            for ($i = 0; $i < $limit; $i++) {
                $data[] = [
                    'id'          => $i,
                    'name'        => '张三 ' . $i,
                    'flow_number' => rand(1, 9999),
                    'view_number' => '<span class="label label-success">' . rand(1, 9999) . '</span>',
                    'desc'        => '描述' . rand(1, 9999),
                ];
            }
            
            return $this->success('', '', [
                'options' => [
                    'columns' => [
                        [
                            [
                                'checkbox' => true,
                                'rowspan'  => 2,
                                'field'    => 'check',
                                'width'    => 40
                            ],
                            [
                                'rowspan' => 2,
                                'field'   => 'id'
                            ],
                            [
                                'field'      => 'name',
                                'title'      => '名称',
                                'sortable'   => true,
                                'switchable' => false,
                                'rowspan'    => 2,
                            ],
                            [
                                'title'   => '详情',
                                'colspan' => 3
                            ]
                        ],
                        [
                            [
                                'field'       => 'flow_number',
                                'title'       => '关注数',
                                'sortable'    => true,
                                'searchable'  => true,
                                'searchType'  => 'select',
                                'searchValue' => [
                                    ['name' => '张三', 'value' => 1],
                                    ['name' => '张三1', 'value' => 2],
                                ],
                                'width'       => 100,
                            ],
                            [
                                'field'      => 'view_number',
                                'title'      => '浏览数',
                                'sortable'   => true,
                                'searchType' => 'input',
                                'searchAttr' => 'type="number" placeholder="输入关键词搜索"',
                                'width'      => 100,
                            ],
                            [
                                'field'      => 'desc',
                                'title'      => '描述',
                                'searchType' => 'input',
                                'width'      => 100,
                            ]
                        ]
                    ],
                ],
                'list'    => [
                    'total'            => 800,
                    'totalNotFiltered' => 800,
                    'rows'             => $data,
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
        
        if ($this->request->request('action')) {
            return $this->success('删除成功', '', $this->request->param('action'));
        }
        
        return $this->display();
    }
}