<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\helper\util\Filter;
use BusyPHP\helper\util\Transform;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\logs\SystemLogs;
use BusyPHP\app\admin\setting\FileSetting;

/**
 * 附件管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/6/4 下午2:17 下午 File.php $
 */
class File extends InsideController
{
    /**
     * @var SystemFile
     */
    private $model;
    
    
    public function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $this->model = SystemFile::init();
    }
    
    
    /**
     * 列表
     */
    public function index()
    {
        $this->setSelectWhere(function($where) {
            if (!$where['classify']) {
                unset($where['classify']);
            }
            if (!$where['mark_type']) {
                unset($where['mark_type']);
            }
            
            switch (intval($where['admin_type'])) {
                case 1:
                    $where['is_admin'] = 1;
                break;
                case 2:
                    $where['is_admin'] = 0;
                break;
            }
            unset($where['admin_type']);
            
            return $where;
        });
        $this->assign('classify_options', Transform::arrayToOption(SystemFile::getTypes()));
        $this->assign('type_options', Transform::arrayToOption(SystemFileClass::init()
            ->getListByCache(), 'var', 'name'));
        $this->setSelectLimit(50);
        $this->setSelectSimple(true);
        
        return $this->select($this->model);
    }
    
    
    /**
     * 文件上传页面
     */
    public function upload()
    {
        $this->assignUrl('parent', url('index'));
        
        return $this->display();
    }
    
    
    /**
     * 删除附件
     */
    public function delete()
    {
        $this->bind(self::CALL_BATCH_EACH, function($id) {
            $this->model->del($id);
        });
        $this->bind(self::CALL_BATCH_EACH_AFTER, function($params) {
            $this->log('删除附件', ['id' => $params], self::LOG_DELETE);
            
            return '删除成功';
        });
        
        return $this->batch();
    }
    
    
    /**
     * 设置
     */
    public function setting()
    {
        return $this->submit('post', function($data) {
            FileSetting::init()->set($data['data']);
            
            // 附件分类设置
            $fileClassModel = SystemFileClass::init();
            $class          = Filter::trim($_POST['class']);
            foreach ($class as $id => $r) {
                $r['is_thumb']      = Transform::boolToNumber($r['is_thumb']);
                $r['delete_source'] = Transform::boolToNumber($r['delete_source']);
                $r['watermark']     = Transform::boolToNumber($r['watermark']);
                $r['size']          = floatval($r['size']);
                $r['size']          = $r['size'] <= -1 ? -1 : $r['size'];
                $r['thumb_type']    = intval($r['thumb_type']);
                $r['width']         = intval($r['width']);
                $r['height']        = intval($r['height']);
                $fileClassModel->one($id)->saveData($r);
            }
            
            $this->log('设置附件配置', ['data' => $data, 'class' => $class], SystemLogs::TYPE_SET);
            
            return '设置成功';
        }, function() {
            $this->bind(self::CALL_DISPLAY, function() {
                $this->assign('file_class', SystemFileClass::init()->order('sort ASC')->selecting());
                $this->assign('type', SystemFile::getTypes());
                
                return FileSetting::init()->get();
            });
            
            
            $this->setRedirectUrl(null);
        });
    }
    
    
    /**
     * 文件管理器
     */
    public function library()
    {
        $where    = [];
        $search   = $this->iRequest('search', 'intval');
        $present  = $this->iRequest('present', 'intval');
        $current  = $this->iRequest('current');
        $classify = '';
        
        // 代表请求当前信息的附件
        if ($present) {
            $markType            = trim($current['mark_type']);
            $markValue           = trim($current['mark_value']);
            $where['mark_type']  = $markType;
            $where['mark_value'] = $markValue;
            
            // 移除搜索
            unset($_REQUEST['field'], $_REQUEST['word'], $_REQUEST['static']);
        } elseif (!$search) {
            // 代表刚从外部打开
            $markType              = $this->iRequest('mark_type', 'trim');
            $markValue             = $this->iRequest('mark_value', 'trim');
            $current['mark_type']  = $markType;
            $current['mark_value'] = $markValue;
            $where['mark_value']   = $markValue;
            $where['mark_type']    = $markType;
        } else {
            $static   = $this->iRequest('static');
            $markType = trim($static['mark_type'] ?? '');
            $classify = trim($static['classify'] ?? '');
        }
        
        
        // 显示模板
        $listType = $classify;
        
        // 没有指定类型则通过附件分类配置创建显示模板
        if (!$classify && $markType) {
            $fileClass  = SystemFileClass::init()->getListByCache();
            $fileConfig = $fileClass[$markType];
            $listType   = $fileConfig['type'];
        }
        
        if ($listType == SystemFile::FILE_TYPE_FILE || $listType == SystemFile::FILE_TYPE_VIDEO || !$listType) {
            $listType = 'list';
        }
        
        
        $this->setSelectWhere($where, function($where) {
            $where['classify']  = $where['classify'] ?? '';
            $where['mark_type'] = $where['mark_type'] ?? '';
            
            if (!$where['mark_type']) {
                unset($where['mark_type']);
            }
            
            if ($where['classify'] == 'image') {
                unset($where['classify']);
                $where['extension'] = ['in', ['jpg', 'jpeg', 'png', 'gif', 'bmp']];
            }
            
            unset($where['classify']);
            
            return $where;
        });
        $this->assign('file_class_options', SystemFileClass::init()->getAdminOptions($markType, '不限分类'));
        $this->assign('file_type_options', Transform::arrayToOption(SystemFile::getTypes(), '', '', $classify));
        $this->assign('present', $present);
        $this->assign('current', $current);
        $this->assign('all_url', url('?search=1', ['current' => $current]));
        $this->assign('current_url', url('?present=1', ['current' => $current]));
        $this->assign('list_type', $listType);
        
        return $this->select($this->model);
    }
}