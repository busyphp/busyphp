<?php

namespace BusyPHP\app\admin\controller\system;

use BusyPHP\app\admin\controller\InsideController;
use BusyPHP\app\admin\event\AdminPanelDisplayEvent;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClass;
use BusyPHP\app\admin\model\system\file\classes\SystemFileClassField;
use BusyPHP\app\admin\model\system\file\image\SystemFileImageStyle;
use BusyPHP\app\admin\model\system\file\image\SystemFileImageStyleField;
use BusyPHP\app\admin\model\system\file\SystemFile;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\app\admin\plugin\table\TableHandler;
use BusyPHP\app\admin\plugin\TablePlugin;
use BusyPHP\app\admin\setting\AdminSetting;
use BusyPHP\app\admin\setting\CaptchaSetting;
use BusyPHP\app\admin\setting\QrcodeSetting;
use BusyPHP\app\admin\setting\StorageSetting;
use BusyPHP\app\admin\setting\PublicSetting;
use BusyPHP\exception\ParamInvalidException;
use BusyPHP\file\QRCode;
use BusyPHP\helper\AppHelper;
use BusyPHP\helper\TransHelper;
use BusyPHP\image\parameter\UrlParameter;
use BusyPHP\image\result\ImageStyleResult;
use BusyPHP\Model;
use BusyPHP\model\Map;
use Exception;
use ReflectionException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\facade\Filesystem;
use think\Response;
use Throwable;

/**
 * 系统管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/9/18 下午下午3:50 SystemManager.php $
 */
class ManagerController extends InsideController
{
    /**
     * @var string
     */
    private $disk;
    
    
    protected function initialize($checkLogin = true)
    {
        parent::initialize($checkLogin);
        
        $disk       = $this->param('disk/s', 'trim');
        $this->disk = $disk ?: StorageSetting::STORAGE_LOCAL;
    }
    
    
    /**
     * 写入菜单
     * @throws DataNotFoundException
     * @throws DbException
     */
    protected function assignNav()
    {
        $this->assign('nav', SystemMenu::init()->getChildList('system_manager/index', true, true));
    }
    
    
    /**
     * 系统基本设置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ParamInvalidException
     */
    public function index() : Response
    {
        if ($this->isPost()) {
            $data = $this->post('data/a');
            PublicSetting::init()->set($data);
            $this->log()->record(self::LOG_UPDATE, '系统基本设置');
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        $info = PublicSetting::init()->get();
        $this->assign('info', $info);
        $this->assign('extend_template', AdminPanelDisplayEvent::triggerEvent('System.Index/index', $info));
        $this->assignNav();
        
        return $this->display();
    }
    
    
    /**
     * 管理面板设置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ParamInvalidException
     */
    public function admin() : Response
    {
        if ($this->isPost()) {
            $data = $this->post('data/a');
            AdminSetting::init()->set($data);
            $this->log()->record(self::LOG_UPDATE, '管理面板设置');
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        $info                     = AdminSetting::init()->get();
        $info['watermark']['txt'] = ($info['watermark']['txt'] ?? '') ?: "登录人：{username}\r\n内部系统，严禁拍照，截图\r\n{time}";
        $this->assign('info', $info);
        $this->assignNav();
        
        return $this->display();
    }
    
    
    /**
     * 存储设置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ParamInvalidException
     */
    public function storage() : Response
    {
        $setting = StorageSetting::init();
        if ($this->isPost()) {
            $data = $this->post('data/a');
            $setting->set($data);
            $this->log()->record(self::LOG_UPDATE, '存储设置');
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        $this->assign('clients', AppHelper::getList());
        $this->assign('disks', $setting->getDisks());
        $this->assign('info', $setting->get());
        $this->assignNav();
        
        return $this->display();
    }
    
    
    /**
     * 图片样式管理
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function image_style() : Response
    {
        if ($this->pluginTable) {
            $data = [];
            foreach (Filesystem::disk($this->disk)->image()->selectStyleByCache() as $item) {
                $data[] = $item;
            }
            
            return $this->success($this->pluginTable->result($data, count($data)));
        }
        
        $this->assign('disks', StorageSetting::init()->getDisks());
        $this->assign('disk', $this->disk);
        $this->assignNav();
        
        return $this->display();
    }
    
    
    /**
     * 添加图片样式
     * @return Response
     * @throws ReflectionException
     */
    public function image_style_add() : Response
    {
        if ($this->isPost()) {
            $data = SystemFileImageStyleField::init();
            $data->setId($this->post('id/s', 'trim'));
            $data->setContent($this->post('content/a'));
            Filesystem::disk($this->disk)->image()->createStyle($data->id, $data->content);
            
            $this->log()->record(self::LOG_INSERT, '添加图片样式');
            
            return $this->success('添加成功');
        }
        
        $image = Filesystem::disk($this->disk)->image();
        $this->assign('info', ['content' => ImageStyleResult::filterContext($image, ImageStyleResult::fillContent())]);
        $this->assign('disk', $this->disk);
        $this->assign('font_list', $image->getFontList());
        $this->assign('font_default', $image->getDefaultFontPath());
        
        return $this->display();
    }
    
    
    /**
     * 修改图片样式
     * @return Response
     */
    public function image_style_edit() : Response
    {
        if ($this->isPost()) {
            $data = SystemFileImageStyleField::init();
            $data->setId($this->post('id/s', 'trim'));
            $data->setContent($this->post('content/a'));
            Filesystem::disk($this->disk)->image()->updateStyle($data->id, $data->content);
            
            $this->log()->record(self::LOG_INSERT, '修改图片样式');
            
            return $this->success('修改成功');
        }
        
        $image = Filesystem::disk($this->disk)->image();
        $this->assign('info', ImageStyleResult::filterContext($image, $image->getStyleByCache($this->get('id/s', 'trim'))));
        $this->assign('disk', $this->disk);
        $this->assign('font_list', $image->getFontList());
        $this->assign('font_default', $image->getDefaultFontPath());
        
        return $this->display('image_style_add');
    }
    
    
    /**
     * 删除图片样式
     * @throws Throwable
     */
    public function image_style_delete() : Response
    {
        $driver = Filesystem::disk($this->disk)->image();
        foreach ($this->param('id/list/请选择要删除的图片样式') as $id) {
            $driver->deleteStyle($id);
        }
        
        $this->log()->record(self::LOG_DELETE, '删除图片样式');
        
        return $this->success('删除成功');
    }
    
    
    /**
     * 上传图片样式的水印图片
     * @return Response
     */
    public function image_style_upload_watermark() : Response
    {
        $this->request->setRequestIsAjax();
        $url = Filesystem::disk($this->disk)->image()->uploadWatermark($this->request->file('file'));
        
        return $this->success(['file_url' => $url]);
    }
    
    
    /**
     * 选择图片样式
     * @return Response
     */
    public function image_style_select() : Response
    {
        $list = [];
        foreach (Filesystem::disk($this->disk)->image()->selectStyleByCache() as $item) {
            $list[] = $item;
        }
        if ($this->pluginSelectPicker) {
            return $this->success($this->pluginSelectPicker->result($list, (string) ImageStyleResult::id(), (string) ImageStyleResult::id()));
        }
        
        return $this->success($list);
    }
    
    
    /**
     * 预览图片样式
     * @return Response
     */
    public function image_style_preview() : Response
    {
        $filesystem = Filesystem::disk($this->disk);
        $parameter  = new UrlParameter(SystemFileImageStyle::getPreviewImagePath($filesystem));
        $parameter->style($this->get('id/s', 'trim'));
        
        return $this->redirect($filesystem->image()->url($parameter));
    }
    
    
    /**
     * 分类上传设置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ParamInvalidException
     * @throws Exception
     */
    public function file_class() : Response
    {
        // 分类设置
        if ($this->isPost()) {
            $data = SystemFileClassField::init();
            $data->setId($this->post('id/d'));
            $data->setExtensions($this->post('extensions/s', 'trim'));
            $data->setMaxSize($this->post('max_size/d'));
            $data->setMimetype($this->post('mimetype/s', 'trim'));
            $data->setStyle($this->post('style/a'));
            SystemFileClass::init()->updateData($data);
            
            $this->log()->record(self::LOG_UPDATE, '分类上传设置');
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        // 分类列表数据
        if ($this->pluginTable) {
            $this->pluginTable->setHandler(new class extends TableHandler {
                public function query(TablePlugin $plugin, Model $model, Map $data) : void
                {
                    $model->order(SystemFileClassField::sort(), 'asc');
                    $model->order(SystemFileClassField::id(), 'desc');
                }
            });
            
            return $this->success($this->pluginTable->build(SystemFileClass::init()));
        }
        
        //
        // 修改分类
        elseif ($this->get('action/s') == 'edit') {
            $this->assign('disks', StorageSetting::init()->getDisks());
            $this->assign('info', SystemFileClass::init()->getInfo($this->get('id/d')));
            
            return $this->display('file_class_edit');
        }
        
        // 分类列表
        $this->assign('file_class', SystemFileClass::init()->order('sort ASC')->selectList());
        $this->assign('type', SystemFile::getTypes());
        $this->assign('info', StorageSetting::init()->get());
        $this->assignNav();
        
        return $this->display();
    }
    
    
    /**
     * 图形验证码设置
     * @throws DbException
     */
    public function captcha()
    {
        if ($this->isPost()) {
            $data = $this->post('data/a');
            CaptchaSetting::init()->set($data);
            $this->log()->record(self::LOG_UPDATE, '图形验证码设置');
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        $this->assign('clients', AppHelper::getList());
        $this->assign('info', CaptchaSetting::init()->get());
        $this->assignNav();
        
        return $this->display();
    }
    
    
    /**
     * 二维码生成设置
     * @throws DbException
     */
    public function qrcode()
    {
        if ($this->isPost()) {
            $data = $this->post('data/a');
            QrcodeSetting::init()->set($data);
            $this->log()->record(self::LOG_UPDATE, '二维码生成设置');
            $this->updateCache();
            
            return $this->success('设置成功');
        }
        
        $this->assign('level_options', TransHelper::toOptionHtml(QRCode::getLevels(), QrcodeSetting::init()
            ->getLevel()));
        $this->assign('info', QrcodeSetting::init()->get());
        $this->assignNav();
        
        return $this->display();
    }
    
    
    /**
     * 生成缓存
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function cache_create() : Response
    {
        $this->updateCache();
        $this->log()->record(self::LOG_DEFAULT, '生成缓存');
        
        return $this->success('生成缓存成功');
    }
    
    
    /**
     * 清理缓存
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function cache_clear() : Response
    {
        $this->clearCache();
        $this->log()->record(self::LOG_DEFAULT, '清理缓存');
        
        return $this->success('清理缓存成功');
    }
}