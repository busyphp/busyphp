<?php
declare(strict_types = 1);

namespace BusyPHP\app\admin\controller\develop;

use BusyPHP\app\admin\annotation\MenuGroup;
use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\js\Driver;
use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use think\Response;

/**
 * 组件
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/2/28 下午9:01 下午 ComponentController.php $
 */
#[MenuRoute(path: 'manual_component', class: true)]
#[MenuGroup(path: 'developer_manual_component', parent: '#developer_manual', icon: 'fa fa-sliders', sort: -90, canDisable: false)]
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
    #[MenuNode(menu: true, icon: 'fa fa-clone', canDisable: false)]
    public function dialog() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 模态框
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-dialog', canDisable: false)]
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
    #[MenuNode(menu: true, icon: 'fa fa-folder-open', canDisable: false)]
    public function file_picker() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 文件上传
     */
    #[MenuNode(menu: true, icon: 'fa fa-cloud-upload', canDisable: false)]
    public function upload() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 数据表格
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-table', canDisable: false)]
    public function table() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 树形组件
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-tree', canDisable: false)]
    public function tree() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 联级选择器
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-tree', canDisable: false)]
    public function linkage_picker() : Response
    {
        $this->assign('tree', json_encode(SystemMenu::init()->getTree(), JSON_UNESCAPED_UNICODE));
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 穿梭框
     */
    #[MenuNode(menu: true, icon: 'fa fa-exchange', canDisable: false)]
    public function shuttle() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 全选/反选
     */
    #[MenuNode(menu: true, icon: 'fa fa-check-square-o', canDisable: false)]
    public function check_all() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 搜索栏
     */
    #[MenuNode(menu: true, icon: 'fa fa-search', canDisable: false)]
    public function search_bar() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 自动表单
     */
    #[MenuNode(menu: true, icon: 'fa fa-wpforms', canDisable: false)]
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
    #[MenuNode(menu: true, icon: 'fa fa-check', canDisable: false)]
    public function validator() : Response
    {
        return $this->insideDisplay('validate');
    }
    
    
    /**
     * 自动请求
     */
    #[MenuNode(menu: true, icon: 'fa fa-random', canDisable: false)]
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
    #[MenuNode(menu: true, icon: 'fa fa-calendar', canDisable: false)]
    public function date() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 下拉选择器
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-down-menu', canDisable: false)]
    public function select_picker() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * Checkbox/Radio
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-checkbox-checked', canDisable: false)]
    public function checkbox_radio() : Response
    {
        if (Driver::getRequestName() === 'Checkbox' || Driver::getRequestName() === 'Radio') {
            return rand(0, 1) ? $this->success() : $this->error('演示性错误提示');
        }
        
        return $this->insideDisplay();
    }
    
    
    /**
     * 输入提示
     */
    #[MenuNode(menu: true, icon: 'fa fa-pencil', canDisable: false)]
    public function autocomplete() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 下拉菜单
     */
    #[MenuNode(menu: true, icon: 'fa fa-chevron-circle-down', canDisable: false)]
    public function dropdown() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 选项卡
     */
    #[MenuNode(menu: true, icon: 'fa fa-flag-o', canDisable: false)]
    public function tab() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 提示工具
     */
    #[MenuNode(menu: true, icon: 'fa fa-commenting-o', canDisable: false)]
    public function tooltip() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 弹出框
     */
    #[MenuNode(menu: true, icon: 'fa fa-comment-o', canDisable: false)]
    public function popover() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 折叠面板
     */
    #[MenuNode(menu: true, icon: 'fa fa-server', canDisable: false)]
    public function collapse() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 富文本编辑器
     */
    #[MenuNode(menu: true, icon: 'fa fa-edit', canDisable: false)]
    public function editor() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 颜色选择器
     */
    #[MenuNode(menu: true, icon: 'fa fa-eyedropper', canDisable: false)]
    public function color_picker() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 图片预览
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-image-viewer', canDisable: false)]
    public function image_viewer() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 视频播放器
     */
    #[MenuNode(menu: true, icon: 'fa fa-youtube-play', canDisable: false)]
    public function video() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 视频预览
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-video', canDisable: false)]
    public function video_viewer() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 音频播放器
     */
    #[MenuNode(menu: true, icon: 'fa fa-audio-description', canDisable: false)]
    public function audio() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 音频预览
     */
    #[MenuNode(menu: true, icon: 'fa fa-play-circle', canDisable: false)]
    public function audio_viewer() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 图标选择器
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-dcs', canDisable: false)]
    public function icon_picker() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 滑块
     */
    #[MenuNode(menu: true, icon: 'fa fa-sliders', canDisable: false)]
    public function range_slider() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 评分
     */
    #[MenuNode(menu: true, icon: 'fa fa-star-o', canDisable: false)]
    public function rate() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 打印
     */
    #[MenuNode(menu: true, icon: 'fa fa-print', canDisable: false)]
    public function print() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 任务日志
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-dcs', canDisable: false)]
    public function console_log() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 日历
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-calendar-block', canDisable: false)]
    public function calendar() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 代码高亮
     */
    #[MenuNode(menu: true, icon: 'fa fa-file-code-o', canDisable: false)]
    public function high_code() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 目录
     */
    #[MenuNode(menu: true, icon: 'fa fa-list-ol', canDisable: false)]
    public function catalog() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 复制/剪切
     */
    #[MenuNode(menu: true, icon: 'fa fa-copy', canDisable: false)]
    public function copy() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 随机字符
     */
    #[MenuNode(menu: true, icon: 'fa fa-refresh', canDisable: false)]
    public function random() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 模版引擎
     * @return Response
     */
    #[MenuNode(menu: true, icon: 'fa fa-code', canDisable: false)]
    public function template() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * 图片占位
     * @return Response
     */
    #[MenuNode(menu: true, icon: 'fa fa-image', canDisable: false)]
    public function image_placeholder() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * Vue
     * @return Response
     */
    #[MenuNode(menu: true, icon: 'fa fa-code', canDisable: false)]
    public function vue() : Response
    {
        return $this->insideDisplay();
    }
    
    
    /**
     * ElementUI
     * @return Response
     */
    #[MenuNode(menu: true, icon: 'bicon bicon-grid', canDisable: false)]
    public function element_ui() : Response
    {
        return $this->insideDisplay();
    }
}