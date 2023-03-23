<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\annotation\MenuGroup;
use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\js\Driver;
use BusyPHP\app\admin\component\js\driver\LinkagePicker;
use BusyPHP\app\admin\component\js\driver\LinkagePicker\LinkagePickerFlatNode;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\model\system\menu\SystemMenuField;
use think\Response;

/**
 * 组件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/2/28 下午9:01 下午 ComponentController.php $
 */
#[MenuRoute(path: 'manual_component', class: true)]
#[MenuGroup(path: 'developer_manual_component', parent: '#developer_manual', icon: 'fa fa-sliders')]
class ComponentController extends InsideController
{
    protected function initialize($checkLogin = true)
    {
        $this->releaseDisabled();
        
        parent::initialize($checkLogin);
    }
    
    
    /**
     * 对话框
     */
    #[MenuNode(menu: true, icon: 'fa fa-clone')]
    public function dialog() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 模态框
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-dialog')]
    public function modal() : Response
    {
        if ($this->isPost()) {
            return $this->success('提交成功');
        }
        
        if (Driver::getRequestName() == 'Modal') {
            return $this->insideDisplay('modal_' . $this->request->request('action'));
        }
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 文件选择
     */
    #[MenuNode(menu: true, icon: 'fa fa-folder-open')]
    public function file_picker() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 文件上传
     */
    #[MenuNode(menu: true, icon: 'fa fa-cloud-upload')]
    public function upload() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 数据表格
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-table')]
    public function table() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 树形组件
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-tree')]
    public function tree() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 联级选择器
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-tree')]
    public function linkage_picker() : Response
    {
        if ($picker = LinkagePicker::initIfRequest()) {
            return $picker
                ->model(SystemMenu::init())
                ->list(function(LinkagePickerFlatNode $node, SystemMenuField $item, int $index) {
                    $node->setId($item->hash);
                    $node->setName($item->name);
                    $node->setParent($item->parentHash);
                })
                ->response();
        }
        
        $this->assign('tree', json_encode(SystemMenu::init()->getTree(), JSON_UNESCAPED_UNICODE));
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 穿梭框
     */
    #[MenuNode(menu: true, icon: 'fa fa-exchange')]
    public function shuttle() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 全选/反选
     */
    #[MenuNode(menu: true, icon: 'fa fa-check-square-o')]
    public function check_all() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 搜索栏
     */
    #[MenuNode(menu: true, icon: 'fa fa-search')]
    public function search_bar() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 自动表单
     */
    #[MenuNode(menu: true, icon: 'fa fa-wpforms')]
    public function form() : Response
    {
        if ($this->isPost()) {
            return $this->success('提交成功');
        }
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 表单验证
     */
    #[MenuNode(menu: true, icon: 'fa fa-check')]
    public function form_verify() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 自动请求
     */
    #[MenuNode(menu: true, icon: 'fa fa-random')]
    public function request() : Response
    {
        if ($this->isPost()) {
            return $this->success('请求成功');
        }
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 日期/时间
     */
    #[MenuNode(menu: true, icon: 'fa fa-calendar')]
    public function date() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 下拉选择器
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-down-menu')]
    public function select_picker() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * Checkbox/Radio
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-checkbox-checked')]
    public function checkbox_radio() : Response
    {
        if (Driver::getRequestName() === 'Checkbox' || Driver::getRequestName() === 'Radio') {
            return $this->success();
        }
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 输入提示
     */
    #[MenuNode(menu: true, icon: 'fa fa-pencil')]
    public function autocomplete() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 下拉菜单
     */
    #[MenuNode(menu: true, icon: 'fa fa-chevron-circle-down')]
    public function dropdown() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 选项卡
     */
    #[MenuNode(menu: true, icon: 'fa fa-flag-o')]
    public function tab() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 提示工具
     */
    #[MenuNode(menu: true, icon: 'fa fa-commenting-o')]
    public function tooltip() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 弹出框
     */
    #[MenuNode(menu: true, icon: 'fa fa-comment-o')]
    public function popover() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 折叠面板
     */
    #[MenuNode(menu: true, icon: 'fa fa-server')]
    public function collapse() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 富文本编辑器
     */
    #[MenuNode(menu: true, icon: 'fa fa-edit')]
    public function editor() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 颜色选择器
     */
    #[MenuNode(menu: true, icon: 'fa fa-eyedropper')]
    public function color_picker() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 图片预览
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-image-viewer')]
    public function image_viewer() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 视频预览
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-video')]
    public function video_viewer() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 图标选择器
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-dcs')]
    public function icon_picker() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 滑块
     */
    #[MenuNode(menu: true, icon: 'fa fa-sliders')]
    public function range_slider() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 评分
     */
    #[MenuNode(menu: true, icon: 'fa fa-star-o')]
    public function rate() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 日历
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-calendar-block')]
    public function calendar() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 代码高亮
     */
    #[MenuNode(menu: true, icon: 'fa fa-file-code-o')]
    public function high_code() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 目录
     */
    #[MenuNode(menu: true, icon: 'fa fa-list-ol')]
    public function catalog() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 复制/剪切
     */
    #[MenuNode(menu: true, icon: 'fa fa-copy')]
    public function copy() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 随机字符
     */
    #[MenuNode(menu: true, icon: 'fa fa-refresh')]
    public function random() : Response
    {
        return $this->insideDisplay();
    }
}