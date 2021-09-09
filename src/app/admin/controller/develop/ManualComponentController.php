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
     * 模态框
     */
    public function modal()
    {
        if ($this->isPost()) {
            return $this->success('提交成功');
        }
        
        if ($this->requestPluginName == 'Modal') {
            return $this->display('modal_' . $this->request->request('action'));
        }
        
        return $this->display();
    }
    
    
    /**
     * 颜色选择器
     */
    public function color()
    {
        return $this->display();
    }
    
    
    /**
     * 树形组件
     */
    public function tree()
    {
        if ($this->request->get('action') === 'data') {
            $parentId = $this->request->post('parent_id', 0, 'intval');
            switch ($parentId) {
                case 1:
                    $data = [
                        [
                            'id'       => '1-1',
                            'text'     => '节点1-1',
                            'state'    => [
                                'selected' => true
                            ],
                            'children' => false
                        ],
                        [
                            'id'       => '1-2',
                            'text'     => '节点1-2',
                            'state'    => [
                                'selected' => false
                            ],
                            'children' => false
                        ]
                    ];
                break;
                case 2:
                    $data = [
                        [
                            'id'       => '2-1',
                            'text'     => '节点2-1',
                            'state'    => [
                                'selected' => true
                            ],
                            'children' => false
                        ],
                        [
                            'id'       => '2-2',
                            'text'     => '节点2-2',
                            'state'    => [
                                'selected' => false
                            ],
                            'children' => false
                        ]
                    ];
                break;
                default:
                    $data = [
                        [
                            'id'       => 1,
                            'text'     => '节点1',
                            'state'    => [
                                'selected' => true
                            ],
                            'children' => true
                        ],
                        [
                            'id'       => 2,
                            'text'     => '节点2',
                            'state'    => [
                                'selected' => false
                            ],
                            'children' => true
                        ]
                    ];
            }
            
            return $this->responseJsTree($data);
        }
        
        return $this->display();
    }
    
    
    /**
     * 表格
     */
    public function table()
    {
        return $this->display();
    }
    
    
    /**
     * 文件上传
     */
    public function upload()
    {
        return $this->display();
    }
    
    
    /**
     * 文件选择
     */
    public function file_picker()
    {
        return $this->display();
    }
    
    
    /**
     * 图片预览
     */
    public function image_viewer()
    {
        return $this->display();
    }
    
    
    /**
     * 视频预览
     */
    public function video_viewer()
    {
        return $this->display();
    }
    
    
    /**
     * 富文本编辑器
     */
    public function editor()
    {
        return $this->display();
    }
    
    
    /**
     * 下拉选择器
     */
    public function select_picker()
    {
        return $this->display();
    }
    
    
    /**
     * 输入提示
     */
    public function autocomplete()
    {
        return $this->display();
    }
    
    
    /**
     * 穿梭框
     */
    public function shuttle()
    {
        return $this->display();
    }
    
    
    /**
     * 全选反选
     */
    public function check_all()
    {
        return $this->display();
    }
    
    
    /**
     * 搜索栏
     */
    public function search_bar()
    {
        return $this->display();
    }
}