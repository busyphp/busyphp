<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\event\AdminPanelDisplayEvent;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassField;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\setting\AdminSetting;
use BusyPHP\app\admin\model\system\logs\SystemLogs;
use BusyPHP\app\admin\setting\UploadSetting;
use BusyPHP\app\admin\setting\PublicSetting;
use BusyPHP\app\admin\setting\WatermarkSetting;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\model\Map;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\facade\Filesystem;
use think\Response;

/**
 * 系统管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/18 下午下午3:50 SystemManager.php $
 */
class SystemManagerController extends InsideController
{
    /**
     * 系统基本设置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ParamInvalidException
     */
    public function index()
    {
        if ($this->isPost()) {
            $data = $this->request->post('data', []);
            PublicSetting::init()->set($data);
            $this->log('系统基本设置', $data, SystemLogs::TYPE_SET);
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        $info = PublicSetting::init()->get();
        $this->assign('info', $info);
        $this->assign('extend_template', AdminPanelDisplayEvent::triggerEvent('System.Index/index', $info));
        
        return $this->display();
    }
    
    
    /**
     * 后台安全设置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ParamInvalidException
     */
    public function admin()
    {
        if ($this->isPost()) {
            $data = $this->request->post('data', []);
            AdminSetting::init()->set($data);
            $this->log('后台安全设置', $data, SystemLogs::TYPE_SET);
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        $this->assign('info', AdminSetting::init()->get());
        
        return $this->display();
    }
    
    
    /**
     * 上传设置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ParamInvalidException
     */
    public function upload()
    {
        if ($this->isPost()) {
            $data = $this->request->post('data', []);
            UploadSetting::init()->set($data);
            
            $this->log('上传设置', $data, SystemLogs::TYPE_SET);
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        $setting = UploadSetting::init();
        
        // 磁盘信息
        $disks = [];
        foreach (Filesystem::getConfig('disks') as $key => $disk) {
            if (($disk['visibility'] ?? '') !== 'public') {
                continue;
            }
            
            // 默认名称
            $name = $disk['name'] ?? '';
            if (!$name) {
                if (strtolower($disk['type'] ?? '') === 'local') {
                    $name = '本地服务器';
                } else {
                    $name = $key;
                }
            }
            
            // 默认描述
            $desc = $disk['description'] ?? '';
            if (!$desc && strtolower($disk['type'] ?? '') === 'local') {
                $root = $disk['root'] ?? '';
                $root = substr($root, strlen($this->app->getRootPath()));
                $desc = "文件直接上传到本地服务器的 <code>{$root}</code> 目录，占用服务器磁盘空间";
            }
            
            $disks[] = [
                'name'    => $name,
                'desc'    => $desc,
                'type'    => $key,
                'checked' => $key == $setting->getDisk()
            ];
        }
        
        $this->assign('clients', SystemFile::getClients());
        $this->assign('disks', $disks);
        $this->assign('file_class', SystemFileClass::init()->order('sort ASC')->selectList());
        $this->assign('type', SystemFile::getTypes());
        $this->assign('info', $setting->get());
        
        return $this->display();
    }
    
    
    /**
     * 图片水印设置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ParamInvalidException
     */
    public function watermark()
    {
        if ($this->isPost()) {
            $data = $this->request->post('data', []);
            WatermarkSetting::init()->set($data);
            $this->log('图片水印设置', $data, SystemLogs::TYPE_SET);
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        $this->assign('info', WatermarkSetting::init()->get());
        
        return $this->display();
    }
    
    
    /**
     * 分类上传设置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ParamInvalidException
     */
    public function file_class()
    {
        // 分类设置
        if ($this->isPost()) {
            $data = SystemFileClassField::init();
            $data->setId($this->request->post('id', 0, 'intval'));
            $data->setAllowExtensions($this->request->post('allow_extensions', '', 'trim'));
            $data->setMaxSize($this->request->post('max_size', 0, 'intval'));
            $data->setMimeType($this->request->post('mime_type', '', 'trim'));
            $data->setWatermark($this->request->post('watermark', 0, 'intval'));
            $data->setThumbType($this->request->post('thumb_type', 0, 'intval'));
            $data->setThumbWidth($this->request->post('thumb_width', 0, 'intval'));
            $data->setThumbHeight($this->request->post('thumb_height', 0, 'intval'));
            SystemFileClass::init()->updateData($data);
            
            $this->log('分类上传设置', SystemFileClass::init()->getHandleData(), SystemLogs::TYPE_UPDATE);
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        // 分类列表数据
        if ($this->pluginTable) {
            $this->pluginTable->setQueryHandler(function(SystemFileClass $model, Map $data) {
                $model->order(SystemFileClassField::sort(), 'asc');
                $model->order(SystemFileClassField::id(), 'desc');
            });
            
            return $this->success($this->pluginTable->build(SystemFileClass::init()));
        }
        
        //
        // 修改分类
        elseif ($this->request->get('action') == 'edit') {
            $info = SystemFileClass::init()->getInfo($this->request->get('id', 0, 'intval'));
            $this->assign('info', $info);
            
            return $this->display('file_class_edit');
        }
        
        // 分类列表
        $this->assign('file_class', SystemFileClass::init()->order('sort ASC')->selectList());
        $this->assign('type', SystemFile::getTypes());
        $this->assign('info', UploadSetting::init()->get());
        
        return $this->display();
    }
    
    
    /**
     * 生成缓存
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function cache_create()
    {
        $this->updateCache();
        $this->log('生成缓存');
        
        return $this->success('生成缓存成功');
    }
    
    
    /**
     * 清理缓存
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function cache_clear()
    {
        $this->clearCache();
        $this->log('清理缓存');
        
        return $this->success('清理缓存成功');
    }
}